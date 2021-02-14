<?php

namespace WPStaging\Backend\Pro\Licensing;

// No Direct Access
if( !defined( "WPINC" ) ) {
    die;
}

class Version {

    public function __construct() {
        // Load some hooks
        add_action( 'wpstg_daily_event', ['WPStaging\Backend\Pro\Licensing\Version', 'daily_version_check'] );

        // For testing daily_version_check, uncomment this line
        //add_action( 'admin_init', array( 'WPStaging\Backend\Pro\Licensing\Version', 'daily_version_check' ) );

        // this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
        if( !defined( 'WPSTG_STORE_URL' ) )
            define( 'WPSTG_STORE_URL', 'https://wp-staging.com' );
    }

    /**
     * Check if license key is valid once per week
     *
     * @access  public
     * @since   2.0.3
     * @return  void
     */
    public function daily_version_check() {
        // data to send in our API request
        $api_params = [
            'edd_action' => 'get_version',
            'item_id'    => 11
        ];
        // Call the API
        $response   = wp_remote_post(
                WPSTG_STORE_URL, [
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params
                ]
        );
        // make sure the response came back okay
        if( is_wp_error( $response ) ) {
            return false;
        }
        $license = json_decode( wp_remote_retrieve_body( $response ) );
        update_option( 'wpstg_version_latest', $license->stable_version );
    }

}
