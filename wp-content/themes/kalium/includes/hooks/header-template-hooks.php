<?php
/**
 * Kalium WordPress Theme
 *
 * Theme header actions and filters.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Sticky header options for header types.
 */
add_action( 'kalium_sticky_header_options', '_kalium_sticky_header_options_default', 10 );

/**
 * Header position and spacing.
 */
add_filter( 'body_class', '_kalium_absolute_header_body_class' );
add_action( 'wp_head', '_kalium_header_position_spacing_action' );

/**
 * Custom logo and menu options for current page.
 */
add_action( 'wp', '_kalium_header_custom_logo_and_menu_on_page' );

/**
 * Site header, top header bar, breadcrumb and heading title display.
 */
add_action( 'kalium_wrapper_start', 'kalium_header_display', 10 );
//TMPadd_action( 'kalium_wrapper_start', 'kalium_breadcrumb_display', 20 );
add_action( 'kalium_wrapper_start', 'kalium_page_heading_title_display', 30 );

/**
 * Before wrapper hooks.
 */
add_action( 'kalium_before_wrapper', 'kalium_display_page_borders', 10 );
add_action( 'kalium_before_wrapper', 'kalium_header_display_mobile_menu', 20 );
add_action( 'kalium_before_wrapper', 'kalium_header_display_top_menu', 30 );
add_action( 'kalium_before_wrapper', 'kalium_header_display_side_menu', 40 );

/**
 * Header display function.
 */
add_action( 'kalium_header_content', 'kalium_header_top_bar_display', 10 );
add_action( 'kalium_header_content', 'kalium_header_content_display', 20 );

/**
 * Header content for main row.
 */
add_action( 'kalium_header_content_main', 'kalium_header_content_left', 10 );
add_action( 'kalium_header_content_main', 'kalium_header_content_logo', 20 );
add_action( 'kalium_header_content_main', 'kalium_header_content_right', 30 );

/**
 * Header content after main row.
 */
add_action( 'kalium_header_content_after', 'kalium_header_content_below', 10 );
