<?php
/**
 * Kalium WordPress Theme
 *
 * Theme upgrader.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * @property string $admin_page Current admin screen.
 */
class Kalium_Theme_Upgrader {

	/**
	 * Theme Backups Folder.
	 *
	 * @var string
	 */
	public static $backup_folder = '';

	/**
	 * Theme backup file name.
	 *
	 *
	 * @var string
	 */
	public static $backup_file_name = 'kalium-{version}-{date}.zip';

	/**
	 * Theme ID based on folder name.
	 *
	 * @var string
	 */
	private $theme_id = 'kalium';

	/**
	 * Update data.
	 *
	 * @var array
	 */
	private $update_data;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$uploads_dir         = wp_upload_dir();
		self::$backup_folder = $uploads_dir['basedir'];

		// Hooks
		add_action( 'after_setup_theme', [ $this, '_after_setup_theme' ] );
		add_action( 'admin_init', [ $this, '_theme_update_notice' ] );
	}

	/**
	 * After setup theme.
	 */
	public function _after_setup_theme() {

		// Retrieve remote version data
		$this->retrieve_version_data();

		// Theme Version Checker
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check' ], 100 );
		add_filter( 'pre_set_transient_update_themes', [ $this, 'check' ], 100 );

		// Theme Upgrader Information
		add_filter( 'upgrader_pre_download', [ $this, '_before_updating_theme_filter' ], 1000, 3 );
		add_action( 'upgrader_process_complete', [ $this, '_after_theme_update_filter' ], 100, 2 );

		// Theme update notices
		add_action( 'admin_footer', [ $this, 'display_theme_update_notices' ], 10 );

		// Theme Updated Redirect to What's New
		if ( true == get_option( 'kalium_updated' ) && ( is_admin() && ! defined( 'DOING_AJAX' ) && 'laborator_options' !== kalium()->request->query( 'page' ) ) ) {
			wp_redirect( admin_url( 'admin.php?page=kalium&tab=whats-new&updated' ) );
			delete_option( 'kalium_updated' );
			exit;
		}
	}

	/**
	 * Theme update notice.
	 *
	 * @return void
	 */
	public function _theme_update_notice() {
		global $pagenow;
		$themes           = get_theme_updates();
		$theme_version_id = 'theme-version-' . kalium()->get_version( true );

		if ( 'update-core.php' !== $pagenow && isset( $themes[ $this->theme_id ] ) && ! get_theme_mod( $theme_version_id, false ) ) {
			$theme_update = $themes[ $this->theme_id ];

			if ( isset( $theme_update->update['new_version'] ) && version_compare( $theme_update->update['new_version'], kalium()->get_version(), '>' ) ) {
				$new_version     = $theme_update->update['new_version'];
				$current_version = kalium()->get_version();
				$update_url      = admin_url( 'update-core.php' );

				if ( ! kalium()->theme_license->get_license() ) {
					$update_url = Kalium_About::get_tab_link( 'theme-registration' );
				}

				// Update Notification Dismiss
				$dismiss_update_notification_name = 'laborator_dismiss_update_notification';

				kalium()->helpers->add_admin_notice( sprintf( '<a href="%s" class="notice-dismiss"></a>There is an update for <strong>%s</strong> theme, your current version is <strong>%s</strong> and latest version is <strong>%s</strong>. <a href="%s">Go to Updates page &raquo;</a>', add_query_arg( [ $dismiss_update_notification_name => wp_create_nonce( $theme_version_id ) ] ), $theme_update, $current_version, $new_version, $update_url ), 'warning', false );

				if ( isset( $_GET[ $dismiss_update_notification_name ] ) && check_admin_referer( $theme_version_id, $dismiss_update_notification_name ) ) {
					set_theme_mod( $theme_version_id, true );
					wp_redirect( remove_query_arg( $dismiss_update_notification_name ) );
					die();
				}
			}
		}
	}

	/**
	 * Check for newer versions.
	 *
	 * @param $transient
	 *
	 * @return mixed
	 */
	public function check( $transient ) {
		if ( empty( $transient->checked[ $this->theme_id ] ) ) {
			return $transient;
		}

		// Get latest theme version
		$update_data = $this->update_data;

		if ( ! empty( $update_data['version_data'] ) && version_compare( $update_data['current_version'], kalium()->get_version(), '>' ) ) {
			$transient->response[ $this->theme_id ] = (array) $update_data['version_data'];
		}

		return $transient;
	}

	/**
	 * Before updating the theme, show necesarry information.
	 *
	 * @param bool        $reply
	 * @param string      $package
	 * @param WP_Upgrader $updater
	 *
	 * @return string|WP_Error
	 */
	public function _before_updating_theme_filter( $reply, $package, $updater ) {
		$license = kalium()->theme_license->get_license();

		// Check if its Theme_Upgrader object
		if ( ! $updater instanceof Theme_Upgrader || ! isset( $updater->skin->theme_info ) || 'kalium' !== $updater->skin->theme_info->get( 'TextDomain' ) ) {
			return $reply;
		}

		// Theme is not activated
		if ( ! $license ) {
			return new WP_Error( 'product_not_activated', sprintf( 'Theme is not registered, please <a href="%s" target="_parent">register the theme</a> before updating it.', Kalium_About::get_tab_link( 'theme-registration' ) ) );
		} // Check license status
		else {
			$response = wp_remote_post( kalium()->theme_license->get_api_server_url(), [
				'body' => [
					'action'      => 'license-status',
					'theme_id'    => 'kalium',
					'license_key' => $license->license_key
				],
			] );

			$response_body = json_decode( wp_remote_retrieve_body( $response ) );

			// Show update errors
			if ( $response_body->has_errors ) {
				return new WP_Error( 'product_license_errors', $response_body->error_msg );
			} // Download permitted
			else {

				// Backup File name and Path
				$file_name        = str_replace( [ '{version}', '{date}' ], [
					kalium()->get_version( true ),
					date( 'dmy-Hi' )
				], self::$backup_file_name );
				$backup_file_path = self::$backup_folder . '/' . $file_name;

				// Feedback strings
				$updater->strings['creating_theme_backup'] = 'Creating theme backup&hellip;<br>';
				$updater->strings['theme_backup_created']  = sprintf( 'Backup file created in <strong>%s</strong><br>', str_replace( ABSPATH, '', $backup_file_path ) );
				$updater->strings['product_update_valid']  = 'License key and WP site is permitted, download can start.<br>';

				// Create Theme Backup
				if ( $license->save_backups ) {

					// Creating Theme Backup message
					$updater->skin->feedback( 'creating_theme_backup' );

					// Initialize Filesystem API
					kalium()->filesystem->initialize();

					// Zip current version of theme
					$result = kalium()->filesystem->zip_file( kalium()->locate_file(), $backup_file_path );

					// On error
					if ( is_wp_error( $result ) ) {
						return new WP_Error( 'theme_backup_creation_error', '<span>Cannot create theme backup, upgrade process failed.</span>' );
					}

					// Theme backup created
					$updater->skin->feedback( 'theme_backup_created' );

					// Valid Product Update Feedback
					$updater->skin->feedback( 'product_update_valid' );
				}
			}

		}

		return $reply;
	}

	/**
	 * Theme update was successful.
	 *
	 * @param WP_Upgrader $updater
	 * @param array       $data
	 */
	public function _after_theme_update_filter( $updater, $data ) {

		// Check if current theme is updated
		if ( 'update' === $data['action'] && 'theme' === $data['type'] && $updater instanceof Theme_Upgrader && 'kalium' === $updater->skin->theme_info->get( 'TextDomain' ) ) {

			// Stop on error...!
			if ( ! $updater->skin->result || is_wp_error( $updater->skin->result ) ) {
				return;
			}

			// Kalium updated
			update_option( 'kalium_updated', true );

			// Clean update cache
			$update_data = get_option( 'kalium_upgrader', [
				'last_check' => 0,
			] );

			$update_data['current_version'] = '';
			$update_data['version_data']    = [];

			// Add Kalium upgrader data
			update_option( 'kalium_upgrader', $update_data );
		}
	}

	/**
	 * Get theme update notice to warn user about import update changes.
	 */
	public function get_theme_update_notice() {
		$update_themes = get_site_transient( 'update_themes' );

		if ( empty( $update_themes ) ) {
			$update_themes = get_transient( 'update_themes' );
		}

		if ( $update_themes && ! empty( $update_themes->response['kalium']['update_notice'] ) ) {
			return wp_kses( $update_themes->response['kalium']['update_notice'], [
				'a'      => [
					'href'   => [],
					'target' => [],
					'title'  => []
				],
				'strong' => [],
				'br'     => []
			] );
		}

		return null;
	}

	/**
	 * Show theme update notices.
	 */
	public function display_theme_update_notices() {
		global $pagenow;

		if ( current_user_can( 'update_themes' ) && in_array( $pagenow, [ 'update-core.php', 'themes.php' ] ) ) {
			$update_message = $this->get_theme_update_notice();

			kalium_enqueue( 'theme-update-notice', 'admin/js/theme-update-notice.min.js' );

			if ( $update_message ) {
				wp_localize_script( 'kalium-theme-update-notice', 'kaliumThemeUpdateNotice', [
					'updateMessage' => $update_message
				] );
			}
		}
	}

	/**
	 * Retrieve version data from server.
	 */
	private function retrieve_version_data() {
		global $pagenow;

		if ( ! is_admin() ) {
			return;
		}

		$update_data = get_option( 'kalium_upgrader', [
			'current_version' => '',
			'version_data'    => [],
			'last_check'      => 0
		] );

		$check_interval = 86400; // 1 day

		// Force check updates (lower interval)
		if ( 'update-core.php' == $pagenow && kalium()->request->query( 'force-check', true ) ) {
			$check_interval = 300; // 5 minutes
			delete_site_transient( 'update_themes' );
		}

		// Check for updates
		if ( $update_data['last_check'] < ( time() - $check_interval ) ) {

			// Get latest theme version
			$response = wp_remote_post( kalium()->theme_license->get_api_server_url(), [
				'body' => [
					'version_check'   => 'kalium',
					'current_version' => kalium()->get_version(),
					'license_key'     => kalium()->theme_license->get_license_key(),
				]
			] );

			$response_body = wp_remote_retrieve_body( $response );

			// Version data
			$version_data = json_decode( $response_body );

			if ( is_object( $version_data ) ) {
				// Version data
				$update_data['version_data'] = $version_data;

				// Current version
				$update_data['current_version'] = $version_data->new_version;

				// Set last checked
				$update_data['last_check'] = time();
			}

			// Update data
			update_option( 'kalium_upgrader', $update_data );
		}

		// Set update data
		$this->update_data = $update_data;
	}
}