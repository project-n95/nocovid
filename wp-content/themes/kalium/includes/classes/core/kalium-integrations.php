<?php
/**
 * Kalium WordPress Theme
 *
 * Other Kalium integrations.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Integrations {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Theme demo
		$this->theme_demo_integration();

		// Laborator libraries
		$this->laborator_libraries();

		// TGM plugin activation
		$this->tgmpa_integration();

		// WPML embed
		$this->wpml_embed_integration();

		// Sidekick integration
		$this->sidekick_integration();

		// Other integrations
		$this->other_integrations();
	}

	/**
	 * Theme demo options.
	 *
	 * @return void
	 */
	private function theme_demo_integration() {
		$theme_demo_file = kalium()->locate_file( 'theme-demo/theme-demo.php' );

		if ( true === file_exists( $theme_demo_file ) ) {
			require_once $theme_demo_file;
		}
	}

	/**
	 * Laborator libraries.
	 *
	 * @return void
	 */
	private function laborator_libraries() {
		kalium()->require_file( 'includes/libraries/laborator/typolab/typolab.php' );
		kalium()->require_file( 'includes/libraries/laborator/importer/importer.php' );
		kalium()->require_file( 'includes/libraries/laborator/custom-css/custom-css.php' );
	}

	/**
	 * TGMPA integration.
	 *
	 * @return void
	 */
	private function tgmpa_integration() {
		kalium()->require_file( 'includes/libraries/class-tgm-plugin-activation.php' );
	}

	/**
	 * WPML embed integration.
	 *
	 * @return void
	 */
	private function wpml_embed_integration() {
		if ( false === apply_filters( 'kalium_wpml_embed_integration', true ) ) {
			return;
		}

		// Include WPML Embedder
		require_once get_template_directory() . '/includes/libraries/wpml-embedder/vendor/otgs/installer/loader.php';

		/** @var string $wp_installer_instance */
		WP_Installer_Setup( $wp_installer_instance,
			[
				'plugins_install_tab'  => 1, // optional, default value: 0
				'affiliate_id:wpml'    => '150643', // optional, default value: empty
				'affiliate_key:wpml'   => 'VWCj6GPGWxBE', // optional, default value: empty
				'src_name'             => 'Kalium', // optional, default value: empty, needed for coupons
				'src_author'           => 'Laborator',// optional, default value: empty, needed for coupons
				'repositories_include' => [ 'wpml' ], // optional, default to empty (show all)
			]
		);
	}

	/**
	 * Sidekick config vars.
	 *
	 * @return void
	 */
	private function sidekick_integration() {

		// Sidekick configuration
		define( 'SK_PRODUCT_ID', 454 );
		define( 'SK_ENVATO_PARTNER', 'iZmD68ShqUyvu7HzjPWPTzxGSJeNLVxGnRXM/0Pqxv4=' );
		define( 'SK_ENVATO_SECRET', 'RqjBt/YyaTOjDq+lKLWhL10sFCMCJciT9SPUKLBBmso=' );
	}

	/**
	 * Other integrations.
	 *
	 * @return void
	 */
	private function other_integrations() {
		kalium()->require_file( 'includes/libraries/dynamic-image-downsize.php' );
		kalium()->require_file( 'includes/libraries/post-link-plus.php' );
	}
}