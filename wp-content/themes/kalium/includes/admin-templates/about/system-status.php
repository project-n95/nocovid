<?php
/**
 * Kalium WordPress Theme
 *
 * System status page.
 *
 * @var $active_plugins
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="about-kalium__system-status-report">
    <p class="about-kalium__system-status-report-info wp-clearfix">
        <span>Please copy and paste this information in your ticket when contacting support:</span>
        <a href="#" class="button-primary debug-report">Get System Report</a>
    </p>

    <p class="about-kalium__system-status-report-data">
        <textarea id="system-status-report" readonly="readonly"></textarea>
        <button id="system-status-report-button" data-clipboard-target="#system-status-report" class="button">Copy for
            Support
        </button>
    </p>
</div>

<div class="about-kalium__system-status-tables-container">

    <table class="about-kalium__system-status-table widefat">
        <thead>
        <tr>
            <th colspan="3">Theme Information</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Theme Name:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Theme name in use."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'theme_name' ) ); ?></th>
        </tr>
        <tr>
            <td>Theme Version:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Current version of the theme (parent theme)."></i>
            </td>
            <th><?php Laborator_System_Status::display_theme_version(); ?></th>
        </tr>
        <tr>
            <td>Previous Versions:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Previous installed versions of the theme."></i>
            </td>
            <th><?php Laborator_System_Status::display_previous_theme_version(); ?></th>
        </tr>
        <tr>
            <td>Theme Directory:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Relative directory path of the theme."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'theme_directory' ) ); ?></th>
        </tr>
        <tr>
            <td>Child Theme:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Is the child theme in use?"></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::yes_null( Laborator_System_Status::get_var( 'is_child_theme' ) ) ); ?></th>
        </tr>
        <tr>
            <td>Theme Registered:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Is the theme registered?"></i>
            </td>
            <th><?php echo Laborator_System_Status::yes_no_icon( Laborator_System_Status::get_var( 'theme_is_registered' ) ); ?></th>
        </tr>
        </tbody>
    </table>

    <table class="about-kalium__system-status-table widefat">
        <thead>
        <tr>
            <th colspan="3">WordPress Environment</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Home URL:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The URL of your site's homepage."></i>
            </td>
            <th><?php echo esc_url( Laborator_System_Status::get_var( 'wp_home_url' ) ); ?></th>
        </tr>
        <tr>
            <td>Site URL:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The root URL of your WordPress installation."></i>
            </td>
            <th><?php echo esc_url( Laborator_System_Status::get_var( 'wp_site_url' ) ); ?></th>
        </tr>
        <tr>
            <td>WordPress Path:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="System path of your WordPress root directory."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'wp_abspath' ) ); ?></th>
        </tr>
        <tr>
            <td>WordPress Content Path:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="System path of your wp-content directory."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'wp_content_dir' ) ); ?></th>
        </tr>
        <tr>
            <td>WordPress Version:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The version of WordPress installed on your site."></i>
            </td>
            <th><?php Laborator_System_Status::display_wp_version(); ?></th>
        </tr>
        <tr>
            <td>WordPress Multisite:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Whether or not you have WordPress Multisite enabled."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::yes_null( Laborator_System_Status::get_var( 'wp_multisite' ) ) ); ?></th>
        </tr>
        <tr>
            <td>WordPress Memory Limit:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The maximum amount of memory (RAM) that your site can use at one time."></i>
            </td>
            <th><?php Laborator_System_Status::display_memory_limit(); ?></th>
        </tr>
        <tr>
            <td>WordPress Debug Mode:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Displays whether or not WordPress is in Debug Mode."></i>
            </td>
            <th><?php echo Laborator_System_Status::yes_null_icon( Laborator_System_Status::get_var( 'wp_debug' ) ); ?></th>
        </tr>
        <tr>
            <td>WordPress Script Debug:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Tells whether the WordPress assets (scripts and styles) are loaded separately."></i>
            </td>
            <th><?php echo Laborator_System_Status::yes_null_icon( Laborator_System_Status::get_var( 'wp_script_debug' ) ); ?></th>
        </tr>
        <tr>
            <td>Language:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The current language used by WordPress."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'wp_language' ) ); ?></th>
        </tr>
        </tbody>
    </table>

    <table class="about-kalium__system-status-table widefat">
        <thead>
        <tr>
            <th colspan="3">Server Environment</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Server info:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Information about the web server that is currently hosting your site."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'server_info' ) ); ?></th>
        </tr>
        <tr>
            <td>PHP version:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The version of PHP installed on your hosting server."></i>
            </td>
            <th><?php Laborator_System_Status::display_php_version(); ?></th>
        </tr>
        <tr>
            <td>PHP post max size:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The largest file size that can be contained in one post."></i>
            </td>
            <th><?php echo esc_html( size_format( Laborator_System_Status::get_var( 'php_post_max_size' ) ) ); ?></th>
        </tr>
        <tr>
            <td>PHP time limit:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)."></i>
            </td>
            <th><?php Laborator_System_Status::display_php_max_execution_time() ?></th>
        </tr>
        <tr>
            <td>PHP max input vars:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The maximum number of variables your server can use for a single function to avoid overloads."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'max_input_vars' ) ); ?></th>
        </tr>
        <tr>
            <td>Max upload size:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The largest file size that can be uploaded to your WordPress installation."></i>
            </td>
            <th><?php echo size_format( Laborator_System_Status::get_var( 'max_upload_size' ) ); ?></th>
        </tr>
        <tr>
            <td>MySQL version:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The version of MySQL installed on your server."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'mysql_version' ) ); ?></th>
        </tr>
        <tr>
            <td>cURL version:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="The version of cURL installed on your server."></i>
            </td>
            <th><?php echo esc_html( Laborator_System_Status::get_var( 'curl_version' ) ); ?></th>
        </tr>
        <tr>
            <td>DOMDocument:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="DOMDocument is required for the Demo Content Importer plugin to properly function."></i>
            </td>
            <th><?php echo Laborator_System_Status::yes_no_icon( Laborator_System_Status::get_var( 'domdocument' ) ); ?></th>
        </tr>
        <tr>
            <td>WP remote post:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Kalium uses this method to communicate with different APIs such as Laborator API Server and Envato API."></i>
            </td>
            <th><?php Laborator_System_Status::display_test_wp_remote_post(); ?></th>
        </tr>
        <tr>
            <td>WP remote get</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Kalium uses this method to communicate with different APIs such as Laborator API Server and Envato API."></i>
            </td>
            <th><?php echo Laborator_System_Status::yes_no_icon( Laborator_System_Status::test_wp_remote_get() ); ?></th>
        </tr>
        <tr>
            <td>GD library:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="GD Library is a program installed on your server that allows programs to manipulate graphics."></i>
            </td>
            <th><?php Laborator_System_Status::display_gd_library(); ?></th>
        </tr>
        </tbody>
    </table>

    <table class="about-kalium__system-status-table widefat">
        <thead>
        <tr>
            <th colspan="3">Security</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Secure connection (HTTPS):</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Does your site uses SSL certificate for secure connection"></i>
            </td>
            <th><?php Laborator_System_Status::display_secure_connection(); ?></th>
        </tr>
        <tr>
            <td>Hide errors from visitors:</td>
            <td class="help">
                <i class="dashicons dashicons-info" title="Error messages can contain sensitive information about your website. These should be hidden from untrusted visitors."></i>
            </td>
            <th><?php Laborator_System_Status::display_hide_errors(); ?></th>
        </tr>
        </tbody>
    </table>

    <table class="about-kalium__system-status-table widefat">
        <thead>
        <tr>
            <th colspan="3">Active plugins (<?php echo count( $active_plugins ); ?>)</th>
        </tr>
        </thead>
        <tbody>
		<?php
		if ( ! empty( $active_plugins ) ) :

			foreach ( $active_plugins as $plugin ) :
				$plugin_name = kalium_get_array_key( $plugin, 'Name' );
				$plugin_uri = kalium_get_array_key( $plugin, 'PluginURI' );
				$plugin_version = kalium_get_array_key( $plugin, 'Version' );
				$plugin_author = kalium_get_array_key( $plugin, 'Author' );

				// Display plugin name and author
				$display_plugin_name        = $plugin_uri ? sprintf( '<a href="%s" title="Visit plugin homepage" target="_blank" rel="noreferrer noopener">%s</a>', $plugin_uri, esc_html( $plugin_name ) ) : esc_html( $plugin_name );
				$display_version_and_author = sprintf( 'by %s &ndash; %s', links_add_target( $plugin_author ), $plugin_version );

				?>
                <tr>
                    <td><?php echo $display_plugin_name; ?></td>
                    <th colspan="2"><?php echo $display_version_and_author; ?></th>
                </tr>
			<?php
			endforeach;

		else : ?>
            <tr>
                <td colspan="3">There are no active plugins in your site.</td>
            </tr>
		<?php
		endif; ?>
        </tbody>
    </table>

</div>