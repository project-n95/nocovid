<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$borders_color      = kalium_get_theme_option('theme_borders_color');
$animations         = kalium_get_theme_option('theme_borders_animation');
$border_width       = kalium_get_theme_option('theme_borders_thickness');
$animation_duration = kalium_get_theme_option('theme_borders_animation_duration');
$animation_delay    = kalium_get_theme_option('theme_borders_animation_delay');

if ( empty($borders_color ) ) {
	$borders_color = '#f3f3ef';
}

if ( $border_width ) {
	$border_width = absint( intval( $border_width ) / 2 );
} else {
	$border_width = 11;
}

if ( $animation_duration ) {
	$animation_duration = floatval( $animation_duration );
} else {
	$animation_duration = 0;
}

if ( $animation_delay ) {
	$animation_delay = floatval( $animation_delay );
} else {
	$animation_delay = 0;
}

kalium_append_custom_css( '.page-border > .top-border, .page-border > .right-border, .page-border > .bottom-border, .page-border > .left-border', "padding: {$border_width}px; background: {$borders_color} !important;" );

$border_width *= 2;

$full_margin_elements           = array();
$horizontal_margin_elements     = array();
$vertical_margin_elements       = array();

$full_margin_elements[]         = 'body > .wrapper';
$full_margin_elements[]         = '.top-menu-container';
$full_margin_elements[]         = '.portfolio-description-container';
$full_margin_elements[]         = '.single-portfolio-holder .portfolio-navigation';
$full_margin_elements[]         = '.portfolio-slider-nav';
$full_margin_elements[]         = '.site-footer';
$full_margin_elements[]         = '.woocommerce-demo-store .woocommerce-store-notice';
$full_margin_elements[]         = '.fullscreen-menu-footer';

$margin_horizontal_elements[]   = '.main-header.fullwidth-header';
$margin_horizontal_elements[]   = 'demo_store';
$margin_horizontal_elements[]   = '.nivo-lightbox-theme-default .nivo-lightbox-close';

$vertical_margin_elements[]     = '.nivo-lightbox-theme-default .nivo-lightbox-close';

// Full Margin Elements
if ( ! empty( $full_margin_elements )  ) {
	kalium_append_custom_css( implode( ', ', $full_margin_elements ), "margin: {$border_width}px;", 'screen and (min-width: 768px)' );
	kalium_append_custom_css( '.site-footer.fixed-footer', "left: {$border_width}px; right: {$border_width}px;", 'screen and (min-width: 992px)' );
}

// Horizontal Margin Elements
if ( ! empty( $margin_horizontal_elements ) ) {
	kalium_append_custom_css( implode( ', ', $margin_horizontal_elements ), "margin-left: {$border_width}px; margin-right: {$border_width}px;" );
}

// Vertical Margin Elements
if ( ! empty( $vertical_margin_elements ) ) {
	kalium_append_custom_css( implode( ', ', $vertical_margin_elements ), "margin-top: {$border_width}px; margin-top: {$border_width}px;" );
}


// Calculate body height and min-height
$border_width *= 2;

kalium_append_custom_css( 'body', "height: calc(100% - {$border_width}px); min-height: calc(100% - {$border_width}px);" );
?>
<div class="page-border<?php echo when_match( $animations == 'fade', 'wow fadeIn' ); ?>" data-wow-duration="<?php echo esc_attr( $animation_duration ) . 's'; ?>" data-wow-delay="<?php echo esc_attr( $animation_delay ) . 's'; ?>">
	<div class="top-border<?php echo when_match( $animations == 'slide', 'wow fadeInDown' ); ?>"></div>
	<div class="right-border<?php echo when_match( $animations == 'slide', 'wow fadeInRight' ); ?>"></div>
	<div class="bottom-border<?php echo when_match( $animations == 'slide', 'wow fadeInUp' ); ?>"></div>
	<div class="left-border<?php echo when_match( $animations == 'slide', 'wow fadeInLeft' ); ?>"></div>
</div>
