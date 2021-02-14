<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

// No Direct Access
if( !defined( "WPINC" ) ) {
    die;
}

use WPStaging\Backend\Modules\Jobs\JobExecutable;
use WPStaging\Backend\Notices\DisabledCacheNotice;
use WPStaging\Framework\Security\AccessToken;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;

/**
 * Class Data
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class Data extends JobExecutable {

    /**
     * @var \wpdb
     */
    private $db;

    /**
     * Prefix of the tmp database tables
     *
     * @var string
     */
    private $tmpPrefix = 'wpstgtmp_';

    /**
     * Initialize
     */
    public function initialize() {
        $this->db = WPStaging::getInstance()->get( "wpdb" );

        // Fix current step
        if( $this->options->currentStep == 0 ) {
            $this->options->currentStep = 1;
        }
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps() {
        $this->options->totalSteps = 12;
    }

    /**
     * Start Module
     * @return object
     */
    public function start() {
        // Execute steps
        $this->run();

        // Save option, progress
        $this->saveOptions();

        return ( object ) $this->response;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute() {

        // No more steps, finished
        if( $this->isFinished() ) {
            $this->prepareResponse( true, false );
            return false;
        }

        // Execute step
        $stepMethodName = "step" . $this->options->currentStep;
        if( !$this->{$stepMethodName}() ) {
            $this->prepareResponse( false, false );
            return false;
        }

        // Prepare Response
        $this->prepareResponse();

        // Not finished
        return true;
    }

    /**
     * Checks Whether There is Any Job to Execute or Not
     * @return bool
     */
    protected function isFinished() {
        return (
                $this->options->currentStep > $this->options->totalSteps ||
                !method_exists( $this, "step" . $this->options->currentStep )
                );
    }

    /**
     * Check if table exists
     * @param string $table
     * @return boolean
     */
    protected function isTable( $table ) {
        if( $table != $this->db->get_var( "SHOW TABLES LIKE '{$table}'" ) ) {
            $this->log( "Table {$table} does not exist", Logger::TYPE_INFO );
            return false;
        }
        return true;
    }

    /**
     * Check if table is excluded
     * @param string $table
     * @return boolean
     */
    protected function isTableExcluded( $table ) {
        if( in_array( $table, $this->options->excludedTables ) ) {
            return true;
        }
        return false;
    }

    /**
     * Update several entries in options table
     * @return bool
     */
    protected function step1() {


        $optionsTableTmp = $this->tmpPrefix . 'options';

        // Options table has been excluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            $this->log( "DB Data Step 1: Skipping table {$this->options->prefix}options" );
            return true;
        }

        $this->log( "DB Data: Move list of staging sites to " . $optionsTableTmp );

        // Get staging sites data from live site - WP Staging 2.0 and higher
        $wpstg_existing_clones_beta = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'wpstg_existing_clones_beta' " );

        if( !$wpstg_existing_clones_beta ) {
            $this->log( "DB Data: Can not get data wpstg_existing_clones_beta from" . $optionsTableTmp );
            $this->returnException( 'DB Data: Can not get data wpstg_existing_clones_beta from' . $optionsTableTmp );
        }

        // do some escaping
        $serialized = $this->mysql_escape_mimic( $wpstg_existing_clones_beta );

        // Get staging sites data from tmp table
        $wpstg_existing_clones_beta_tmp_tbl = $this->db->get_var( "SELECT option_value FROM {$optionsTableTmp} options WHERE option_name = 'wpstg_existing_clones_beta' " );

        // Copy staging sites data to tmp table
        if( !$wpstg_existing_clones_beta_tmp_tbl ) {
            $query = $this->db->query(
                    "INSERT INTO {$optionsTableTmp} (option_name, option_value) VALUES ('wpstg_existing_clones_beta', '{$serialized}')"
            );
        } else {
            $query = $this->db->query(
                    "UPDATE {$optionsTableTmp} SET option_value = '" . $serialized . "' WHERE option_name = 'wpstg_existing_clones_beta' "
            );
        }



        if( $query === false ) {
            $this->log( "DB Data: Can not update value wpstg_existing_clones_beta in " . $optionsTableTmp . ' - db error: ' . $this->db->last_error );
            $this->returnException( "DB Data: Can not update value wpstg_existing_clones_beta in " . $optionsTableTmp . ' - db error: ' . $this->db->last_error );
            return false;
        }

        $optionsToDelete = (array)apply_filters('wpstg.after_push.options_to_delete', [
            'wpstg_is_staging_site',
            DisabledCacheNotice::OPTION_NAME,
        ]);

        foreach ($optionsToDelete as $option) {
            $resultDelete = $this->db->query(
                "DELETE FROM $optionsTableTmp WHERE option_name = '$option'"
            );

            if( $resultDelete === false ) {
                $this->log( "DB Data: Can not delete table row $option from $optionsTableTmp" );
                $this->returnException( "DB Data: Can not delete table row $option from $optionsTableTmp - db error: " . $this->db->last_error );
                return false;
            }
        }

        // Copy license data
        $wpstgLicense = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'wpstg_license_key' " );

        $resultWpstgLicense = $this->db->replace(
                $this->tmpPrefix . 'options', [
            'option_name'  => 'wpstg_license_key',
            'option_value' => $wpstgLicense ? $wpstgLicense : ''
                ], [
            '%s',
            '%s'
                ]
        );

        if( $resultWpstgLicense === false ) {
            $this->log( 'DB Data: Warning - Can not copy license key from live site' );
        }


        // Copy wpstg settings
        $wpstgSettings = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'wpstg_settings' " );

        $resultWpstgSettings = $this->db->replace(
                $this->tmpPrefix . 'options', [
            'option_name'  => 'wpstg_settings',
            //'option_value' => $wpstgSettings ? $this->mysql_escape_mimic($wpstgSettings) : ''
            'option_value' => $wpstgSettings ? $wpstgSettings : ''
                ], [
            '%s',
            '%s'
                ]
        );

        if( $resultWpstgSettings === false ) {
            $this->log( 'DB Data: Error - Can not copy WP Staging settings from live site' );
            $this->returnException( 'DB Data: Error - Can not copy WP Staging settings from live site - db error: ' . $this->db->last_error );
        }

        // Copy wpstg license status
        $wpstg_license_status = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'wpstg_license_status' " );

        $resultWpstg_license_status = $this->db->replace(
                $this->tmpPrefix . 'options', [
            'option_name'  => 'wpstg_license_status',
            //'option_value' => $wpstg_license_status ? $this->mysql_escape_mimic($wpstg_license_status) : ''
            'option_value' => $wpstg_license_status ? $wpstg_license_status : ''
                ], [
            '%s',
            '%s'
                ]
        );

        if( $resultWpstg_license_status === false ) {
            $this->log( 'DB Data: Warning: Can not copy WP Staging license status from live site' );
            $this->returnException( 'DB Data: Warning: Can not copy WP Staging license status from live site' );
        }


        return true;
    }

    /**
     * Update table wp_options
     * Change table prefix
     * @return bool
     */
    protected function step2() {


        // options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            $this->log( "DB Data Step 2: Skipping table {$this->options->prefix}options" );
            return true;
        }

        $this->log( "DB Data Step 2: Updating {$this->tmpPrefix}options table prefix to {$this->db->prefix}. db error: {$this->db->last_error}" );
        $this->debugLog( "DB Data Step 2: SQL - UPDATE {$this->tmpPrefix}options SET option_name = replace(option_name, {$this->options->prefix}, {$this->db->prefix}) WHERE option_name LIKE {$this->options->prefix}_%" );

        $resultOptions = $this->db->query(
                $this->db->prepare(
                        "UPDATE IGNORE {$this->tmpPrefix}options SET option_name= replace(option_name, %s, %s) WHERE option_name LIKE %s", $this->options->prefix, $this->db->prefix, $this->options->prefix . "_%"
                )
        );

        if( $resultOptions === false ) {
            $this->log( "DB Data Step 2: Failed to update {$this->tmpPrefix}options with table prefixes. DB Error: {$this->db->last_error}" );
            $this->returnException( "DB Data Step 2: Failed to update {$this->tmpPrefix}options with table prefixes {$this->db->prefix}. DB Error: {$this->db->last_error}" );
            return false;
        }

        return true;
    }

    /**
     * Update table user_meta
     * Change table prefix
     * @return bool
     */
    protected function step3() {


        // usermeta table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'usermeta' ) ) {
            $this->log( "DB Data Step 3: Skipping table {$this->options->prefix}user_meta" );
            return true;
        }

        $this->log( "DB Data Step 3: Updating {$this->tmpPrefix}usermeta db prefix to {$this->db->prefix} {$this->db->last_error}" );

        if( $this->options->prefix == $this->db->prefix ) {
            $this->log( "DB Data Step 3: Skip. Prefix of production and live site is the same: {$this->db->prefix}  {$this->db->last_error}" );
            return true;
        }

        if( $this->isTable( $this->tmpPrefix . 'usermeta' ) === false ) {
            $this->log( 'DB Data Step 3: Fatal Error ' . $this->tmpPrefix . 'usermeta does not exist' );
            $this->returnException( 'DB Data Step 3: Fatal Error ' . $this->tmpPrefix . 'usermeta does not exist' );
            return false;
        }

        $resultMetaKeys = $this->db->query(
                $this->db->prepare(
                        "UPDATE {$this->tmpPrefix}usermeta SET meta_key = replace(meta_key, %s, %s) WHERE meta_key LIKE %s", $this->options->prefix, $this->db->prefix, $this->options->prefix . "_%"
                )
        );

        if( $resultMetaKeys === false ) {
            $this->log( "DB Data Step 3: SQL - UPDATE {$this->tmpPrefix}usermeta SET meta_key = replace(meta_key, {$this->options->prefix}, {$this->db->prefix}) WHERE meta_key LIKE {$this->options->prefix}_%" );
            $this->log( "DB Data Step 3: Failed to update usermeta meta_key database table prefixes {$this->db->last_error}" );
            $this->returnException( "DB Data Step 3: Failed to update {$this->tmpPrefix}usermeta meta_key database table prefixes {$this->db->last_error}" );
            return false;
        }


        return true;
    }

    /**
     * Update table options active_plugins
     * Update active plugins
     * @return bool
     */
    protected function step4() {


        // Options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $this->log( "DB Data Step 4: Updating {$this->tmpPrefix}options active_plugins" );

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 4: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            $this->returnException( 'DB Data Step 4: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }


        // Get active_plugins from tmp tables
        $activePlugins = $this->db->get_var( "SELECT option_value FROM {$this->tmpPrefix}options WHERE option_name = 'active_plugins' " );
        $activePlugins = unserialize( $activePlugins );


        // Get active_plugins from production site
        $activePluginsProd = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'active_plugins' " );
        $activePluginsProd = unserialize( $activePluginsProd );

        if( !$activePlugins ) {
            $this->log( "DB Data Step 4: Can not get list of active plugins from from {$this->tmpPrefix}options " );
            $this->returnException( "DB Data Step 4: Can not get active_plugins from {$this->tmpPrefix}options" );
        }


        // Disable WP Staging Plugin
        if( ($key = array_search( 'wp-staging/wp-staging.php', $activePlugins )) !== false ) {
            unset( $activePlugins[$key] );
        }
        if( ($key = array_search( 'wp-staging-1/wp-staging.php', $activePlugins )) !== false ) {
            unset( $activePlugins[$key] );
        }
        // Activate WP Staging Pro Plugin
        if( (array_search( 'wp-staging-pro/wp-staging-pro.php', $activePlugins )) === false ) {
            $activePlugins[] = 'wp-staging-pro/wp-staging-pro.php';
        }
        // Activate WP Stagin Hooks Plugin if it is activated on production site
        if( array_search( 'wp-staging-hooks/wp-staging-hooks.php', $activePluginsProd ) !== false && array_search( 'wp-staging-hooks/wp-staging-hooks.php', $activePlugins ) === false ) {
            $activePlugins[] = 'wp-staging-hooks/wp-staging-hooks.php';
        }

        // Update active_plugins
        $resultActivePlugins = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . serialize( $activePlugins ) . "' WHERE option_name = 'active_plugins' "
        );

        if( $resultActivePlugins === false ) {
            $this->log( "DB Data Step 4: Can not update table active_plugins in {$this->tmpPrefix}options" );
            $this->returnException( "DB Data Step 4: Can not update table active_plugins in {$this->tmpPrefix}options - db error: " . $this->db->last_error );
            return false;
        }

        return true;
    }

    /**
     * Update table tmp_usermeta session token
     * @return bool
     */
    protected function step5() {


        // usermeta table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'usermeta' ) ) {
            return true;
        }

        $this->log( "DB Data Step 5: Updating {$this->tmpPrefix}usermeta session_tokens" );

        if( $this->isTable( $this->tmpPrefix . 'usermeta' ) === false ) {
            $this->log( 'DB Data Step 5: Fatal Error ' . $this->tmpPrefix . 'usermeta does not exist', Logger::TYPE_ERROR );
            $this->returnException( 'DB Data Step 5: Fatal Error ' . $this->tmpPrefix . 'usermeta does not exist' );
            return false;
        }

        $userId       = get_current_user_id();
        // Get session token for current user from live site usermeta table
        $sessionToken = $this->db->get_var( "SELECT meta_value FROM {$this->db->base_prefix}usermeta WHERE meta_key = 'session_tokens' AND user_id = '{$userId}'" );

        $sessionToken = unserialize( $sessionToken );

        if( !$sessionToken ) {
            $this->log( "DB Data Step 5: Can not get session token of current user from {$this->db->prefix}usermeta ", Logger::TYPE_WARNING );
        }
        // Update session_tokens
        $resultSessionToken = $this->db->query(
                "UPDATE {$this->tmpPrefix}usermeta SET meta_value = '" . serialize( $sessionToken ) . "' WHERE meta_key = 'session_tokens' AND user_id = {$userId}"
        );

        if( $resultSessionToken === false ) {
            $this->log( "DB Data Step 5: Can not update row session_tokens in {$this->tmpPrefix}usermeta", Logger::TYPE_WARNING );
            return false;
        }

        return true;
    }

    /**
     * Get permalink_structure from live site and copy it to the migrating tmp_tables to keep the current permalink structure
     * Update permalink_structure
     * @return bool
     */
    protected function step6() {


        // options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $this->log( "DB Data Step 6: Updating {$this->tmpPrefix}options permalink_structure" );

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 6: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            $this->returnException( 'DB Data Step 6: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }

        // Get permalink_structure value from live site options table
        $permalink = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'permalink_structure' " );


        if( !$permalink ) {
            $this->log( "DB Data Step 6: Can not get permalink_structure from {$this->db->prefix}options " );
            $permalink = '/%postname%/';
        }
        // Update permalink_structure
        $resultPermalink = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . $permalink . "' WHERE option_name = 'permalink_structure'"
        );

        if( $resultPermalink === false ) {
            $this->log( "DB Data Step 6: Can not update row permalink_structure in {$this->tmpPrefix}options" );
            $this->returnException( "DB Data Step 6: Can not update row permalink_structure in {$this->tmpPrefix}options - db error: " . $this->db->last_error );
            return false;
        }

        return true;
    }

    /**
     * Get original siteurl and home path and copy it to the wpstgtmp table
     * Update permalink_structure
     * @return bool
     */
    protected function step7() {


        // options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $this->log( "DB Data Step 7: Updating {$this->tmpPrefix}options siteurl" );

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 7: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            $this->returnException( 'DB Data Step 7: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }

        // Get siteurl value from live site options table
        $siteUrl = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'siteurl' " );

        if( !$siteUrl ) {
            $this->log( "DB Data Step 7: Can not get siteurl from {$this->db->prefix}options " );
            $this->returnException( "DB Data Step 7: Can not find siteurl in {$this->db->prefix}options" );
        }
        // Update siteurl
        $resultSiteUrl = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . $siteUrl . "' WHERE option_name = 'siteurl'"
        );

        if( $resultSiteUrl === false ) {
            $this->log( "DB Data Step 7: Can not update row siteurl in {$this->tmpPrefix}options" );
            $this->returnException( "DB Data Step 7: Can not update row siteurl in {$this->tmpPrefix}options - db error: " . $this->db->last_error );
            return false;
        }

        // Get home value from live site options table
        $home = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'home' " );

        if( !$home ) {
            $this->log( "DB Data Step 7: Can not get home or is empty from {$this->db->prefix}options " );
            //$this->returnException( "DB Data Step 7: Can not find home or is empty in {$this->db->prefix}options" );
        }
        // Update home
        $resultHome = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . $home . "' WHERE option_name = 'home'"
        );

        if( $resultHome === false ) {
            $this->log( "DB Data Step 7: Can not update row home in {$this->tmpPrefix}options" );
            $this->returnException( "DB Data Step 7: Can not update row home in {$this->tmpPrefix}options - db error: " . $this->db->last_error );
            return false;
        }

        return true;
    }

    /**
     * Get wpstgpro_version from live site and copy it to wpstgtmp_options
     */
    protected function step8() {
        // options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $this->log( "DB Data Step 8: Updating {$this->tmpPrefix}options wpstgpro_version" );

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 8: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            $this->returnException( 'DB Data Step 8: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }

        // Get wpstgpro_version value from live site options table
        $select = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'wpstgpro_version' " );

        if( !$select ) {
            $this->log( "DB Data Step 8: Can not get wpstgpro_version from {$this->db->prefix}options " );
            $this->returnException( "DB Data Step 8: Can not find wpstgpro_version in {$this->db->prefix}options" );
        }
        // Update wpstgpro_version
        $update = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . $select . "' WHERE option_name = 'wpstgpro_version'"
        );

        if( $update === false ) {
            $this->log( "DB Data Step 8: Can not update row wpstgpro_version in {$this->tmpPrefix}options" );
            $this->returnException( "DB Data Step 8: Can not update row wpstgpro_version in {$this->tmpPrefix}options - db error: " . $this->db->last_error );
            return false;
        }

        return true;
    }

    /**
     * Get blog_public from live site and copy it to wpstgtmp_options
     * @return bool
     */
    protected function step9() {
        // options table has been exluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $this->log( "DB Data Step 9: Copy (no)index value from live site to wpstgtmp_options" );

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 9: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            $this->returnException( 'DB Data Step 9: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }

        // Get blog_public value from live site options table
        $result = $this->db->get_var( "SELECT option_value FROM {$this->db->prefix}options WHERE option_name = 'blog_public' " );

        if( !$result ) {
            $this->log( "DB Data Step 9: Can not find blog_public in {$this->db->prefix}options ", Logger::TYPE_WARNING );
            //$this->returnException("DB Data Step 9: Can not get blog_public in {$this->db->prefix}options");
        }
        // Update blog_public
        $update = $this->db->query(
                "UPDATE {$this->tmpPrefix}options SET option_value = '" . $result . "' WHERE option_name = 'blog_public'"
        );

        if( $update === false ) {
            $this->log( "DB Data Step 9: Can not update row blog_public in {$this->tmpPrefix}options", Logger::TYPE_WARNING );
            return true;
        }

        return true;
    }

    /**
     * Preserve data and prevents data from being pushed from staging to production in wp_options
     * @return bool
     */
    protected function step10() {
        $this->log( "DB Data Step 10: Preserve Data in " . $this->db->prefix . "options" );

        // Skip - Table does not exist
        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            return true;
        }

        // options table has been excluded from pushing process so exit here
        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        $sql = '';

        $preserved_option_names = [
            'siteurl',
            'home',
            'wpstg_optimizer_excluded',
            'upload_path',
            SnapshotRepository::OPTION_NAME,
            AccessToken::OPTION_NAME,
        ];

        $preserved_option_names    = apply_filters( 'wpstg_preserved_options', $preserved_option_names );
        $preserved_options_escaped = esc_sql( $preserved_option_names );

        $preserved_options_data = [];

        // Get preserved data in wp_options tables
        $table                                                = $this->db->prefix . 'options';
        $preserved_options_data[$this->tmpPrefix . 'options'] = $this->db->get_results(
                sprintf(
                        "SELECT * FROM `{$table}` WHERE `option_name` IN ('%s')", implode( "','", $preserved_options_escaped )
                ), ARRAY_A
        );

        // Create preserved data queries for options tables
        foreach ( $preserved_options_data as $key => $value ) {
            if( empty( $value ) === false ) {
                foreach ( $value as $option ) {
                    $sql .= $this->db->prepare(
                            "DELETE FROM `{$key}` WHERE `option_name` = %s;\n", $option['option_name']
                    );

                    $sql .= $this->db->prepare(
                            "INSERT INTO `{$key}` ( `option_id`, `option_name`, `option_value`, `autoload` ) VALUES ( NULL , %s, %s, %s );\n", $option['option_name'], $option['option_value'], $option['autoload']
                    );
                }
            }
        }

        $this->debugLog( "DB Data Step 10: Preserve values " . json_encode( $preserved_options_data ), Logger::TYPE_INFO );

        $this->executeSql( $sql );

        return true;
    }

    /**
     * Delete several option from tmp_options and make sure they do not exist on production site after pushing
     * @return boolean
     */
    protected function step12() {

        if( $this->isTableExcluded( $this->options->prefix . 'options' ) ) {
            return true;
        }

        if( $this->isTable( $this->tmpPrefix . 'options' ) === false ) {
            $this->log( 'DB Data Step 12: Fatal Error ' . $this->tmpPrefix . 'options does not exist', Logger::TYPE_ERROR );
            $this->returnException( 'DB Data Step12: Fatal Error ' . $this->tmpPrefix . 'options does not exist' );
            return false;
        }

        $sql = $this->db->prepare(
                "DELETE FROM `{$this->tmpPrefix}options` WHERE `option_name` = %s;\n", 'wpstg_connection'
        );

        $sql .= $this->db->prepare(
                "DELETE FROM `{$this->tmpPrefix}options` WHERE `option_name` = %s;\n", 'wpstg_emails_disabled'
        );

        $this->executeSql( $sql );

        return true;
    }

    /**
     *
     * @param mixed string|array $sqlbatch
     */
    private function executeSql( $sqlbatch ) {
        $queries = array_filter( explode( ";\n", $sqlbatch ) );

        foreach ( $queries as $query ) {
            if( $this->db->query( $query ) === false ) {
                $this->log( "DB Data Warning:  Can not execute query {$query}", Logger::TYPE_WARNING );
            }
        }
        return true;
    }

    /**
     * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
     * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
     * @access public
     * @param  mixed string|array $input The string to escape.
     * @return string
     */
    public function mysql_escape_mimic( $input ) {
        if( is_array( $input ) ) {
            return array_map( __METHOD__, $input );
        }
        if( !empty( $input ) && is_string( $input ) ) {
            return str_replace( ['\\', "\0", "\n", "\r", "'", '"', "\x1a"], ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $input );
        }

        return $input;
    }

}
