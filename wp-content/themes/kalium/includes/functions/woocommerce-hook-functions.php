<?php
/**
 * Kalium WordPress Theme
 *
 * WooCommerce hook functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * WooCommerce init.
 *
 * @return void
 */
function _kalium_woocommerce_init() {

	// Product classes
	add_filter( 'post_class', '_kalium_woocommerce_product_classes', 25, 3 );

	// Page title and results count hide
	if ( ! wp_validate_boolean( kalium_get_theme_option( 'shop_title_show' ) ) ) {
		add_filter( 'woocommerce_show_page_title', '__return_false' );
		add_filter( 'kalium_woocommerce_show_results_count', '__return_false' );
	}

	// Hide sorting
	if ( ! wp_validate_boolean( kalium_get_theme_option( 'shop_sorting_show' ) ) ) {
		add_filter( 'kalium_woocommerce_show_product_sorting', '__return_false' );
	}

	// Product info (loop)
	if ( 'default' === kalium_woocommerce_get_catalog_layout() ) {
		add_action( 'woocommerce_after_shop_loop_item', 'kalium_woocommerce_product_loop_item_info', 25 );
	}

	// Catalog mode
	if ( kalium_woocommerce_is_catalog_mode() ) {
		add_filter( 'get_data_shop_add_to_cart_listing', '__return_false' );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		add_action( 'woocommerce_single_product_summary', '_kalium_woocommerce_catalog_mode_add_to_cart_options', 30 );

		if ( kalium_get_theme_option( 'shop_catalog_mode_hide_prices' ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 29 );
			add_filter( 'get_data_shop_product_price_listing', '__return_false' );
		}
	}

	// Single product Kalium image gallery
	if ( kalium_woocommerce_use_custom_product_gallery_layout() ) {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		add_action( 'kalium_woocommerce_single_product_images', 'kalium_woocommerce_show_product_images_custom_layout', 20 );
		add_filter( 'woocommerce_available_variation', '_kalium_woocommerce_variation_image_handler', 10, 3 );
	}

	// Social network share links
	if ( kalium_get_theme_option( 'shop_single_share_product' ) ) {
		add_action( 'woocommerce_single_product_summary', 'kalium_woocommerce_share_product', 50 );
	}

	// Hide Related Products
	if ( ! wp_validate_boolean( kalium_get_theme_option( 'shop_related_products_per_page' ) ) ) {
		remove_filter( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	}

	// Category image size
	add_filter( 'subcategory_archive_thumbnail_size', '_kalium_woocommerce_subcategory_archive_thumbnail_size' );

	if ( ( $category_image_size = kalium_get_theme_option( 'shop_category_image_size' ) ) && preg_match( '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $category_image_size ) ) {
		add_filter( 'subcategory_archive_thumbnail_size', kalium_hook_return_value( $category_image_size ), 100 );
	}

	// Custom image size for single product images
	if ( kalium_get_theme_option( 'shop_single_product_custom_image_size' ) ) {
		add_filter( 'woocommerce_get_image_size_single', '_kalium_woocommerce_get_custom_image_size_single' );
	}
}

/**
 * Product classes.
 *
 * @param array  $classes
 * @param string $class
 * @param string $post_id
 *
 * @return array
 */
function _kalium_woocommerce_product_classes( $classes, $class = '', $post_id = '' ) {
	global $product;

	if ( $product instanceof WC_Product ) {
		$is_single_product = is_product() && $post_id === get_queried_object_id();

		// Product class
		$classes[] = 'product';

		// Product layout type
		$classes[] = 'catalog-layout-' . kalium_woocommerce_get_catalog_layout();

		// Products per row small width devices
		if ( ! $is_single_product ) {
			$classes[] = 'columns-xs-' . kalium_woocommerce_products_per_row_on_mobile();
		}

		// Single product classes
		if ( $is_single_product ) {
			$classes[] = 'product-images-columns-' . _kalium_woocommerce_get_product_gallery_container_width();
			$classes[] = 'product-images-align-' . kalium_woocommerce_get_product_gallery_container_alignment();
		}
	}

	return $classes;
}

/**
 * Pagination handler.
 *
 * @param int   $posts_per_page
 * @param int   $total_items
 * @param array $fetched_ids
 * @param array $query_args
 *
 * @return array
 */
function _kalium_woocommerce_infinite_scroll_pagination_handler( $posts_per_page, $total_items, $fetched_ids, $query_args ) {
	$response = [];

	// Execute actions before products query
	do_action( 'kalium_woocommerce_infinite_scroll_pagination_before_query' );

	// Workaround to apply woocommerce filters
	$_GET = array_merge( $_POST, $_GET );

	// Products
	$products   = new Kalium_WooCommerce_Products();
	$query_args = $products->query_products( $query_args );

	// Product IDs
	$product_ids = new WP_Query();
	$product_ids = $product_ids->query( $query_args );

	// Products content
	$products_content = $products->get_content();

	$response['fetchedItems'] = $product_ids;
	$response['items']        = $products_content;
	$response['hasMore']      = count( $fetched_ids ) + count( $product_ids ) < $total_items;
	$response['hasItems']     = true;

	return $response;
}

/**
 * Category image size,
 *
 * @return string
 */
function _kalium_woocommerce_subcategory_archive_thumbnail_size() {
	return 'shop-category-thumb';
}

/**
 * Add "shop-categories" class for products container ([product_categories]).
 *
 * @param string $output
 * @param string $tag
 *
 * @return string|string[]|null
 */
function _kalium_woocommerce_product_categories_shortcode_wrap( $output, $tag ) {
	if ( 'product_categories' === $tag ) {
		$output = preg_replace( '/(<ul.*?class=".*?)(".*?>)/', '${1} shop-categories${2}', $output );
	}

	return $output;
}

/**
 * Loop add to cart link args.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_loop_add_to_cart_args( $args ) {
	global $product;

	$args['attributes']['data-added_to_cart_text'] = esc_html__( 'Added to cart', 'kalium' );

	// Product type
	$args['class'] .= ' product-type-' . $product->get_type();

	if ( false === strpos( $args['class'], 'add_to_cart_button' ) ) {
		$args['class'] .= ' add_to_cart_button';
	}

	return $args;
}

/**
 * Pagination Next & Prev Labels.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_pagination_args( $args ) {
	$args['prev_text'] = '<i class="flaticon-arrow427"></i> ';
	$args['prev_text'] .= esc_html__( 'Previous', 'kalium' );
	$args['next_text'] = esc_html__( 'Next', 'kalium' );
	$args['next_text'] .= ' <i class="flaticon-arrow413"></i>';

	return $args;
}

/**
 * Add Kalium style images for variations.
 *
 * @param array               $variation_arr
 * @param WC_Product_Variable $variable_product
 * @param WC_Product          $variation
 *
 * @return array
 */
function _kalium_woocommerce_variation_image_handler( $variation_arr, $variable_product, $variation ) {
	global $post;

	$attachment_id                 = $variation->get_image_id();
	$variation_arr['kalium_image'] = [];

	// Product main and thumbnail image
	if ( $attachment_id ) {
		$is_featured_image = get_post_thumbnail_id( $post->ID ) == $variation->get_image_id();

		if ( ! $is_featured_image ) {
			$variation_arr['kalium_image']['main']  = kalium_woocommerce_get_single_product_image( $attachment_id, kalium_woocommerce_get_product_image_size( 'single' ), kalium_woocommerce_is_product_gallery_lightbox_enabled() );
			$variation_arr['kalium_image']['thumb'] = kalium_woocommerce_get_single_product_image( $attachment_id, kalium_woocommerce_get_product_image_size( 'gallery' ) );
		}
	}

	return $variation_arr;
}

/**
 * Review product form.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_product_review_comment_form_args( $args ) {
	$args['class_submit'] = 'button';

	// Comment textarea
	$args['comment_field'] = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-textarea-row \2"', $args['comment_field'] );

	// Comment fields
	if ( ! empty( $args['fields'] ) ) {
		foreach ( $args['fields'] as & $field ) {
			$field = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-input-row \2"', $field );
		}

		// Clear last field
		$field_keys = array_keys( $args['fields'] );

		$args['fields'][ end( $field_keys ) ] .= '<div class="clear"></div>';
	}

	return $args;
}

/**
 * Cart Fragments for mini cart.
 *
 * @param array $fragments_arr
 *
 * @return array
 */
function _kalium_woocommerce_woocommerce_add_to_cart_fragments( $fragments_arr ) {

	// Mini cart
	ob_start();
	get_template_part( 'tpls/wc-mini-cart' );
	$cart_contents = ob_get_clean();

	$fragments_arr['labMiniCart']      = $cart_contents;
	$fragments_arr['labMiniCartCount'] = WC()->cart->get_cart_contents_count();

	// Kalium args
	$kalium_args = [];

	// Totals
	$totals = WC()->cart->get_totals();

	$kalium_args['totals'] = array_map( 'wc_price', [
		'cartTotal'      => $totals['total'],
		'cartSubtotal'   => $totals['subtotal'],
		'cartTotalExTax' => $totals['total'] - $totals['total_tax'],
	] );

	// Total items in cart
	$kalium_args['totals']['items'] = WC()->cart->get_cart_contents_count();

	// Theme related data about cart
	$fragments_arr['kalium'] = $kalium_args;

	return $fragments_arr;
}

/**
 * Single Product Image – fadeIn effect for carousel type.
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_woocommerce_single_product_link_image_classes_carousel( $classes ) {
	$classes[] = 'wow';
	$classes[] = 'fadeIn';
	$classes[] = 'fast';

	return $classes;
}

/**
 * Single Product Image – fadeInLab effect for carousel type
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_woocommerce_single_product_link_image_classes_plain( $classes ) {
	$classes[] = 'wow';
	$classes[] = 'fadeInLab';

	return $classes;
}

/**
 * WooCommerce form fields.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_woocommerce_form_field_args( $args ) {

	// Replace Input Labels with Placeholder (text, password, etc)
	if ( in_array( $args['type'], [ 'text', 'password', 'state', 'country', 'email', 'tel' ] ) ) {
		$args['placeholder'] = $args['label'];

		if ( is_array( $args['label_class'] ) ) {
			$args['label_class'][] = 'hidden';
		}
	}

	return $args;
}

/**
 * Related products and up sells columns count.
 *
 * @return int
 */
function _kalium_woocommerce_related_products_columns() {
	return kalium_get_theme_option( 'shop_related_products_columns' );
}

/**
 * Related products to show.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_related_products_args( $args ) {
	$args['posts_per_page'] = kalium_get_theme_option( 'shop_related_products_per_page' );

	return $args;
}

/**
 * Return to shop after cart item adding (option enabled in WooCommerce).
 *
 * @return string
 */
function _kalium_woocommerce_continue_shopping_redirect_to_shop() {
	return wc_get_page_permalink( 'shop' );
}

/**
 * Replace cart remove link icon.
 *
 * @param string $remove_link
 *
 * @return string
 */
function _kalium_woocommerce_woocommerce_cart_item_remove_link( $remove_link ) {
	return str_replace( '&times;', '<i class="flaticon-cross37"></i>', $remove_link );
}

/**
 * Single product image column width.
 *
 * @return string
 */
function _kalium_woocommerce_get_product_gallery_container_width() {
	$images_column_size = kalium_get_theme_option( 'shop_single_image_column_size' );
	$size               = 'default';

	switch ( $images_column_size ) {
		case 'xlarge':
		case 'large':
		case 'medium':
			$size = $images_column_size;
			break;
	}

	return $size;
}

/**
 * Custom image size for single product images (main images).
 *
 * @param array $size
 *
 * @return array
 */
function _kalium_woocommerce_get_custom_image_size_single( $size ) {
	$width  = kalium_get_theme_option( 'shop_single_product_custom_image_size_width' );
	$height = kalium_get_theme_option( 'shop_single_product_custom_image_size_height' );

	if ( empty( $width ) || ! is_numeric( $width ) || $width <= 0 ) {
		$width = wc_get_theme_support( 'single_image_width' );
	}

	// Custom width
	$size['width'] = $width;

	// Custom height
	if ( $height && is_numeric( $height ) && $height > 0 ) {
		$size['height'] = $height;
	}

	// Crop if width and height are specified
	$size['crop'] = ! empty( $size['width'] ) && ! empty( $size['height'] );

	return $size;
}

/**
 * Category thumbnail.
 *
 * @param WP_Term $category
 */
function _kalium_woocommerce_subcategory_thumbnail( $category ) {
	$small_thumbnail_size = apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' );
	$thumbnail_id         = get_term_meta( $category->term_id, 'thumbnail_id', true );

	if ( $thumbnail_id ) {
		$image = kalium_get_attachment_image( $thumbnail_id, $small_thumbnail_size );
	} else {
		$image = kalium_get_attachment_image( wc_placeholder_img_src() );
	}

	echo $image;
}

/**
 * Catalog mode, show add to cart options.
 *
 * @return void
 */
function _kalium_woocommerce_catalog_mode_add_to_cart_options() {
	global $product;

	// Variable product
	if ( 'variable' === $product->get_type() ) {

		// Remove add to cart button
		remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
		remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

		// Variation product add-to-cart
		woocommerce_variable_add_to_cart();
	}
}

/**
 * Assign "form-control" class to variation select field.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_woocommerce_dropdown_variation_attribute_options_args( $args ) {
	if ( empty( $args['class'] ) ) {
		$args['class'] = '';
	}

	$args['class'] .= ' form-control';

	return $args;
}

/**
 * Product SKU in single page.
 *
 * @param bool $value
 *
 * @return bool
 */
function _kalium_woocommerce_product_sku_enabled_filter( $value ) {
	if ( is_product() ) {
		$sku_visibility = kalium_get_theme_option( 'shop_single_product_sku_visibility' );

		return intval( $sku_visibility ) || "" === $sku_visibility;
	}

	return $value;
}

/**
 * Unassign product category and tags filters.
 *
 * @return void
 */
function _kalium_woocommerce_product_meta_categories_and_tags_remove_filters_action() {
	remove_filter( 'get_the_terms', '_kalium_woocommerce_hide_categories_filter', 10 );
	remove_filter( 'get_the_terms', '_kalium_woocommerce_hide_tags_filter', 10 );
}

/**
 * Product Category and Tags hide.
 *
 * @return void
 */
function _kalium_woocommerce_product_meta_categories_and_tags_action() {
	$categories_visibility = kalium_get_theme_option( 'shop_single_product_categories_visibility' );
	$tags_visibility       = kalium_get_theme_option( 'shop_single_product_tags_visibility' );

	if ( ! wp_validate_boolean( $categories_visibility ) ) {
		add_filter( 'get_the_terms', '_kalium_woocommerce_hide_categories_filter', 10, 3 );
	}

	if ( ! wp_validate_boolean( $tags_visibility ) ) {
		add_filter( 'get_the_terms', '_kalium_woocommerce_hide_tags_filter', 10, 3 );
	}
}

/**
 * Return empty categories to hide from product page.
 *
 * @param WP_Term[] $terms
 * @param int       $post_id
 * @param string    $taxonomy
 *
 * @return array
 */
function _kalium_woocommerce_hide_categories_filter( $terms, $post_id, $taxonomy ) {
	if ( 'product_cat' === $taxonomy ) {
		return [];
	}

	return $terms;
}

/**
 * Return empty tags to hide from product page.
 *
 * @param WP_Term[] $terms
 * @param int       $post_id
 * @param string    $taxonomy
 *
 * @return array
 */
function _kalium_woocommerce_hide_tags_filter( $terms, $post_id, $taxonomy ) {
	if ( 'product_tag' === $taxonomy ) {
		return [];
	}

	return $terms;
}

/**
 * YITH badge management workaround for Kalium.
 *
 * @return void
 * @since 3.1
 */
function _kalium_yith_wcbm_show_badge_on_product() {
	if ( function_exists( 'YITH_WCBM_Frontend' ) ) {
		echo YITH_WCBM_Frontend()->show_badge_on_product( '<style>.yith-wcbm-badge{z-index:100}</style>' );
	}
}

/**
 * Return to shop button text when cart is empty.
 *
 * @return string
 *
 * @since 3.1
 */
function _kalium_woocommerce_return_to_shop_text( $text ) {
	return __( 'Browse products', 'kalium' );
}
