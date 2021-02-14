<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if (!defined("WPINC")) {
    die;
}

use WPStaging\Backend\Modules\Jobs\JobExecutable;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Strings;

/**
 * @package WPStaging\Backend\Modules\Jobs
 */
class DatabaseTmpRename extends JobExecutable
{
    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \WPDB
     */
    private $productionDb;

    /**
     * The prefix of the new database tables which are used for the live site after updating tables
     * @todo change to CONSTANT
     * @var string
     */
    private $tmpPrefix;

    /**
     * This contains an object of all existing database tables.
     * @var object
     */
    private $existingTables = [];

    /**
     * Initialize
     */
    public function initialize()
    {
        // Variables
        $this->productionDb = WPStaging::getInstance()->get('wpdb');
        $this->total = count($this->options->tables);
        $this->tmpPrefix = 'wpstgtmp_';
        $this->getAllTables();

        $this->checkFatalError();
    }

    protected function checkFatalError()
    {
        if ($this->tmpPrefix === $this->productionDb->prefix) {
            $this->returnException('Fatal Error: Prefix ' . $this->productionDb->prefix . ' is used for the live site and used for the temporary database tables hence we can not replace the production database. Please ask support@wp-staging.com how to resolve this.');
        }
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps()
    {
        $this->options->totalSteps = $this->total;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute()
    {
        // Over limits threshold
        if ($this->isOverThreshold()) {
            $this->log('DB Rename: Is over threshold. Continuing ...', Logger::TYPE_INFO);
            // Prepare response and save current progress
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        // Backup table
        if (!empty($this->options->tables[$this->options->currentStep]->name) && !$this->isFinished()) {
            // Prepare Response
            $this->prepareResponse(false, true);
            // Not finished
            return false;
        }
        // Rename all tables. This not done in chunks to execute it as fast as possible and prevent interruption
        foreach ($this->options->tables as $table) {

            // Excluded table
            if (in_array($table->name, $this->options->excludedTables)) {
                continue;
            }

            // Rename table
            if ($this->renameTable($table->name) === false) {
                return true;
            }
        }

        $this->prepareResponse(true, false);

        $this->flush();
        $this->isFinished();
        return false;
    }

    /**
     * Flush wpdb cache and permalinks
     * @global object $wp_rewrite
     */
    protected function flush()
    {
        // flush rewrite rules to prevent 404s and other oddities
        wp_cache_flush();
        global $wp_rewrite;
        $wp_rewrite->init();
        flush_rewrite_rules(true); // true = hard refresh, recreates the .htaccess file
    }


    /**
     * Switch over tmp tables to live ones
     * @param string $table table name
     * @return bool true
     */
    protected function renameTable($table)
    {
        $strings = new Strings();

        // Table name without prefix
        $table = $strings->str_replace_first($this->options->prefix, '', $table);

        $tmpTable = $this->tmpPrefix . $table;

        $liveTable = $this->productionDb->prefix . $table;

        if ($this->tableExists($tmpTable)) {
            $this->log('DB Rename: ' . $tmpTable . ' to ' . $liveTable);
        }

        $this->productionDb->query('SET FOREIGN_KEY_CHECKS=0;');

        /**
         * Attention: Dropping table first and then renaming it works much more reliable than just using the RENAME statement
         */
        // Drop live table
        if ($this->productionDb->query("DROP TABLE IF EXISTS {$liveTable}") === false) {
            $this->log("DB Rename: Error - Can not drop table {$liveTable} Error: {$this->productionDb->last_error}", Logger::TYPE_ERROR);
            $this->returnException("DB Rename: Error - Can not drop table {$liveTable} db error - " . $this->productionDb->last_error);
        }

        // Rename tmp table to live table
        if ($this->productionDb->query("RENAME TABLE {$tmpTable} TO {$liveTable}") === false) {
            $this->log("DB Rename: Error - Can not rename table {$tmpTable} TO {$liveTable} Error: {$this->productionDb->last_error}", Logger::TYPE_ERROR);
            $this->returnException("DB Rename: Error - Can not rename table {$tmpTable} TO {$liveTable} db error - " . $this->productionDb->last_error);
            return false;
        }

        return true;
    }

    /**
     * Drop table if necessary
     * @param string $table
     */
    protected function dropTable($table)
    {
        // Check if table already exists
        if ($this->tableExists($table) === false) {
            return;
        }

        $this->log("DB Rename: {$table} already exists, dropping it first");
        if ($this->productionDb->query("DROP TABLE {$table}") === false) {
            //$this->db->query("ROLLBACK");
            $this->log("DB Rename: Can not drop table {$table}");
            $this->returnException("DB Rename: Can not drop table {$table}");
        }
    }

    /**
     * Check if table needs to be dropped first
     * @param string $new
     * @param string $old
     * @return bool
     */
    protected function shouldDropTable($new, $old)
    {
        return ($old === $new);
    }

    /**
     * Check if table exists
     * @param string $table
     * @return boolean
     */
    protected function tableExists($table)
    {
        if (in_array($table, $this->existingTables, true)) {
            return true;
        }
        return false;
    }

    /**
     * Get all available tables
     */
    protected function getAllTables()
    {
        $sql = "SHOW TABLE STATUS";
        $tables = $this->productionDb->get_results($sql);
        foreach ($tables as $table) {
            $this->existingTables[] = $table->Name;
        }
    }


    /**
     * Push is finished
     * @return boolean
     */
    protected function isFinished()
    {

        // This job is finished
        if ($this->options->currentStep >= $this->options->totalSteps) {
            $this->log('DB Rename: Has been finished successfully. Cleaning up...');
            $this->prepareResponse(true, false);
            return true;
        }


        return false;
    }
}
