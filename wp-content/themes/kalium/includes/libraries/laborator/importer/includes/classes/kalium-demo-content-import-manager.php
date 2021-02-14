<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Import Manager class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Kalium_Demo_Import_Manager class.
 */
class Kalium_Demo_Import_Manager {

	/**
	 * Instance, as this is a singleton class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Reference to TGM_Plugin_Activation.
	 *
	 * @var TGM_Plugin_Activation
	 */
	private $tgmpa;

	/**
	 * Content pack instance.
	 *
	 * @var Kalium_Demo_Content_Pack
	 */
	private $content_pack;

	/**
	 * Create instance of Import Manager.
	 *
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return self
	 */
	public static function get_instance( $content_pack = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		// Set content pack in use
		if ( ! is_null( $content_pack ) ) {
			self::$instance->set_content_pack( $content_pack );
		}

		// Register required plugins
		self::$instance->register_required_plugins();

		return self::$instance;
	}

	/**
	 * Get content pack.
	 *
	 * @return Kalium_Demo_Content_Pack|null
	 */
	public function get_content_pack() {
		return $this->content_pack;
	}

	/**
	 * Set content pack currently in use.
	 *
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return void
	 */
	public function set_content_pack( $content_pack ) {
		if ( $content_pack instanceof Kalium_Demo_Content_Pack ) {
			$this->content_pack = $content_pack;
		} else {
			kalium_doing_it_wrong( __FUNCTION__, 'Import manager requires an instance of `Kalium_Demo_Content_Pack` as content pack!', '3.0' );
		}
	}

	/**
	 * Get import instance.
	 *
	 * @return Kalium_Demo_Import_Instance|null
	 */
	public function get_import_instance() {
		return $this->get_content_pack()->get_import_instance();
	}

	/**
	 * Register required plugins in TGMPA.
	 *
	 * @return void
	 */
	public function register_required_plugins() {

		// Plugins registered by the theme already
		$theme_plugins = [];

		foreach ( kalium()->theme_plugins->get_plugins_list() as $theme_plugin ) {
			$theme_plugins[] = $theme_plugin['slug'];
		}

		// Register plugins that are not installed
		if ( $this->content_pack ) {
			foreach ( $this->content_pack->get_required_plugins() as $plugin_slug => $plugin ) {

				// Only if its not installed and not registered in theme
				if ( false === kalium()->is->plugin_installed( $plugin['basename'] ) && false === in_array( $plugin_slug, $theme_plugins ) ) {

					// Register plugin
					$this->tgmpa->register( [
						'name' => $plugin['name'],
						'slug' => $plugin_slug,
					] );
				}
			}
		}
	}

	/**
	 * Get content pack download path.
	 *
	 * @return string
	 */
	public function get_content_pack_download_path() {
		$upload_dir = wp_upload_dir();

		return sprintf( '%1$s/kalium-demo-content/%2$s', $upload_dir['basedir'], $this->get_content_pack()->get_id() );
	}

	/**
	 * Get source path.
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public function get_content_pack_import_source_path( $file_name ) {
		return sprintf( '%1$s/%2$s-%3$s', $this->get_content_pack_download_path(), $this->get_import_instance()->get_id(), $file_name );
	}

	/**
	 * Download content pack resources.
	 *
	 * @param Kalium_Demo_Content_Import_Type $import_type
	 *
	 * @return true|WP_Error
	 */
	public function download_content_pack_resources( $import_type ) {

		// Initialize Filesystem
		if ( ! kalium()->filesystem->initialize() ) {
			return new WP_Error( 'kalium_demo_content_fs_init_error', 'Filesystem cannot be initialized!' );
		}

		// Content pack path
		$content_pack_path = $this->get_content_pack_download_path();

		// Create directory if it doesn't exists
		if ( ! kalium()->filesystem->exists( $content_pack_path ) ) {
			wp_mkdir_p( $content_pack_path );

			// Create index.html file to avoid possible indexing
			kalium()->filesystem->touch( $content_pack_path . '/index.html' );
		}

		// Download sources
		foreach ( $import_type->get_sources() as $source ) {

			// Source name and URL
			$file_name  = $source['name'];
			$source_url = $source['url'];

			// File path
			$file_path = $this->get_content_pack_import_source_path( $file_name );

			// If demo content file already exists
			if ( kalium()->filesystem->exists( $file_path ) ) {
				continue;
			}

			// Downloaded file TMP path
			$downloaded_file = download_url( $source_url );

			// Errors on download
			if ( is_wp_error( $downloaded_file ) ) {

				// If forbidden, the license key is not valid
				if ( 'Forbidden' === $downloaded_file->get_error_message() ) {
					return new WP_Error( 'kalium_demo_content_download_forbidden', 'Please register the theme in order to download demo content files!', [
						'popupButtons' => [
							'registerTheme',
							'closePopup',
						],
					] );
				}

				// On http error code above 300
				if ( 'http_404' === $downloaded_file->get_error_code() ) {
					$error_data = $downloaded_file->get_error_data();

					if ( isset( $error_data['code'] ) && is_numeric( $error_data['code'] ) ) {
						status_header( $error_data['code'] );
					}
				}

				return $downloaded_file;
			}

			// Move file to content pack folder
			kalium()->filesystem->move( $downloaded_file, $file_path );
		}

		return true;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	private function __construct() {
		global $tgmpa;

		// Reference to TGM_Plugin_Activation
		$this->tgmpa = &$tgmpa;
	}
} // Kalium_Demo_Import_Manager
