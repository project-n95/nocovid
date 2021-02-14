<?php
/**
 *	Icon Box
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
if( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

// Element Class
$class = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

?>
<div class="icon-box-container <?php echo esc_attr( $css_class ) . vc_shortcode_custom_css_class( $css, ' ' ); ?>">
	
	<?php echo wpb_js_remove_wpautop( $content ); ?>
	
</div>