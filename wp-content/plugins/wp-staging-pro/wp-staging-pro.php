<?php

/**
 * Plugin Name: WP STAGING PRO
 * Plugin URI: https://wp-staging.com
 * Description: Create a staging clone site for testing & developing
 * Author: WP-STAGING
 * Author URI: https://wordpress.org/plugins/wp-staging
 * Version: 3.1.9
 * Text Domain: wp-staging
 * Domain Path: /languages/
 *
 * @package  WPSTG
 * @category Development, Migrating, Staging
 * @author   WP STAGING
 */

if (!defined("WPINC")) {
    die;
}

if (!defined('WPSTG_PRO_ENTRYPOINT')) {
    define('WPSTG_PRO_ENTRYPOINT', __FILE__);
}

if (version_compare(phpversion(), '5.5.0', '>=')) {
    include_once dirname(__FILE__) . '/bootstrapPro.php';
} else {
    if (!function_exists('wpstg_unsupported_php_version')) {
        function wpstg_unsupported_php_version()
        {
            echo '<div class="notice-warning notice is-dismissible">';
            echo '<p style="font-weight: bold;">' . esc_html__('PHP Version not supported') . '</p>';
            echo '<p>' . esc_html__(sprintf('WPSTAGING requires PHP %s or higher. Your site is running an outdated version of PHP (%s), which requires an update.', '5.5', phpversion()), 'wp-staging') . '</p>';
            echo '</div>';
        }
    }
    add_action('admin_notices', 'wpstg_unsupported_php_version');
}
