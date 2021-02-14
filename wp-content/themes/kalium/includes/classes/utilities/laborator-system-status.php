<?php
/**
 * Kalium WordPress Theme
 *
 * System status class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Laborator_System_Status {

	/**
	 * Minimum required memory_limit (in bytes).
	 *
	 * @const int
	 */
	const MIN_MEMORY_LIMIT = 134217728; // 128 MB

	/**
	 * Minimum required phpversion.
	 *
	 * @const string
	 */
	const MIN_PHP_VERSION = '7.3';

	/**
	 * System status vars.
	 *
	 * @var array
	 */
	private static $system_status_vars = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Init vars.
	 *
	 * @return void
	 */
	public static function init_vars() {
		global $wpdb;

		// Theme name
		self::$system_status_vars['theme_name'] = wp_get_theme()->display( 'Name' );

		// Theme version
		self::$system_status_vars['theme_version'] = kalium()->get_version();

		// Theme directory
		self::$system_status_vars['theme_directory'] = kalium()->get_theme_dir( '~/' );

		// Check if current theme is child theme
		self::$system_status_vars['is_child_theme'] = is_child_theme();

		// Check if theme is registered
		self::$system_status_vars['theme_is_registered'] = kalium()->theme_license->is_theme_registered();

		// WordPress Home URL
		self::$system_status_vars['wp_home_url'] = home_url();

		// WordPress Site URL
		self::$system_status_vars['wp_site_url'] = site_url();

		// WordPress Absolute Path
		self::$system_status_vars['wp_abspath'] = ABSPATH;

		// WordPress Content Dir
		self::$system_status_vars['wp_content_dir'] = WP_CONTENT_DIR;

		// WordPress Version
		self::$system_status_vars['wp_version'] = $GLOBALS['wp_version'];

		// WordPress Multisite
		self::$system_status_vars['wp_multisite'] = is_multisite();

		// WordPress Memory Limit
		$wp_memory_limit = kalium()->helpers->let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, kalium()->helpers->let_to_num( @ini_get( 'memory_limit' ) ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		self::$system_status_vars['wp_memory_limit'] = $wp_memory_limit;

		// WordPress Debug
		self::$system_status_vars['wp_debug'] = defined( 'WP_DEBUG' ) && WP_DEBUG;

		// WordPress Script Debug
		self::$system_status_vars['wp_script_debug'] = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		// WordPress Language
		self::$system_status_vars['wp_language'] = get_locale();

		// Server info
		self::$system_status_vars['server_info'] = $_SERVER['SERVER_SOFTWARE'];

		// PHP version
		self::$system_status_vars['php_version'] = phpversion();

		// PHP post max size
		self::$system_status_vars['php_post_max_size'] = kalium()->helpers->let_to_num( ini_get( 'post_max_size' ) );

		// PHP max execution time
		self::$system_status_vars['max_execution_time'] = (int) ini_get( 'max_execution_time' );

		// PHP input vars
		self::$system_status_vars['max_input_vars'] = (int) ini_get( 'max_input_vars' );

		// Max upload size
		self::$system_status_vars['max_upload_size'] = wp_max_upload_size();

		// MySQL version
		self::$system_status_vars['mysql_version'] = 'N/A';

		if ( ! empty( $wpdb->is_mysql ) ) {

			if ( $wpdb->use_mysqli ) {
				$server_info = mysqli_get_server_info( $wpdb->dbh );
			} else {
				$server_info = mysql_get_server_info( $wpdb->dbh );
			}

			self::$system_status_vars['mysql_version'] = $server_info;
		}

		// Curl Version
		$curl_version = '';

		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		} elseif ( extension_loaded( 'curl' ) ) {
			$curl_version = 'cURL installed but unable to retrieve version.';
		}

		self::$system_status_vars['curl_version'] = $curl_version;

		// DOMDocument
		self::$system_status_vars['domdocument'] = class_exists( 'DOMDocument' );

		// GD library
		self::$system_status_vars['gd_library'] = extension_loaded( 'gd' ) && function_exists( 'gd_info' );

		if ( self::$system_status_vars['gd_library'] ) {
			$gd_info = gd_info();

			if ( isset( $gd_info['GD Version'] ) && preg_match( '/(?<version>[\d.]+)+/', $gd_info['GD Version'], $matches ) ) {
				self::$system_status_vars['gd_library'] = $matches['version'];
			}
		}

		// Secure connection
		self::$system_status_vars['secure_connection'] = 'https' === substr( home_url(), 0, 5 );

		// Hide errors from visitors
		self::$system_status_vars['hide_errors'] = ! ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG && WP_DEBUG_DISPLAY ) || 0 === intval( ini_get( 'display_errors' ) );
	}

	/**
	 * Test wp_remote_get method.
	 *
	 * @return bool
	 */
	public static function test_wp_remote_get() {
		$result = false;

		// Envato API Check
		$response = wp_safe_remote_get( 'https://build.envato.com/api/', [
			'timeout'    => 60,
			'compress'   => false,
			'decompress' => false,
			'user-agent' => 'kalium-test-wp-remote-get'
		] );

		// Check if site returns 200 status code
		if ( ! is_wp_error( $response ) && 200 === $response['response']['code'] ) {
			$result = true;
		}

		return $result;
	}

	/**
	 * Test wp_remote_post method.
	 *
	 * @return bool
	 */
	public static function test_wp_remote_post() {
		$result = false;

		$response = wp_safe_remote_post( kalium()->theme_license->get_api_server_url(), [
			'timeout'    => 60,
			'compress'   => false,
			'decompress' => false,
			'body'       => [
				'system' => 'hello'
			]
		] );

		if ( ! is_wp_error( $response ) && 200 === $response['response']['code'] ) {
			$response_body = wp_remote_retrieve_body( $response );
			$response_json = json_decode( $response_body );

			if ( false === is_null( $response_json ) && ! empty( $response_json->success ) ) {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Get system status var.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public static function get_var( $name ) {

		if ( isset( self::$system_status_vars[ $name ] ) ) {
			return self::$system_status_vars[ $name ];
		}

		return null;
	}

	/**
	 * Get a list of plugins active on the site.
	 *
	 * @return array
	 */
	public static function get_active_plugins() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! function_exists( 'get_plugin_data' ) ) {
			return [];
		}

		$active_plugins      = (array) get_option( 'active_plugins', [] );
		$active_plugins_data = [];

		if ( is_multisite() ) {
			$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', [] ) );
			$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
		}

		foreach ( $active_plugins as $plugin ) {
			$active_plugins_data[] = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		}

		return $active_plugins_data;
	}

	/**
	 * Yes/no var parser based on boolean value parameter.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function yes_no( $value ) {
		return boolval( $value ) ? 'Yes' : 'No';
	}

	/**
	 * Yes/null var parser based on boolean value parameter.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function yes_null( $value ) {
		return boolval( $value ) ? 'Yes' : '-';
	}

	/**
	 * Yes/no var parser with icon based on boolean value parameter.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function yes_no_icon( $value ) {

		$value = boolval( $value );

		$class = [
			'dashicons',
			$value ? 'dashicons-yes' : 'dashicons-no',
		];

		return sprintf(
			'<mark class="%s"><i class="%s"></i></mark>',
			$value ? 'yes' : 'no',
			kalium()->helpers->list_classes( $class )
		);
	}

	/**
	 * Yes/no var parser with icon (on true) based on boolean value parameter.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function yes_null_icon( $value ) {

		if ( boolval( $value ) ) {
			$class = [
				'dashicons',
				'dashicons-yes',
			];

			return sprintf( '<mark class="yes"><i class="%s"></i></mark>', kalium()->helpers->list_classes( $class ) );
		}

		return '-';
	}

	/**
	 * Green colored text.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function green_text( $text ) {
		return sprintf( '<mark class="yes">%s</mark>', wp_kses_post( $text ) );
	}

	/**
	 * Red colored text.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function red_text( $text ) {
		return sprintf( '<mark class="no">%s</mark>', wp_kses_post( $text ) );
	}

	/**
	 * Display Kalium version.
	 */
	public static function display_theme_version() {
		$theme_version = self::get_var( 'theme_version' );

		// Retrieve latest version available for Kalium
		$version_check = wp_remote_get( 'https://api.laborator.co/?version_check=kalium' );
		$api_response  = json_decode( wp_remote_retrieve_body( $version_check ), true );

		if ( isset( $api_response, $api_response['new_version'] ) && version_compare( $theme_version, $api_response['new_version'], '<' ) ) {
			echo self::red_text( sprintf( '%s - There is a newer version of Kalium available (<strong>%s</strong>).', $theme_version, $api_response['new_version'] ) );
			echo sprintf( ' <a href="%s" target="_blank" rel="noreferrer noopener">Learn more how to update the theme &raquo;</a>', 'https://documentation.laborator.co/kb/kalium/updating-the-theme/' );
		} else {
			echo self::green_text( $theme_version );
		}
	}

	/**
	 * Display previous theme versions.
	 */
	public static function display_previous_theme_version() {
		if ( Kalium_Version_Upgrades::instance()->get_previous_version() ) {
			$theme_version         = self::get_var( 'theme_version' );
			$versions_list         = array_reverse( Kalium_Version_Upgrades::instance()->get_versions_list() );
			$previous_version_list = [];

			foreach ( $versions_list as $version ) {
				if ( version_compare( $version, $theme_version, '<' ) ) {
					$previous_version_list[] = $version;
				}
			}

			echo implode( ' &rarr; ', array_reverse( $previous_version_list ) );
		} else {
			echo '-';
		}
	}

	/**
	 * Display WordPress version.
	 *
	 * @return void
	 */
	public static function display_wp_version() {
		$wp_version = self::get_var( 'wp_version' );

		// Retrieve latest version available for WordPress
		$version_check = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
		$api_response  = json_decode( wp_remote_retrieve_body( $version_check ), true );

		if ( $api_response && isset( $api_response['offers'], $api_response['offers'][0], $api_response['offers'][0]['version'] ) ) {
			$latest_version = $api_response['offers'][0]['version'];
		} else {
			$latest_version = null;
		}

		// Display WP version
		if ( is_null( $latest_version ) ) {
			echo sprintf( '%s - Your server doesn\'t allow to check for latest version of WordPress.', $wp_version );
		} else if ( version_compare( $wp_version, $latest_version, '>=' ) ) {
			echo self::green_text( $wp_version );
		} else {
			echo sprintf( '%s - There is a newer version of WordPress available (%s).', self::red_text( $wp_version ), $latest_version );
		}
	}

	/**
	 * Display memory limit.
	 *
	 * @return void
	 */
	public static function display_memory_limit() {

		// Memory limit
		$memory_limit           = self::get_var( 'wp_memory_limit' );
		$memory_limit_formatted = size_format( $memory_limit );

		// Memory limit is Okay
		if ( $memory_limit >= self::MIN_MEMORY_LIMIT ) {
			echo self::green_text( $memory_limit_formatted );
		} else {
			echo self::red_text( sprintf( '%s - We recommend setting memory to at least <strong>%s</strong>. See: %s', $memory_limit_formatted, size_format( self::MIN_MEMORY_LIMIT ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank"> Increasing memory allocated to PHP </a>' ) );
		}
	}

	/**
	 * Display PHP version.
	 *
	 * @return void
	 */
	public static function display_php_version() {

		// PHP version
		$php_version = self::$system_status_vars['php_version'];

		// Minimum required PHP version by the theme
		if ( version_compare( $php_version, self::MIN_PHP_VERSION, '>=' ) ) {
			echo self::green_text( $php_version );
		} // Under PHP 7.2
		else if ( version_compare( $php_version, '7.3', '<' ) ) {

			$php_version_display = $php_version;

			if ( version_compare( $php_version, '7', '<' ) ) {
				$php_version_display = self::red_text( $php_version );
			}

			// Make the php version bold
			$php_version_display = sprintf( '<strong>%s</strong>', $php_version_display );

			echo sprintf( '%s - We recommend using PHP version 7.3 or above for greater performance and security. <a href="%s" target="_blank" rel="noreferrer noopener">How to update your PHP version &raquo;</a>', $php_version_display, 'https://documentation.laborator.co/kb/general/how-to-upgrade-php-version-and-increase-server-limits/' );
		}
	}

	/**
	 * Display max execution time.
	 *
	 * @return void
	 */
	public static function display_php_max_execution_time() {
		$max_execution_time          = self::get_var( 'max_execution_time' );
		$demo_content_execution_time = 90;

		// Correct execution time
		if ( $max_execution_time >= 30 ) {
			$max_execution_time_green = self::green_text( $max_execution_time );

			if ( $max_execution_time < $demo_content_execution_time ) {
				echo sprintf( '%s - We recommend setting max execution time at least <strong>%d</strong> when you are importing demo content. See: <a href="%s" target="_blank" rel="noopener noreferrer">Increasing max execution to PHP</a>', $max_execution_time_green, $demo_content_execution_time, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' );
			} else {
				echo $max_execution_time_green;
			}
		} else {
			echo sprintf( '%s - Minimum recommended PHP execution time limit should be 30 seconds.', self::red_text( $max_execution_time ) );
		}
	}

	/**
	 * Display test remote post method.
	 *
	 * @return void
	 */
	public static function display_test_wp_remote_post() {
		$wp_remote_post = self::test_wp_remote_post();

		// Display wp_remote_post status
		echo self::yes_no_icon( $wp_remote_post );

		// When wp_remote_post is not working
		if ( ! $wp_remote_post ) {
			echo sprintf( 'Laborator API server is not accessible at this url <strong>%s</strong>', kalium()->theme_license->get_api_server_url() );
		}
	}

	/**
	 * Display GD library info.
	 *
	 * @return void
	 */
	public static function display_gd_library() {
		$gd_library = self::get_var( 'gd_library' );

		// GD library status
		if ( $gd_library ) {
			echo self::green_text( $gd_library );
		} else {
			echo self::yes_no_icon( $gd_library );
		}
	}

	/**
	 * Display secure connection.
	 *
	 * @return void
	 */
	public static function display_secure_connection() {
		$secure_connection = self::get_var( 'secure_connection' );

		// Secure connection state
		echo self::yes_no_icon( $secure_connection );

		// More information
		if ( false === $secure_connection ) {
			echo self::red_text( ' Your site is not using secure connection (HTTPS).' );
		}
	}

	/**
	 * Display hide errors.
	 *
	 * @return void
	 */
	public static function display_hide_errors() {
		$hide_errors = self::get_var( 'hide_errors' );

		// Hide errors state
		echo self::yes_no_icon( $hide_errors );

		// More information
		if ( false === $hide_errors ) {
			echo self::red_text( ' Error messages should not be shown to visitors.' );
		}
	}
}