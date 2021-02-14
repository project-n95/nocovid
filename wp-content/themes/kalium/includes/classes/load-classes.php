<?php
/**
 * Kalium WordPress Theme
 *
 * Core classes to load when Kalium initializes.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Class list to load
return apply_filters( 'kalium_load_classes', [

	// Core sub-instances
	'Kalium_Request'            => [
		'path'          => 'core/kalium-request',
		'instance_name' => 'request',
	],
	'Kalium_Helpers'            => [
		'path'          => 'core/kalium-helpers',
		'instance_name' => 'helpers',
	],
	'Kalium_Is'                 => [
		'path'          => 'core/kalium-is',
		'instance_name' => 'is',
	],
	'Kalium_Filesystem'         => [
		'path'          => 'admin/kalium-filesystem',
		'instance_name' => 'filesystem',
	],
	'Kalium_Enqueue'            => [
		'path'          => 'core/kalium-enqueue',
		'instance_name' => 'enqueue',
	],
	'Kalium_Theme_License'      => [
		'path'          => 'core/kalium-theme-license',
		'instance_name' => 'theme_license',
	],
	'Kalium_Theme_Plugins'      => [
		'path'          => 'core/kalium-theme-plugins',
		'instance_name' => 'theme_plugins',
	],
	'Kalium_Images'             => [
		'path'          => 'core/kalium-images',
		'instance_name' => 'images',
	],
	'Kalium_Media'              => [
		'path'          => 'core/kalium-media',
		'instance_name' => 'media',
	],
	'Kalium_Structured_Data'          => [
		'path'          => 'seo/kalium-structured-data',
		'instance_name' => 'structured_data',
	],
	'Kalium_WooCommerce'        => [
		'path'          => 'compatibility/kalium-woocommerce',
		'instance_name' => 'woocommerce',
	],
	'Kalium_ACF'                => [
		'path'          => 'compatibility/kalium-acf',
		'instance_name' => 'acf',
	],
	'Kalium_Elementor'          => [
		'path'          => 'compatibility/kalium-elementor',
		'instance_name' => 'elementor',
	],

	// Core classes
	'Kalium_Theme_Upgrader'     => [ 'path' => 'core/kalium-theme-upgrader' ],
	'Kalium_Version_Upgrades'   => [ 'path' => 'core/kalium-version-upgrades' ],
	'Kalium_Translations'       => [ 'path' => 'core/kalium-translations' ],
	'Kalium_Integrations'       => [ 'path' => 'core/kalium-integrations' ],

	// Plugin compatibility
	'Kalium_WPBakery'           => [ 'path' => 'compatibility/kalium-wpbakery' ],
	'Kalium_Slider_Revolution'  => [ 'path' => 'compatibility/kalium-slider-revolution' ],
	'Kalium_LayerSlider'        => [ 'path' => 'compatibility/kalium-layerslider' ],
	'Kalium_WPML'               => [ 'path' => 'compatibility/kalium-wpml' ],

	// Generic classes
	'Kalium_WP_Hook_Value'      => [ 'path' => 'core/kalium-hook-value', 'instantiate' => false ],

	// Admin classes
	'Kalium_About'              => [ 'path' => 'admin/kalium-about' ],

	// Utility classes
	'Laborator_System_Status'   => [ 'path' => 'utilities/laborator-system-status', 'instantiate' => false ],

	// GDPR Notices for WP-Admin
	'Kalium_Admin_GDPR_Notices' => [ 'path' => 'core/kalium-admin-gdpr-notices' ],
] );
