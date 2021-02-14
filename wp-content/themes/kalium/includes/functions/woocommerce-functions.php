<?php
/**
 * Kalium WordPress Theme
 *
 * WooCommerce functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 *  Check if shop is in catalog mode.
 *
 * @return bool
 */
function kalium_woocommerce_is_catalog_mode() {
	return wp_validate_boolean( kalium_get_theme_option( 'shop_catalog_mode' ) );
}

/**
 * Get layout type for loop products.
 *
 * @return string
 */
function kalium_woocommerce_get_catalog_layout() {

	// Shop columns
	$shop_catalog_layout = kalium_get_theme_option( 'shop_catalog_layout' );

	if ( in_array( $shop_catalog_layout, [ 'full-bg', 'distanced-centered', 'transparent-bg' ] ) ) {
		return $shop_catalog_layout;
	}

	return 'default';
}

/**
 * Get shop sidebar position, if hidden "false" will be returned.
 *
 * @return string|false
 */
function kalium_woocommerce_get_sidebar_position() {
	$sidebar = kalium_get_theme_option( 'shop_sidebar' );

	if ( in_array( $sidebar, [ 'left', 'right' ] ) ) {
		return $sidebar;
	}

	return false;
}

/**
 * Get shop sidebar position, if hidden "false" will be returned.
 *
 * @return string|false
 */
function kalium_woocommerce_single_product_get_sidebar_position() {
	$sidebar = kalium_get_theme_option( 'shop_single_sidebar_position' );

	if ( in_array( $sidebar, [ 'left', 'right' ] ) ) {
		return $sidebar;
	}

	return false;
}

/**
 * Products per row in mobile devices.
 *
 * @return int
 */
function kalium_woocommerce_products_per_row_on_mobile() {
	$columns_mobile = kalium_get_theme_option( 'shop_product_columns_mobile' );

	return kalium_get_number_from_word( $columns_mobile );
}

/**
 * Get category columns number.
 *
 * @return int
 */
function kalium_woocommerce_get_category_columns() {
	$category_columns = kalium_get_theme_option( 'shop_category_columns' );
	$columns          = kalium_get_number_from_word( $category_columns );

	return apply_filters( 'kalium_woocommerce_get_category_columns', $columns );
}

/**
 * Support multi currency in AJAX mode for paged products page.
 *
 * @param array $actions
 *
 * @return array
 */
function kalium_wcml_multi_currency_ajax_actions( $actions ) {
	$actions[] = 'kalium_endless_pagination_get_paged_items';

	return $actions;
}

/**
 * List of plugins that will exclude usage of Kalium product gallery usage.
 *
 * @return bool
 */
function kalium_woocommerce_custom_product_gallery_conditional_use() {
	$not_supported_plugins = [
		'woocommerce-additional-variation-images/woocommerce-additional-variation-images.php',
	];

	// Disable custom product gallery when certain plugins are activated (not supported)
	foreach ( $not_supported_plugins as $plugin_file ) {
		if ( kalium()->is->plugin_active( $plugin_file ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Use Kalium's default product gallery layout.
 *
 * @return bool
 */
function kalium_woocommerce_use_custom_product_gallery_layout() {
	return apply_filters( 'kalium_woocommerce_use_custom_product_image_gallery_layout', true );
}

/**
 * Check if zoom is enabled.
 *
 * @return mixed
 */
function kalium_woocommerce_is_product_gallery_zoom_enabled() {
	return get_theme_support( 'wc-product-gallery-zoom' );
}

/**
 * Check if gallery lightbox is enabled.
 *
 * @return mixed
 */
function kalium_woocommerce_is_product_gallery_lightbox_enabled() {
	return get_theme_support( 'wc-product-gallery-lightbox' );
}

/**
 * Get localized strings from Kalium theme used in WooCommerce template files.
 *
 * @param string $str_id
 * @param bool   $echo
 *
 * @return string|void
 */
function kalium_woocmmerce_get_i18n_str( $str_id, $echo = false ) {
	$found_string = 'kalium_woocmmerce_get_i18n_str::notFoundString';

	$strings = [
		'(leave blank to leave unchanged)' => __( '(leave blank to leave unchanged)', 'kalium' ),
		'Current password'                 => __( 'Current password', 'kalium' ),
	];

	if ( isset( $strings[ $str_id ] ) ) {
		$found_string = $strings[ $str_id ];
	}

	if ( ! $echo ) {
		return $found_string;
	}

	echo $found_string;
}

/**
 *  Get product image size.
 *
 * @param string $type
 *
 * @return string|array
 */
function kalium_woocommerce_get_product_image_size( $type = 'single' ) {

	// Larger product image
	$image_size = apply_filters( 'kalium_woocommerce_single_product_main_image_size', 'woocommerce_single' );

	// Thumbnail product image
	if ( 'gallery' == $type || 'gallery_thumbnail' == $type ) {
		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

		return apply_filters( 'kalium_woocommerce_single_product_gallery_image_size', [
			$gallery_thumbnail['width'],
			$gallery_thumbnail['height']
		] );
	}

	return $image_size;
}

/**
 * Products masonry layout type.
 *
 * @return string
 */
function kalium_woocommerce_products_masonry_layout() {
	switch ( kalium_get_theme_option( 'shop_loop_masonry_layout_mode' ) ) {
		case 'fitRows':
			return 'fitRows';
			break;
	}

	return 'masonry';
}
