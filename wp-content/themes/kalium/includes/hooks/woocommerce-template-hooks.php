<?php
/**
 * Kalium WordPress Theme
 *
 * WooCommerce hooks functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * WooCommerce init.
 */
add_action( 'woocommerce_init', '_kalium_woocommerce_init' );

/**
 * Disable WooCommerce styles.
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add "shop-categories" class for products container ([product_categories])
 */
add_filter( 'do_shortcode_tag', '_kalium_woocommerce_product_categories_shortcode_wrap', 100, 2 );

/**
 * Replace category thumbnail in shop loop.
 */
remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );
add_action( 'woocommerce_before_subcategory_title', '_kalium_woocommerce_subcategory_thumbnail', 10 );

/**
 * Loop pagination args.
 */
add_filter( 'woocommerce_pagination_args', '_kalium_woocommerce_pagination_args', 10 );

/**
 * Review product form.
 */
add_filter( 'woocommerce_product_review_comment_form_args', '_kalium_woocommerce_product_review_comment_form_args', 10 );

/**
 * Add to cart link args (loop).
 */
add_filter( 'woocommerce_loop_add_to_cart_args', '_kalium_woocommerce_loop_add_to_cart_args' );

/**
 * Cart fragments.
 */
add_filter( 'woocommerce_add_to_cart_fragments', '_kalium_woocommerce_woocommerce_add_to_cart_fragments', 10 );

/**
 * WooCommerce fields.
 */
add_filter( 'woocommerce_form_field_args', '_kalium_woocommerce_woocommerce_form_field_args', 10, 3 );

/**
 * Related products and up sells columns count.
 */
add_filter( 'woocommerce_related_products_columns', '_kalium_woocommerce_related_products_columns', 10 );
add_filter( 'woocommerce_upsells_columns', '_kalium_woocommerce_related_products_columns', 10 );

/**
 * Related products to show.
 */
add_filter( 'woocommerce_output_related_products_args', '_kalium_woocommerce_related_products_args', 10 );

/**
 * Return to shop after cart item adding (option enabled in WooCommerce).
 */
add_filter( 'woocommerce_continue_shopping_redirect', '_kalium_woocommerce_continue_shopping_redirect_to_shop', 10 );

/**
 * Replace cart remove link icon.
 */
add_filter( 'woocommerce_cart_item_remove_link', '_kalium_woocommerce_woocommerce_cart_item_remove_link' );

/**
 * Assign "form-control" class to variation select field.
 */
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', '_kalium_woocommerce_dropdown_variation_attribute_options_args', 10 );

/**
 * Product attributes toggles.
 */
add_filter( 'wc_product_sku_enabled', '_kalium_woocommerce_product_sku_enabled_filter' );
add_action( 'woocommerce_product_meta_start', '_kalium_woocommerce_product_meta_categories_and_tags_action' );
add_action( 'woocommerce_product_meta_end', '_kalium_woocommerce_product_meta_categories_and_tags_remove_filters_action' );

/**
 * YITH badge management workaround for Kalium.
 */
add_action( 'woocommerce_before_shop_loop_item_title', '_kalium_yith_wcbm_show_badge_on_product' );
add_action( 'kalium_woocommerce_single_product_images', '_kalium_yith_wcbm_show_badge_on_product' );

/**
 * Archive wrapper.
 */
add_action( 'woocommerce_before_main_content', 'kalium_woocommerce_archive_wrapper_start', 20 );
add_action( 'woocommerce_after_main_content', 'kalium_woocommerce_archive_wrapper_end', 5 );

/**
 * Products wrapper.
 */
add_action( 'woocommerce_product_loop_start', 'kalium_woocommerce_product_loop_start', 10 );
add_action( 'woocommerce_product_loop_end', 'kalium_woocommerce_product_loop_end', 10 );

/**
 * Archive products header.
 */
add_action( 'woocommerce_before_main_content', 'kalium_woocommerce_archive_header', 15 );

/**
 * Results count and archive description.
 */
add_action( 'kalium_woocommerce_archive_description', 'woocommerce_result_count', 20 );

/**
 * Remove certain actions from shop archive page.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * Remove default result counter and products order dropdown.
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Remove default product details added by WooCommerce.
 */
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

/**
 * Remove Link from Products.
 */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

/**
 * Single product wrapper.
 */
add_action( 'woocommerce_before_single_product', 'kalium_woocommerce_single_product_wrapper_start', 1 );
add_action( 'woocommerce_after_single_product', 'kalium_woocommerce_single_product_wrapper_end', 1000 );

/**
 * Change the order of product details on single page.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 29 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 21 );

/**
 * Product loop start.
 */
remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
add_filter( 'woocommerce_before_shop_loop', 'kalium_woocommerce_maybe_show_product_categories' );

/**
 * Loop product images.
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'kalium_woocommerce_catalog_loop_thumbnail', 10 );

/**
 * Infinite pagination setup.
 */
add_action( 'woocommerce_after_shop_loop', 'kalium_woocommerce_infinite_scroll_pagination', 9 );

/**
 * My Account Wrapper.
 */
add_action( 'woocommerce_before_my_account', 'kalium_woocommerce_my_account_wrapper_start' );
add_action( 'woocommerce_after_my_account', 'kalium_woocommerce_my_account_wrapper_end' );

/**
 * Support multi currency in AJAX mode for paged products page.
 */
add_filter( 'wcml_multi_currency_ajax_actions', 'kalium_wcml_multi_currency_ajax_actions' );

/**
 * Review rating.
 */
add_action( 'woocommerce_product_get_rating_html', 'kalium_woocommerce_product_get_rating_html', 10, 3 );

/**
 * Product rating.
 */
add_action( 'kalium_woocommerce_single_product_rating_stars', 'kalium_woocommerce_single_product_rating_stars', 10 );

/**
 * Payment method title.
 */
add_action( 'woocommerce_review_order_before_payment', 'kalium_woocommerce_review_order_before_payment_title', 10 );

/**
 * Login page heading.
 */
add_action( 'woocommerce_before_customer_login_form', 'kalium_woocommerce_my_account_login_page_heading', 10 );

/**
 * Account navigation.
 */
add_action( 'woocommerce_before_account_navigation', 'kalium_woocommerce_account_navigation_before' );
add_action( 'woocommerce_after_account_navigation', 'kalium_woocommerce_account_navigation_after' );

/**
 * Orders and downloads page titles.
 */
add_action( 'woocommerce_before_account_orders', 'kalium_woocommerce_before_account_orders', 10 );
add_action( 'woocommerce_before_account_downloads', 'kalium_woocommerce_before_account_downloads', 10 );

/**
 * BACS details.
 */
add_action( 'woocommerce_thankyou_bacs', 'kalium_woocommerce_bacs_details_before', 1 );
add_action( 'woocommerce_thankyou_bacs', 'kalium_woocommerce_bacs_details_after', 100 );

/**
 * Show rating below top rated products widget,
 */
add_action( 'woocommerce_widget_product_item_end', 'kalium_woocommerce_top_rated_products_widget_rating', 10 );

/**
 * Ordering dropdown for products loop.
 */
add_filter( 'kalium_woocommerce_shop_loop_ordering', 'kalium_woocommerce_shop_loop_ordering_dropdown', 10, 2 );

/**
 * Single product images wrapper  .
 */
add_filter( 'woocommerce_before_single_product_summary', 'kalium_woocommerce_single_product_images_wrapper_start', 2 );
add_filter( 'woocommerce_before_single_product_summary', 'kalium_woocommerce_single_product_images_wrapper_end', 1000 );

/**
 * Product flash badges.
 */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

add_action( 'woocommerce_before_shop_loop_item_title', 'kalium_woocommerce_product_badges', 10 );
add_action( 'woocommerce_before_single_product_summary', 'kalium_woocommerce_product_badges', 10 );

/**
 * Edit account heading
 */
add_action( 'woocommerce_before_edit_account_form', 'kalium_woocommerce_myaccount_edit_account_heading', 10 );

/**
 * Edit address sub title
 */
add_filter( 'woocommerce_my_account_edit_address_title', 'kalium_woocommerce_my_account_edit_address_title', 10 );

/**
 * Go back link for Edit Address page
 */
add_action( 'woocommerce_after_edit_account_address_form', 'kalium_woocommerce_myaccount_edit_address_back_link', 10 );

/**
 * Address wrapper on "My Account" page
 */
add_filter( 'woocommerce_my_account_my_address_description', 'kalium_woocommerce_my_account_my_address_description', 10 );

/**
 * Forgot password go back link.
 */
add_action( 'woocommerce_after_lost_password_form', 'kalium_woocommerce_myaccount_forgot_password_back_link' );

/**
 * Use Kalium's default product gallery layout
 */
add_filter( 'kalium_woocommerce_use_custom_product_image_gallery_layout', 'kalium_woocommerce_custom_product_gallery_conditional_use' );
