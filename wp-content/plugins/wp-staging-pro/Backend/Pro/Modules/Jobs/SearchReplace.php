<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if( !defined( "WPINC" ) ) {
    die;
}

use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Core\Utils\Helper;

/**
 * Class Database
 * @package WPStaging\Backend\Modules\Jobs
 */
class SearchReplace extends \WPStaging\Backend\Modules\Jobs\JobExecutable {

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \WPDB
     */
    public $db;

    /**
     * The prefix of the new database tables which are used for the live site after updating tables
     * @var string
     */
    public $tmpPrefix;

    /**
     *
     * @var string
     */
    private $homeUrl;

    /**
     * Initialize
     */
    public function initialize() {
        $this->total     = count( $this->options->tables );
        $this->db        = WPStaging::getInstance()->get( "wpdb" );
        $this->tmpPrefix = 'wpstgtmp_';
        $helper          = new Helper();
        $this->homeUrl   = $helper->getHomeUrlWithoutScheme();
    }

    /**
     * Get destination Hostname e.g. domain.com or domain.com/subfolder if WP has been installed in a subfolder
     * @return string
     */
    private function getDestinationHost() {
        $helper          = new Helper();
        $destinationHost = $helper->getHomeUrlWithoutScheme();

        if( $this->isSubDir() ) {
            $destinationHost = $helper->getHomeUrlWithoutScheme() . '/' . $this->getSubDir();
        }

        return $destinationHost;
    }

    public function start() {

        $this->run();

        // Save option, progress
        $this->saveOptions();

        return ( object ) $this->response;
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
        if( $this->isOverThreshold() ) {
            // Prepare response and save current progress
            $this->prepareResponse( false, false );
            $this->saveOptions();
            return false;
        }

        // No more steps, finished
        if( $this->options->currentStep > $this->total || !isset( $this->options->tables[$this->options->currentStep] ) ) {
            $this->prepareResponse( true, false );
            return false;
        }

        // Table is excluded
        if( !empty( $this->options->tables[$this->options->currentStep]->name ) && in_array( ( string ) $this->options->tables[$this->options->currentStep]->name, $this->options->excludedTables ) ) {
            $table = $this->options->tables[$this->options->currentStep]->name;
            $this->log( "DB Search & Replace: Table {$table} excluded", \WPStaging\Core\Utils\Logger::TYPE_INFO );
            $this->prepareResponse();
            return true;
        }

        // Search & Replace
        if( !$this->stopExecution() && !$this->updateTable( $this->options->tables[$this->options->currentStep]->name ) ) {
            // Prepare Response
            $this->prepareResponse( false, false );

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
    private function stopExecution() {
        if( $this->db->prefix == $this->tmpPrefix ) {
            $this->returnException( 'Fatal Error 9: Prefix ' . $this->db->prefix . ' is used for the live site hence it can not be used for the staging site as well. Please ask support@wp-staging.com how to resolve this.' );
        }
        return false;
    }

    /**
     * Copy Tables
     * @param string $tableName
     * @return bool
     */
    private function updateTable( $tableName ) {
        $strings      = new Strings();
        $table        = $strings->str_replace_first( $this->options->prefix, '', $tableName );
        $newTableName = $this->tmpPrefix . $table;

        // Save current job
        $this->setJob( $newTableName );

        // Beginning of the job
        if( !$this->startJob( $newTableName, $tableName ) ) {
            return true;
        }
        // Copy data
        $this->startReplace( $newTableName );

        // Finis the step
        return $this->finishStep();
    }

    /**
     * Start search replace job
     * @param string $new
     * @param string $old
     */
    private function startReplace( $new ) {
        $rows = $this->options->job->start + $this->settings->querySRLimit;
        $this->log(
                "DB Search & Replace:  Table {$new} {$this->options->job->start} to {$rows} records"
        );

        // Search & Replace
        $this->searchReplace( $new, $rows, [] );

        // Set new offset
        $this->options->job->start += $this->settings->querySRLimit;
    }


    /**
     * Gets the columns in a table.
     * @access public
     * @param  string $table The table to check.
     * @return array
     */
    private function get_columns( $table ) {
        $primary_key = null;
        $columns     = [];
        $fields      = $this->db->get_results( 'DESCRIBE ' . $table );
        if( is_array( $fields ) ) {
            foreach ( $fields as $column ) {
                $columns[] = $column->Field;
                if( $column->Key == 'PRI' ) {
                    $primary_key = $column->Field;
                }
            }
        }
        return [$primary_key, $columns];
    }

    /**
     * Return url without scheme
     * @param string $str
     * @return string
     */
    private function get_url_without_scheme( $str ) {
        return preg_replace( '#^https?://#', '', rtrim( $str, '/' ) );
    }

    /**
     * Adapted from interconnect/it's search/replace script, adapted from Better Search Replace
     *
     * Modified to use WordPress wpdb functions instead of PHP's native mysql/pdo functions,
     * and to be compatible with batch processing.
     *
     * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
     *
     * @access public
     * @param  string 	$table 	The table to run the replacement on.
     * @param  int          $page  	The page/block to begin the query on.
     * @param  array 	$args         An associative array containing arguments for this run.
     * @return array
     */
    private function searchReplace( $table, $page, $args ) {

        // Skip certain tables for compatibility purposes
        if( $this->thirdParty->isSearchReplaceExcluded( $table ) ) {
            $this->log( "DB Search & Replace: Skip {$table}", \WPStaging\Core\Utils\Logger::TYPE_INFO );
            return true;
        }

        // Load up the default settings for this chunk.
        $table = esc_sql( $table );

        $helper             = new Helper();
        // Search URL example.com/staging and root path to staging site /var/www/htdocs/staging
        $args['search_for'] = [
            '\/\/' . str_replace( '/', '\/', $this->get_url_without_scheme( $this->options->url ) ), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->get_url_without_scheme( $this->options->url ), // //host.com or //host.com/subfolder
            rtrim( $this->options->path, DIRECTORY_SEPARATOR ),
            str_replace( '/', '%2F', $this->get_url_without_scheme( $this->options->url ) )
        ];

        $args['replace_with'] = [
            '\/\/' . str_replace( '/', '\/', $this->getDestinationHost() ), // \/\/host.com or \/\/host.com\/subfolder
            '//' . $this->getDestinationHost(), // //host.com or //host.com/subfolder
            rtrim( ABSPATH, '/' ),
            $helper->getHomeUrlWithoutScheme()
        ];


        $args['replace_guids']    = 'off';
        $args['dry_run']          = 'off';
        $args['case_insensitive'] = false;
        $args['skip_transients']  = 'off';

        // Allow filtering of search & replace parameters
        $args = apply_filters( 'wpstg_push_searchreplace_params', $args );


        $this->log( "DB Search & Replace: Table {$table}" );

        // Get a list of columns in this table.
        list( $primary_key, $columns ) = $this->get_columns( $table );

        // Bail out early if there isn't a primary key.
        // We commented this to search & replace through tables which have no primary keys like wp_revslider_slides
        // @todo test this carefully. If it causes (performance) issues we need to activate it again!
        // @since 2.4.4
        //      if( null === $primary_key ) {
        //         return false;
        //      }

        $current_row = 0;
        $start       = $this->options->job->start;

        //Make sure value is never smaller than 1 or greater than 20000
        $end  = $this->settings->querySRLimit;
        //$end = $this->settings->querySRLimit == '0' || empty( $this->settings->querySRLimit ) ? 1 : $this->settings->querySRLimit > 20000 ? 20000 : $this->settings->querySRLimit;
        // Grab the content of the current table.
        $data = $this->db->get_results( "SELECT * FROM $table LIMIT $start, $end", ARRAY_A );

        // Filter certain rows (of other plugins)
        $filter = [
            'Admin_custome_login_Slidshow',
            'Admin_custome_login_Social',
            'Admin_custome_login_logo',
            'Admin_custome_login_text',
            'Admin_custome_login_login',
            'Admin_custome_login_top',
            'Admin_custome_login_dashboard',
            'Admin_custome_login_Version',
            'upload_path',
	    'wpstg_existing_clones_beta',
	    'wpstg_existing_clones',
	    'wpstg_settings',
	    'wpstg_license_status',
	    'siteurl',
	    'home'
        ];

        $filter = apply_filters( 'wpstg_clone_searchreplace_excl_rows', $filter );

        // Go through the table rows
        foreach ($data as $row) {
            $current_row++;
            $update_sql = [];
            $where_sql  = [];
            $upd        = false;

            // Skip rows
            if (isset($row['option_name']) && in_array($row['option_name'], $filter)) {
                continue;
            }

            // Skip transients (There can be thousands of them. Save memory and increase performance)
            if (isset($row['option_name']) && $args['skip_transients'] === 'on' && strpos($row['option_name'], '_transient')
                !== false) {
                continue;
            }
            // Skip rows with more than 5MB to save memory. These rows contain log data or something similiar but never site relevant data
            if (isset($row['option_value']) && strlen($row['option_value']) >= 5000000) {
                continue;
            }

	    // Go through the columns
            foreach ($columns as $column) {

                $dataRow = $row[$column];

                // Skip column larger than 5MB
                $size = strlen($dataRow);
                if ($size >= 5000000) {
                    continue;
                }

                // Skip primary key column
                if ($column == $primary_key) {
                    $where_sql[] = $column.' = "'.$this->mysql_escape_mimic($dataRow).'"';
                    continue;
                }

                // Skip GUIDs by default.
                if ($args['replace_guids'] !== 'on' && $column == 'guid') {
                    continue;
                }


                $i = 0;
                foreach ( $args['search_for'] as $replace ) {
                    $dataRow = $this->recursive_unserialize_replace( $args['search_for'][$i], $args['replace_with'][$i], $dataRow, false, $args['case_insensitive'] );
                    $i++;
                }
                unset($replace, $i);

                // Something was changed
                if( $row[$column] != $dataRow ) {
                    $update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $dataRow ) . '"';
                    $upd          = true;
                }
            }

            // Determine what to do with updates.
            if( $args['dry_run'] === 'on' ) {
                // Don't do anything if a dry run
            } elseif( $upd && !empty( $where_sql ) ) {
                // If there are changes to make, run the query.
                $sql    = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
                $result = $this->db->query( $sql );

                if( !$result ) {
                    $this->log( "Error updating row {$current_row}", \WPStaging\Core\Utils\Logger::TYPE_ERROR );
                }
            }
        } // end row loop
        unset($row);
        unset($update_sql);
        unset($where_sql);
        unset($sql);
        unset($current_row);

        // DB Flush
        $this->db->flush();
        return true;
    }

    /**
     * Adapted from interconnect/it's search/replace script.
     *
     * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
     *
     * Take a serialised array and unserialise it replacing elements as needed and
     * unserialising any subordinate arrays and performing the replace on those too.
     *
     * @access private
     * @param  string 			$from       		String we're looking to replace.
     * @param  string 			$to         		What we want it to be replaced with
     * @param  array  			$data       		Used to pass any subordinate arrays back to in.
     * @param  boolean 			$serialized 		Does the array passed via $data need serialising.
     * @param  sting|boolean              $case_insensitive 	Set to 'on' if we should ignore case, false otherwise.
     *
     * @return string|array	The original array with all elements replaced as needed.
     */
    private function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialized = false, $case_insensitive = false ) {
        try {
            // PDO instances can not be serialized or unserialized
            if( is_serialized( $data ) && strpos( $data, 'O:3:"PDO":0:' ) !== false ) {
                return $data;
            }
            // DateTime object can not be unserialized.
            // Would throw PHP Fatal error:  Uncaught Error: Invalid serialization data for DateTime object in
            // Bug PHP https://bugs.php.net/bug.php?id=68889&thanks=6 and https://github.com/WP-Staging/wp-staging-pro/issues/74
            if( is_serialized( $data ) && strpos( $data, 'O:8:"DateTime":0:' ) !== false ) {
                return $data;
            }
            // Some unserialized data cannot be re-serialized eg. SimpleXMLElements
            if( is_serialized( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
                $data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true, $case_insensitive );
            } elseif( is_array( $data ) ) {
                $tmp = [];
                foreach ( $data as $key => $value ) {
                    $tmp[$key] = $this->recursive_unserialize_replace( $from, $to, $value, false, $case_insensitive );
                }

                $data = $tmp;
                unset( $tmp );
            } elseif( is_object( $data ) ) {
                $props = get_object_vars( $data );

                // Do a search & replace
                if( empty( $props['__PHP_Incomplete_Class_Name'] ) ) {
                    $tmp = $data;
                    foreach ( $props as $key => $value ) {
                        if( $key === '' || ord( $key[0] ) === 0 ) {
                            continue;
                        }
                        $tmp->$key = $this->recursive_unserialize_replace( $from, $to, $value, false, $case_insensitive );
                    }
                    $data  = $tmp;
                    $tmp   = '';
                    $props = '';
                    unset( $tmp );
                    unset( $props );
                }
            } else {
                if( is_string( $data ) ) {
                    if( !empty( $from ) && !empty( $to ) ) {
                        $data = $this->str_replace( $from, $to, $data, $case_insensitive );
                    }
                }
            }

            if( $serialized ) {
                return serialize( $data );
            }
        } catch ( Exception $error ) {

        }

        return $data;
    }

    /**
     * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
     * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
     * @access public
     * @param  string $input The string to escape.
     * @return string
     */
    private function mysql_escape_mimic( $input ) {
        if( is_array( $input ) ) {
            return array_map( __METHOD__, $input );
        }
        if( !empty( $input ) && is_string( $input ) ) {
            return str_replace( ['\\', "\0", "\n", "\r", "'", '"', "\x1a"], ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $input );
        }

        return $input;
    }

    /**
     * Return unserialized object or array
     *
     * @param string $serialized_string Serialized string.
     * @param string $method            The name of the caller method.
     *
     * @return mixed, false on failure
     */
    private static function unserialize( $serialized_string ) {
        if( !is_serialized( $serialized_string ) ) {
            return false;
        }

        $serialized_string   = trim( $serialized_string );
        $unserialized_string = @unserialize( $serialized_string );

        return $unserialized_string;
    }

    /**
     * Wrapper for str_replace
     *
     * @param string $from
     * @param string $to
     * @param string $data
     * @param string|bool $case_insensitive
     *
     * @return string
     */
    private function str_replace( $from, $to, $data, $case_insensitive = false ) {
        if( $case_insensitive === 'on' ) {
            $data = str_ireplace( $from, $to, $data );
            //$data = preg_replace( '#' . $regexExclude . preg_quote( $from ) . '#i', $to, $data );
        } else {
            $data = str_replace( $from, $to, $data );
            //$data = preg_replace( '#' . $regexExclude . preg_quote( $from ) . '#', $to, $data );
        }

        return $data;
    }

    /**
     * Set the job
     * @param string $table
     */
    private function setJob( $table ) {
        if( !empty( $this->options->job->current ) ) {
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
    private function startJob( $new, $old ) {
        if( $this->options->job->start != 0 ) {
            return true;
        }

        $this->options->job->total = ( int ) $this->db->get_var( "SELECT COUNT(1) FROM {$new}" );

        //$this->log( "DB Search & Replace: Start Job total steps {$this->options->job->total}" );
        //$this->log( "SELECT COUNT(1) FROM {$new}" );


        if( $this->options->job->total == 0 ) {
            $this->finishStep();
            return false;
        }

        return true;
    }

    /**
     * Finish the step
     */
    private function finishStep() {
        // This job is not finished yet
        if( $this->options->job->total > $this->options->job->start ) {
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
    private function dropTable( $new ) {
        $old = $this->db->get_var( $this->db->prepare( "SHOW TABLES LIKE %s", $new ) );

        if( !$this->shouldDropTable( $new, $old ) ) {
            return;
        }

        $this->log( "DB Search & Replace: {$new} already exists, dropping it first" );
        $this->db->query( "DROP TABLE {$new}" );
    }

    /**
     * Check if table needs to be dropped
     * @param string $new
     * @param string $old
     * @return bool
     */
    private function shouldDropTable( $new, $old ) {
        return (
                $old == $new &&
                (
                !isset( $this->options->job->current ) ||
                !isset( $this->options->job->start ) ||
                $this->options->job->start == 0
                )
                );
    }

    /**
     * Check if WP is installed in subdir
     * @return boolean
     */
    private function isSubDir() {
        // Compare names without scheme to bypass cases where siteurl and home have different schemes http / https
        // This is happening much more often than you would expect
        $siteurl = preg_replace( '#^https?://#', '', rtrim( get_option( 'siteurl' ), '/' ) );
        $home    = preg_replace( '#^https?://#', '', rtrim( get_option( 'home' ), '/' ) );

        if( $home !== $siteurl ) {
            return true;
        }
        return false;
    }

    /**
     * Get the install sub directory if WP is installed in sub directory
     * @return string
     */
    private function getSubDir() {
        $home    = get_option( 'home' );
        $siteurl = get_option( 'siteurl' );

        if( empty( $home ) || empty( $siteurl ) ) {
            return '';
        }

        $dir = str_replace( $home, '', $siteurl );
        return str_replace( '/', '', $dir );
    }

}
