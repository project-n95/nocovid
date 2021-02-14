<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium WooCommerce compatibility class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_WooCommerce {

	/**
	 * Class instructor, define necessary actions.
	 */
	public function __construct() {
		if ( ! kalium()->is->woocommerce_active() ) {
			return;
		}

		// Include files
		$this->include_files();

		// Hooks
		add_action( 'after_setup_theme', [ $this, '_after_setup_theme' ] );
	}

	/**
	 * After setup theme.
	 *
	 * @return void
	 */
	public function _after_setup_theme() {

		// Add theme support for WooCommerce
		add_theme_support( 'woocommerce', apply_filters( 'kalium_theme_support_woocommerce', [
			'single_image_width'            => 820,
			'thumbnail_image_width'         => 550,
			'gallery_thumbnail_image_width' => 220,

			'product_grid' => [
				'default_rows' => 4,
				'min_rows'     => 1,
				'max_rows'     => 10,

				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 6,
			],
		] ) );

		// Gallery slider
		add_theme_support( 'wc-product-gallery-slider' );

		// Gallery zoom
		if ( kalium_get_theme_option( 'shop_single_product_image_zoom', 1 ) ) {
			add_theme_support( 'wc-product-gallery-zoom' );
		}

		// Gallery lightbox
		if ( kalium_get_theme_option( 'shop_single_product_image_lightbox', 1 ) ) {
			add_theme_support( 'wc-product-gallery-lightbox' );
		}

		// Category thumbnails
		$this->define_category_thumbnails();

		// Use image resizer in AJAX requests for infinite pagination
		add_action( 'kalium_woocommerce_infinite_scroll_pagination_before_query', [
			kalium()->woocommerce,
			'maybe_resize_images',
		], 10 );
	}

	/**
	 * Image resize for WooCommerce.
	 *
	 * @return void
	 */
	public function maybe_resize_images() {
		if ( class_exists( 'WC_Regenerate_Images' ) ) {
			add_filter( 'wp_get_attachment_image_src', [ 'WC_Regenerate_Images', 'maybe_resize_image' ], 10, 4 );
		}
	}

	/**
	 * Category image thumbnail.
	 *
	 * @return void
	 */
	public function define_category_thumbnails() {

		// Category image size dimensions
		$shop_category_image_size   = kalium_get_theme_option( 'shop_category_image_size' );
		$shop_category_thumb_width  = 500;
		$shop_category_thumb_height = 290;
		$shop_category_thumb_crop   = true;

		// Custom defined size
		if ( preg_match_all( '/^([0-9]+)x?([0-9]+)?x?(0|1)?$/', $shop_category_image_size, $shop_category_image_dims ) ) {
			$shop_category_thumb_width  = intval( $shop_category_image_dims[1][0] );
			$shop_category_thumb_height = intval( $shop_category_image_dims[2][0] );
			$shop_category_thumb_crop   = intval( $shop_category_image_dims[3][0] ) == 1;

			if ( $shop_category_thumb_width == 0 || $shop_category_thumb_height == 0 ) {
				$shop_category_thumb_crop = false;
			}
		}

		add_image_size( 'shop-category-thumb', $shop_category_thumb_width, $shop_category_thumb_height, $shop_category_thumb_crop );
	}

	/**
	 * Include related files.
	 *
	 * @return void
	 */
	private function include_files() {
		if ( class_exists( 'WC_Shortcode_Products' ) ) {
			kalium()->require_file( 'includes/classes/woocommerce/kalium-woocommerce-products.php' );
		}
	}
}
