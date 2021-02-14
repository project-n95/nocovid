<?php
/**
 * Kalium WordPress Theme
 *
 * Checker methods of any type.
 *
 * @link https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Is {

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function plugin_installed( $plugin ) {

		// Require wp-admin/includes/plugin.php to use is_plugin_active function
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Plugins
		$plugins = get_plugins();

		return isset( $plugins[ $plugin ] );
	}

	/**
	 * Check if plugin is active.
	 *
	 * @param string|array $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $plugin ) {

		// Require wp-admin/includes/plugin.php to use is_plugin_active function
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( $plugin );
	}

	/**
	 * Check if any of the given plugins is active.
	 *
	 * @param array|string $plugins
	 *
	 * @return bool
	 */
	public function any_plugin_active( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			$plugins = [ $plugins ];
		}

		foreach ( $plugins as $plugin ) {
			if ( $this->plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if all of the given plugins are active.
	 *
	 * @param array|string $plugins
	 *
	 * @return bool
	 */
	public function plugins_are_active( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			$plugins = [ $plugins ];
		}

		foreach ( $plugins as $plugin ) {
			if ( ! $this->plugin_active( $plugin ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if Portfolio Post Type plugin is active.
	 *
	 * @return bool
	 */
	public function portfolio_plugin_active() {
		return $this->plugin_active( 'portfolio-post-type/portfolio-post-type.php' ) || class_exists( 'Portfolio_Post_Type' );
	}

	/**
	 * Check if WPBakery Page Builder plugin is active.
	 *
	 * @return bool
	 */
	public function wpb_page_builder_active() {
		return $this->plugin_active( 'js_composer/js_composer.php' ) || class_exists( 'Vc_Manager' );
	}

	/**
	 * Check if WooCommerce plugin is active.
	 *
	 * @return bool
	 */
	public function woocommerce_active() {
		return $this->plugin_active( 'woocommerce/woocommerce.php' ) || class_exists( 'WooCommerce' );
	}

	/**
	 * Check if ACF plugin is active.
	 *
	 * @return bool
	 */
	public function acf_active() {
		$acf_plugins = [
			'advanced-custom-fields/acf.php',
			'advanced-custom-fields-pro/acf.php',
		];

		return $this->any_plugin_active( $acf_plugins ) || function_exists( 'get_field' );
	}

	/**
	 * Check if Elementor plugin is active.
	 *
	 * @return bool
	 */
	public function elementor_active() {
		return $this->plugin_active( 'elementor/elementor.php' ) || did_action( 'elementor/loaded' );
	}

	/**
	 * Check if WPML plugin is activated.
	 *
	 * @return bool
	 */
	public function wpml_active() {
		return $this->plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) || function_exists( 'icl_object_id' );
	}

	/**
	 * Check if Breadcrumb NavXT plugin is active.
	 */
	public function breadcrumb_navxt_active() {
		return $this->plugin_active( 'breadcrumb-navxt/breadcrumb-navxt.php' ) || function_exists( 'bcn_display' );
	}

	/**
	 * Check if given file is SVG.
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	public function svg( $file ) {
		$file_info = pathinfo( $file );

		return 'svg' == strtolower( kalium_get_array_key( $file_info, 'extension' ) );
	}
}
