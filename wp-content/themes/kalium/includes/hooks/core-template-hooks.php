<?php
/**
 * Kalium WordPress Theme
 *
 * Core hooks functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * After wrapper hooks.
 */
add_action( 'kalium_after_wrapper', 'kalium_display_footer', 10 );

/**
 * Enqueue styles of the theme.
 */
add_action( 'wp_enqueue_scripts', '_kalium_enqueue_styles', 100 );

/**
 * Enqueue scripts of the theme.
 */
add_action( 'wp_enqueue_scripts', '_kalium_enqueue_scripts', 100 );

/**
 * Custom JavaScript in head and footer.
 */
add_action( 'wp_head', '_kalium_wp_head_custom_js', 100 );

/**
 * Custom grid width.
 */
add_action( 'wp_head', '_kalium_grid_container_max_width', 100 );

/**
 * Enqueue main JS file.
 */
add_action( 'wp_footer', '_kalium_enqueue_main_js_file' );

/**
 * Custom User JavaScript print in the end.
 */
add_action( 'wp_print_footer_scripts', '_kalium_wp_footer_custom_js' );

/**
 * Theme widgets init.
 */
add_action( 'widgets_init', '_kalium_widgets_init', 10 );

/**
 * Google API Key for ACF.
 */
add_filter( 'acf/fields/google_map/api', '_kalium_google_api_key_acf', 10 );

/**
 * Map WPBakery Page Builder shortcodes for blog entries on AJAX pagination
 */
add_action( 'kalium_endless_pagination_pre_get_paged_items', '_kalium_endless_pagination_ajax_map_wpb_shortcodes', 10 );

/**
 * Sidebar skin
 */
add_filter( 'kalium_widget_area_classes', '_kalium_set_widgets_classes', 10 );

/**
 * Custom sidebars plugin args
 */
add_filter( 'cs_sidebar_params', '_kalium_custom_sidebars_params', 10 );

/**
 * Password protected post form
 */
add_filter( 'the_password_form', '_kalium_the_password_form', 10 );

/**
 * Default excerpt length and more dots
 */
add_filter( 'excerpt_length', '_kalium_get_default_excerpt_length', 10 );
add_filter( 'excerpt_more', '_kalium_get_default_excerpt_more', 100 );

/**
 * Footer classes
 */
add_filter( 'kalium_footer_class', '_kalium_footer_classes', 10 );

/**
 * Image placeholder set style
 */
add_action( 'template_redirect', '_kalium_image_placeholder_set_style', 10 );

/**
 * Parse footer styles.
 */
add_action( 'wp_footer', '_kalium_append_custom_css' );

/**
 * Print scripts in the header.
 */
add_action( 'wp_print_scripts', '_kalium_wp_print_scripts' );

/**
 * Laborator admin menu item class.
 */
add_filter( 'add_menu_classes', '_laborator_options_admin_menu_icon' );

/**
 * Kalium admin bar item.
 */
add_action( 'admin_bar_menu', '_kalium_admin_bar_entry', 10000 );

/**
 * Append content to the footer.
 */
add_action( 'wp_footer', '_kalium_append_footer_html' );

/**
 * Page Custom CSS.
 */
add_action( 'wp', '_kalium_page_custom_css' );

/**
 * Add open graph meta in header.
 */
add_action( 'wp_head', '_kalium_wp_head_open_graph_meta', 5 );

/**
 * Custom breadcrumb placement on certain page types.
 */
//TMPadd_action( 'wp', '_kalium_custom_breadcrumb_placement' );

/**
 * Set WPBakery Page Builder as theme.
 */
add_action( 'vc_before_init', 'vc_set_as_theme' );

/**
 * Handle endless pagination (global)
 */
add_action( 'wp_ajax_kalium_endless_pagination_get_paged_items', '_kalium_endless_pagination_get_paged_items', 10 );
add_action( 'wp_ajax_nopriv_kalium_endless_pagination_get_paged_items', '_kalium_endless_pagination_get_paged_items', 10 );

/**
 * Version upgrade hooks
 */
add_action( 'version_upgrade_2_3', [ 'Kalium_Version_Upgrades', 'version_upgrade_2_3' ], 10 );

/**
 * JavaScript assets enqueue mapping
 */
add_action( 'wp_footer', 'kalium_js_assets_enqueue_mapping', 10 );

/**
 * Map WPBakery Shortcodes for Product Filter AJAX request.
 */
add_action( 'wp_ajax_prdctfltr_respond_550', 'WPBMap::addAllMappedShortcodes', 5 );
add_action( 'wp_ajax_nopriv_prdctfltr_respond_550', 'WPBMap::addAllMappedShortcodes', 5 );

/**
 * Theme execution time.
 */
add_action( 'wp_footer', 'kalium_print_theme_execution_time', 10000 );

/**
 * Exclude post types from search.
 */
add_filter( 'pre_get_posts', '_kalium_exclude_post_types_from_search' );
