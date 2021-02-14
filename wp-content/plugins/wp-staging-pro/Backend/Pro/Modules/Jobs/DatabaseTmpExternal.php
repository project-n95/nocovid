<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if (!defined("WPINC")) {
    die;
}

use WPStaging\Core\WPStaging;

/**
 * Class Database
 * @package WPStaging\Backend\Modules\Jobs
 */
class DatabaseTmpExternal extends \WPStaging\Backend\Modules\Jobs\JobExecutable {

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \WPDB
     */
    private $stagingDb;

    /**
     * @var \WPDB
     */
    private $productionDb;

    /**
     * The prefix of the new database tables which are used for the live site after updating tables
     * @var string
     * @todo change to CONSTANT
     */
    public $tmpPrefix;

    /**
     * Initialize
     */
    public function initialize() {
        // Variables
        $this->total = count($this->options->tables);
        $this->stagingDb = $this->getStagingDB();
        $this->productionDb = WPStaging::getInstance()->get("wpdb");
        $this->tmpPrefix = 'wpstgtmp_';
    }

    /**
     * Get database object to interact with
     */
    private function getStagingDB() {
        return new \wpdb($this->options->databaseUser, str_replace("\\\\", "\\", $this->options->databasePassword), $this->options->databaseDatabase, $this->options->databaseServer);
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps() {
        $this->options->totalSteps = $this->total;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute() {
        // Over limits threshold
        if ($this->isOverThreshold()) {
            // Prepare response and save current progress
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        // No more steps, finished
        if ($this->options->currentStep > $this->total || !isset($this->options->tables[$this->options->currentStep])) {
            $this->prepareResponse(true, false);
            return false;
        }

        // Table is excluded
        if (in_array($this->options->tables[$this->options->currentStep]->name, $this->options->excludedTables)) {
            $this->prepareResponse();
            return true;
        }

        // Copy table
        if (!$this->copyTable($this->options->tables[$this->options->currentStep]->name)) {
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
     * Set the job
     * @param string $table
     */
    private function setJob($table) {
        if (isset($this->options->job->current)) {
            return;
        }

        $this->options->job->current = $table;
        $this->options->job->start = 0;
    }

    /**
     * Copy Tables
     * @param string $tableName
     * @return bool
     */
    private function copyTable($tableName) {
        $strings = new \WPStaging\Core\Utils\Strings();
        $newTableName = $this->tmpPrefix . $strings->str_replace_first($this->options->prefix, null, $tableName);

        // Drop table if necessary
        $this->dropTable($newTableName);

        // Save current job
        $this->setJob($newTableName);

        // Beginning of the job
        if (!$this->startJob($newTableName, $tableName)) {
            return true;
        }

        // Copy data
        $this->copyData($newTableName, $tableName);

        // Finish the step
        return $this->finishStep();
    }

    /**
     * Start Job and create tmp table
     * @param string $new
     * @param string $old
     * @return bool
     */
    private function startJob($new, $old) {
        if ($this->options->job->start != 0) {
            return true;
        }
        


        $this->log("DB tmp table: CREATE table {$this->productionDb->dbname}.{$new}");

        // Build CREATE statement for table from staging db
        $sql = $this->getCreateStatement($old);

        // Search & replace to prefixed tmp table wpstgtmp_*
        $sql = str_replace("CREATE TABLE `{$old}`", "CREATE TABLE `{$new}`", $sql);
        
        $sql = wpstg_unique_constraint($sql);

        $this->productionDb->query('SET FOREIGN_KEY_CHECKS=0;');

        // Execute Query
        if ($this->productionDb->query($this->adaptCreateStatement($sql)) === false) {
            $this->returnException("DB Tmp Table: Error - Can not copy table {$old} TO {$new} Query: {$sql} db error - " . $this->productionDb->last_error);
        }

        // Old disabled method! Not working with different users with insuficcent database access permissions
        //$this->stagingDb->query( "CREATE TABLE `{$this->productionDb->dbname}`.`{$new}` LIKE `{$this->stagingDb->dbname}`.`{$old}`" );
        // Count rows
        $this->options->job->total = (int) $this->stagingDb->get_var("SELECT COUNT(1) FROM `{$this->stagingDb->dbname}`.`{$old}`");

        if ($this->options->job->total == 0) {
            $this->finishStep();
            return false;
        }

        return true;
    }

    /**
     *  Change create statements according to MySQL version
     *  @since 1.0.0
     */
    private function adaptCreateStatement($create) {

        $fromDbVersion = $this->stagingDb->get_var("SELECT VERSION()");

        $toDbVersion = $this->productionDb->get_var("SELECT VERSION()");

        // If same version, all is good
        if (version_compare($fromDbVersion, $toDbVersion) == 0) {
            return $create;
        }

        // Change from unicode 5.2 (520) to "normal" utf8mb4 unicode on MySQL versions before 5.6
        if (version_compare($toDbVersion, '5.6', '<')) {
            $create = str_replace('utf8mb4_unicode_520_ci', 'utf8mb4_unicode_ci', $create);
            $create = str_replace('utf8_unicode_520_ci', 'utf8_unicode_ci', $create);
        }

        return $create;
    }

    /**
     * Get MySQL query create table
     *
     * @param  string $table_name Table name
     * @return array
     */
    private function getCreateStatement($tableName) {

        $row = $this->stagingDb->get_results("SHOW CREATE TABLE `{$tableName}`", ARRAY_A);

        // Get CREATE statement
        if (isset($row[0]['Create Table'])) {
            return $row[0]['Create Table'];
        }
        return [];
    }

    /**
     * Copy data from old table to new table
     * @param string $new
     * @param string $old
     */
    private function copyData($new, $old) {
        $rows = $this->options->job->start + $this->settings->queryLimit;

        $this->log(
                "DB tmp table: INSERT  {$this->stagingDb->dbname}.{$old} as {$this->productionDb->dbname}.{$new} from {$this->options->job->start} to {$rows} records"
        );

        $limitation = '';

        if ((int) $this->settings->queryLimit > 0) {
            $limitation = " LIMIT {$this->settings->queryLimit} OFFSET {$this->options->job->start}";
        }

        // Get data from staging site
        $rows = $this->stagingDb->get_results("SELECT * FROM `{$old}` {$limitation}", ARRAY_A);

        // Start transaction
        $this->productionDb->query('SET autocommit=0;');
        $this->productionDb->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->productionDb->query('START TRANSACTION;');

        // Copy into production site
        foreach ($rows as $row) {
            $escaped_values = wpstg_mysql_escape_mimic(array_values($row));
            $values = implode("', '", $escaped_values);
            $this->productionDb->query("INSERT INTO `{$new}` VALUES ('{$values}')");
        }

        // Commit transaction
        $this->productionDb->query('COMMIT;');
        $this->productionDb->query('SET autocommit=1;');

        // Set new offset
        $this->options->job->start += $this->settings->queryLimit;
    }

    /**
     * Finish the step
     */
    private function finishStep() {
        // This job is not finished yet
        if ($this->options->job->total > $this->options->job->start) {
            return false;
        }

        // Add it to cloned tables listing
        $this->options->clonedTables[] = $this->options->tables[$this->options->currentStep];

        // Reset job
        $this->options->job = new \stdClass();

        return true;
    }

    /**
     * Drop table if necessary
     * @param string $new
     */
    private function dropTable($new) {
        $old = $this->productionDb->get_var($this->productionDb->prepare("SHOW TABLES LIKE %s", $new));

        if (!$this->shouldDropTable($new, $old)) {
            return;
        }

        $this->log("DB tmp table: {$new} already exists, dropping it first");
        $this->productionDb->query("DROP TABLE {$new}");
    }

    /**
     * Check if table needs to be dropped
     * @param string $new
     * @param string $old
     * @return bool
     */
    private function shouldDropTable($new, $old) {
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
