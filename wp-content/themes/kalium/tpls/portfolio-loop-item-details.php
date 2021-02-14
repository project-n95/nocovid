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


global $i, $portfolio_args;

// Item Class
$item_class = array( 'portfolio-item' );

// Item Details
$portfolio_item_id    = get_the_ID();
$portfolio_item_title = get_the_title();
$portfolio_item_href  = get_permalink();

$portfolio_item_type     = kalium_get_field( 'item_type' );
$portfolio_item_subtitle = kalium_get_field( 'sub_title' );

$portfolio_item_new_window = false;

$portfolio_item_terms  = kalium_unique_terms( get_the_terms( $portfolio_item_id, 'portfolio_category' ) );
$portfolio_terms_slugs = array();

// Featured Image Id
$post_thumbnail_id = get_post_thumbnail_id();

// Custom Vars
$custom_hover_background_color   = kalium_get_field( 'custom_hover_background_color' );
$custom_hover_color_transparency = kalium_get_field( 'hover_color_transparency' );

// Dominant image color for hover layer
$layout_options = kalium_get_array_key( $portfolio_args['layouts'], kalium_conditional( 'type-1' === $portfolio_args['layout_type'], 'type_1', 'type_2' ) );

if ( 'dominant-color' === $layout_options['hover_style'] && $post_thumbnail_id && ( $dominant_image_color = kalium()->images->get_dominant_color( $post_thumbnail_id ) ) ) {
	$custom_hover_background_color = $dominant_image_color;
}

$hover_effect_style  = kalium_get_field( 'hover_effect_style' );
$hover_layer_options = kalium_get_field( 'hover_layer_options' );


// Portfolio Item Type Class
$item_class[] = 'portfolio-item-' . $portfolio_item_type;


// Create Term Slugs
if ( is_array( $portfolio_item_terms ) ) {
	foreach ( $portfolio_item_terms as $term ) {
		$portfolio_terms_slugs[] = $term->slug;
	}
}


// Item Effect
$reveal_effect = $portfolio_args['reveal_effect'];
$show_effect   = '';
$reveal_delay  = 0.00;
$delay_wait    = 0.15;

if ( false !== strpos( $reveal_effect, '-one' ) ) {
	$reveal_delay = $i % ( $portfolio_args['columns'] * 2 ) * $delay_wait;
}

if ( $reveal_delay ) {
	$reveal_delay = str_replace( ',', '.', "{$reveal_delay}" );
}

switch ( $reveal_effect ) {
	case 'fade':
	case 'fade-one':
		$show_effect = 'fadeIn';
		break;

	case 'slidenfade':
	case 'slidenfade-one':
		$show_effect = 'fadeInLab';
		break;

	case 'zoom':
	case 'zoom-one':
		$show_effect = 'zoomIn';
		break;
}

if ( $show_effect ) {
	$show_effect = "wow {$show_effect}";
}


// Custom Link
$item_linking          = kalium_get_field( 'item_linking' );
$item_launch_link_href = kalium_get_field( 'launch_link_href' );
$item_new_window       = kalium_get_field( 'new_window' );

if ( 'external' == $item_linking && ! empty( $item_launch_link_href ) && '#' != $item_launch_link_href ) {
	$portfolio_item_href       = $item_launch_link_href;
	$portfolio_item_new_window = $item_new_window;
}
