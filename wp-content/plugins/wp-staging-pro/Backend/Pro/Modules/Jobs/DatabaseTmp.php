<?php
namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if (!defined("WPINC"))
{
    die;
}

use WPStaging\Core\WPStaging;

/**
 * Class Database
 * @package WPStaging\Backend\Modules\Jobs
 */
class DatabaseTmp extends \WPStaging\Backend\Modules\Jobs\JobExecutable
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
    
    public $tmpPrefix;
    
    /**
     * Tables to operate on
     * @var array 
     */
    //private $includedTables;

    /**
     * Initialize
     */
    public function initialize()
    {
        // Variables
        $this->total                = count($this->options->tables);
        $this->db                   = WPStaging::getInstance()->get("wpdb");
        $this->tmpPrefix            = 'wpstgtmp_';
        
    }
    

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps()
    {
        $this->options->totalSteps  = $this->total;
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
     * Stop Execution immediately
     * return mixed bool | json
     */
    private function stopExecution(){
        if ($this->db->prefix == $this->tmpPrefix){
            $this->returnException('Fatal Error 9: Prefix ' . $this->db->prefix . ' is used for the live site hence it can not be used for the staging site as well. Please ask support@wp-staging.com how to resolve this.');
        }
        return false;
    }

    /**
     * Copy Tables
     * @param string $tableName
     * @return bool
     */
    private function copyTable($tableName)
    {
        $strings = new \WPStaging\Core\Utils\Strings();

        $newTableName = $this->tmpPrefix . $strings->str_replace_first($this->options->prefix, null, $tableName);

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

        // Finish the step
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
            "DB tmp table: {$old} as {$new} from {$this->options->job->start} to {$rows} records"
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

        $this->log("DB tmp table: CREATE table {$new}");

        $this->db->query("CREATE TABLE {$new} LIKE {$old}");

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

        $this->log("DB tmp table: {$new} already exists, dropping it first");
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
            $old == $new &&
            (
                !isset($this->options->job->current) ||
                !isset($this->options->job->start) ||
                $this->options->job->start == 0
            )
        );
    }
}
