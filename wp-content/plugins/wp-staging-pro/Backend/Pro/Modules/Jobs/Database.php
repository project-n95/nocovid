<?php
namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if (!defined("WPINC"))
{
    die;
}

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Strings;

/**
 * Class Database
 * @package WPStaging\Backend\Modules\Jobs
 */
class Database extends \WPStaging\Backend\Modules\Jobs\JobExecutable
{

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \WPDB
     */
    private $db;
    
    /**
     * The prefix of the new database tables which are used for the live site after updating tables
     * @var string 
     */
    
    private $tmpPrefix;

    /**
     * Initialize
     */
    public function initialize()
    {
        // Variables
        $this->total                = count($this->options->tables);
        $this->db                   = WPStaging::getInstance()->get("wpdb");
        $this->tmpPrefix           = $this->setTmpPrefix();
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps()
    {
        $this->options->totalSteps  = $this->total === 0 ? 1 : $this->total;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute()
    {
        // Over limits threshold
        if ($this->isOverThreshold())
        {
            // Prepare response and save current progress
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        // No more steps, finished
        if ($this->options->currentStep > $this->total || !isset($this->options->tables[$this->options->currentStep]))
        {
            $this->prepareResponse(true, false);
            return false;
        }

        // Table is excluded
        if (in_array($this->options->tables[$this->options->currentStep]->name, $this->options->excludedTables))
        {
            $this->prepareResponse();
            return true;
        }

        // Copy table
        if (!$this->stopExecution() && !$this->copyTable($this->options->tables[$this->options->currentStep]->name))
        {
            // Prepare Response
            $this->prepareResponse(false, false);

            // Not finished
            return true;
        }

        // Prepare Response
        $this->prepareResponse();

        // Not finished
        return true;
    }

    /**
     * Get prefix for the new database tables which are used for the new live site
     * @return string
     */
    private function setTmpPrefix(){
        $tmpPrefix = "wpstgtmp{$this->options->cloneNumber}";
        
        // Check that $tmpPrefix is not already used for other tables
        $exists = $this->db->get_results(
            "SHOW TABLES LIKE '%".$tmpPrefix."%'"
        );
    
        if ( $exists ){
            // Change prefix slightly to make sure it is no longer the same
            $tmpPrefix = $tmpPrefix . 'a';
        }  
        
        // Check that $tmpPrefix is not the same as the prefix of the current live site
        if ($this->db->prefix == $tmpPrefix){
            // Change prefix slightly to make sure it is no longer the same
            $tmpPrefix = $tmpPrefix . 'b';         
        }
        
        $clone = get_option('wpstg_existing_clones_beta', []);
        $clone[$this->options->current]['tmpPrefix'] = $tmpPrefix;
        //$this->returnException(var_dump($clone));
        //update_option('wpstg_existing_clones_beta',$clone);
        
        return $tmpPrefix . '_';
    }
    
        
    /**
     * Get prefix of the staging site the current process is running for
     * @return string
     */
    private function getStagingPrefix(){
        // Make sure prefix of staging site is NEVER identical to prefix of live site! 
        if ( $this->options->prefix == $this->db->prefix ){
            $error = 'Fatal error 7: Database table prefix ' . $this->options->prefix . ' would be identical to the table prefix of the production site. Open a support ticket at support@wp-staging.com if you have questions';
            $this->returnException($error);
            wp_die( $error );        
            
        }  
        return $this->options->prefix;
    }
   
    
    /**
     * Stop Execution immediately
     * return mixed bool | json
     */
    private function stopExecution(){
        if ($this->tmpPrefix == $this->getStagingPrefix()){
            $this->returnException('Fatal Error 9: Prefix ' . $this->db->prefix . ' is used for the live site hence it can not be used for the staging site as well. Please ask support@wp-staging.com how to resolve this.');
        }
        return false;
    }

    /**
     * No worries, SQL queries don't eat from PHP execution time!
     * @param string $tableName
     * @return bool
     */
    private function copyTable($tableName)
    {
       $strings = new Strings();
        
        $tableName = is_object($tableName) ? $tableName->name : $tableName;
        $newTableName = $this->tmpPrefix . $strings->str_replace_first($this->getStagingPrefix(), null, $tableName);

        // Drop table if necessary
        $this->dropTable($newTableName);

        // Save current job
        $this->setJob($newTableName);

        // Beginning of the job
        if (!$this->startJob($newTableName, $tableName))
        {
            return true;
        }

        // Copy data
        $this->copyData($newTableName, $tableName);

        // Finis the step
        return $this->finishStep();
    }

    /**
     * Copy data from old table to new table
     * @param string $new
     * @param string $old
     */
    private function copyData($new, $old)
    {
        $rows = $this->options->job->start+$this->settings->queryLimit;
        $this->log(
            "DB Copy: {$old} as {$new} from {$this->options->job->start} to {$rows} records"
        );

        $limitation = '';

        if ((int) $this->settings->queryLimit > 0)
        {
            $limitation = " LIMIT {$this->settings->queryLimit} OFFSET {$this->options->job->start}";
        }

        $this->db->query(
            "INSERT INTO {$new} SELECT * FROM {$old} {$limitation}"
        );

        // Set new offset
        $this->options->job->start += $this->settings->queryLimit;
    }

    /**
     * Set the job
     * @param string $table
     */
    private function setJob($table)
    {
        if (isset($this->options->job->current))
        {
            return;
        }

        $this->options->job->current = $table;
        $this->options->job->start   = 0;
    }

    /**
     * Start Job
     * @param string $new
     * @param string $old
     * @return bool
     */
    private function startJob($new, $old)
    {
        if ($this->options->job->start != 0)
        {
            return true;
        }

        $this->log("DB Copy: Creating table {$new}");

        $this->db->query("CREATE TABLE {$new}");

        $this->options->job->total = (int) $this->db->get_var("SELECT COUNT(1) FROM {$old}");

        if ($this->options->job->total == 0)
        {
            $this->finishStep();
            return false;
        }

        return true;
    }

    /**
     * Finish the step
     */
    private function finishStep()
    {
        // This job is not finished yet
        if ($this->options->job->total > $this->options->job->start)
        {
            return false;
        }

        // Add it to cloned tables listing
        $this->options->clonedTables[]  = $this->options->tables[$this->options->currentStep];

        // Reset job
        $this->options->job             = new \stdClass();

        return true;
    }

    /**
     * Drop table if necessary
     * @param string $new
     */
    private function dropTable($new)
    {
        $old = $this->db->get_var($this->db->prepare("SHOW TABLES LIKE %s", $new));

        if (!$this->shouldDropTable($new, $old))
        {
            return;
        }

        $this->log("DB Copy: {$new} already exists, dropping it first");
        $this->db->query("DROP TABLE {$new}");
    }

    /**
     * Check if table needs to be dropped
     * @param string $new
     * @param string $old
     * @return bool
     */
    private function shouldDropTable($new, $old)
    {
        return (
            $old === $new &&
            (
                !isset($this->options->job->current) ||
                !isset($this->options->job->start) ||
                $this->options->job->start == 0
            )
        );
    }
}
