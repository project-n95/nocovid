<?php
/**
 * Kalium WordPress Theme
 *
 * Theme header.
 *
 * @author     Laborator
 * @link       https://kaliumtheme.com
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Header classes
$header_classes = [
	'site-header',
	'main-header',
];

// Header type and sticky header
$menu_type     = kalium_get_theme_option( 'main_menu_type' );
$sticky_header = kalium_get_theme_option( 'sticky_header' );

// Menu type header class
$header_classes[] = "menu-type-{$menu_type}";

// Header options
$header_padding_top    = kalium_get_theme_option( 'header_vpadding_top' );
$header_padding_bottom = kalium_get_theme_option( 'header_vpadding_bottom' );
$header_fullwidth      = kalium_is_fullwidth_header();

// Header styling
$header_background_color = kalium_get_theme_option( 'header_background_color' );
$header_bottom_border    = kalium_get_theme_option( 'header_bottom_border' );
$header_bottom_spacing   = kalium_get_theme_option( 'header_bottom_spacing' );

// Fullwidth header
if ( $header_fullwidth ) {
	$header_classes[] = 'fullwidth-header';
}

// Sticky header
if ( $sticky_header ) {

	// Sticky header is active
	$header_classes[] = 'is-sticky';

	// Custom header sticky menu
	kalium_define_js_variable( 'stickyHeaderOptions', kalium_get_sticky_header_options() );

	// Logo switch on sections
	kalium_define_js_variable( 'logoSwitchOnSections', kalium_get_logo_switch_sections() );
}

// Header vertical padding: Top
if ( is_numeric( $header_padding_top ) && $header_padding_top >= 0 ) {
	kalium_append_custom_css( '.header-block, .site-header--static-header-type', "padding-top: {$header_padding_top}px;" );

	// Responsive
	if ( $header_padding_top >= 40 ) {
		kalium_append_custom_css( '.header-block, .site-header--static-header-type', 'padding-top: ' . ( $header_padding_top / 2 ) . 'px;', 'screen and (max-width: 992px)' );
	}

	if ( $header_padding_top >= 40 ) {
		kalium_append_custom_css( '.header-block, .site-header--static-header-type', 'padding-top: ' . ( $header_padding_top / 3 ) . 'px;', 'screen and (max-width: 768px)' );
	}
}

// Header vertical padding: Bottom
if ( is_numeric( $header_padding_bottom ) && $header_padding_bottom >= 0 ) {
	kalium_append_custom_css( '.header-block, .site-header--static-header-type', "padding-bottom: {$header_padding_bottom}px;" );

	// Responsive
	if ( $header_padding_top >= 40 ) {
		kalium_append_custom_css( '.header-block, .site-header--static-header-type', 'padding-bottom: ' . ( $header_padding_bottom / 2 ) . 'px;', 'screen and (max-width: 992px)' );
	}

	if ( $header_padding_top >= 40 ) {
		kalium_append_custom_css( '.header-block, .site-header--static-header-type', 'padding-bottom: ' . ( $header_padding_bottom / 3 ) . 'px;', 'screen and (max-width: 768px)' );
	}
}

// Header background color
if ( $header_background_color ) {
	kalium_append_custom_css( '.header-block, .site-header--static-header-type', "background-color: {$header_background_color}" );
}

// Bottom spacing
if ( is_numeric( $header_bottom_spacing ) && $header_bottom_spacing >= 0 ) {
	kalium_append_custom_css( '.site-header', "margin-bottom: {$header_bottom_spacing}px;" );
}

// Bottom border
if ( $header_bottom_border ) {
	$header_classes[] = 'header-bottom-border';

	if ( '' === $header_bottom_spacing ) {
		$header_classes[] = 'header-bottom-spacing';
	}

	kalium_append_custom_css( '.header-bottom-border', "border-bottom-color: {$header_bottom_border}" );
}
?>
    <header <?php kalium_class_attr( $header_classes ); ?>>

		<?php
		/**
		 * Hook: kalium_header_content.
		 *
		 * @hooked kalium_header_top_bar_display - 10
		 * @hooked kalium_header_content_display - 20
		 */
		do_action( 'kalium_header_content' );
		?>

    </header>

<?php

