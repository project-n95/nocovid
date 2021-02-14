<?php
/**
 *	Custom Image Gallery
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// (Deprecated) Default filters for Visual Composer element classes
function kalium_vc_shortcodes_css_class_filter_deprecated( $classes, $base = '', $atts = array() ) {
	
	// Light gallery support for VC Masonry Media Grid
	if ( in_array( $base, array( 'vc_masonry_media_grid', 'vc_media_grid', 'vc_gallery' ) ) && 'yes' == kalium_get_array_key( $atts, 'use_light_gallery' ) ) {
		$classes .= ' light-gallery--enabled';
		
		// Enqueue light gallery library
		kalium_enqueue_lightbox_library();
	}
	
	return $classes;
}

add_filter( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'kalium_vc_shortcodes_css_class_filter_deprecated', 10, 3 );