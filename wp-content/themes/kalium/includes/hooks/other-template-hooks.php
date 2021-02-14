<?php
/**
 * Kalium WordPress Theme
 *
 * Other hooks functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Remove multiple current menu items with hashtags
 */
add_filter( 'nav_menu_css_class', '_kalium_unique_hashtag_url_base_menu_item', 10, 2 );
add_filter( 'wp_nav_menu_args', '_kalium_unique_hashtag_url_base_reset', 10 );

/**
 * Fix for Post Link Plus when WPML is active
 */
add_filter( 'kalium_post_link_plus_result', '_kalium_post_link_plus_result_mapper', 10 );

/**
 * Set WooCommerce Product Filter to use as theme.
 */
add_filter( 'svx_plugins_settings', '_kalium_prdctfltr_set_as_theme', 100 );

/**
 * Warn users to install ACF5 Pro
 */
add_action( 'admin_init', '_kalium_acf5_warning_init', 10 );

/**
 * Product Filter plugin AJAX fix with WPBakery
 */
add_action( 'before_prdctfltr_init', '_kalium_prdctfltr_map_wpb_shortcodes_fix' );

/**
 * Go to Top feature.
 */
add_action( 'wp_footer', '_kalium_go_to_top_link' );

/**
 * Maintenance mode page.
 */
add_action( 'template_redirect', '_kalium_page_maintenance_mode' );

/**
 * Coming soon or maintenance mode.
 */
add_action( 'template_redirect', '_kalium_coming_soon_mode' );

/**
 * Google Meta Theme Color (Phone).
 */
add_action( 'wp_head', '_kalium_google_theme_color' );

/**
 * Holiday season text display.
 */
add_action( 'admin_head', '_kalium_holiday_season_display' );

/**
 * Favicon from theme options.
 */
add_action( 'wp_head', '_kalium_theme_options_favicon' );

/**
 * Text line below user name on single post page.
 */
add_action( 'personal_options', '_kalium_user_custom_text' );
add_action( 'personal_options_update', '_kalium_user_custom_text_save' );
add_action( 'edit_user_profile_update', '_kalium_user_custom_text_save' );
add_filter( 'kalium_blog_single_post_author_info_subtitle', '_kalium_user_custom_text_display' );
