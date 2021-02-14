<?php
/**
 *	Scroll Box
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
if ( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

// Unique element id
$element_id = sprintf( 'scrollbox-%d', mt_rand( 100000, 999999 ) );

// Element Class
$class = $this->getExtraClass( $el_class );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, sprintf( 'lab-scroll-box %1$s %2$s', $class, vc_shortcode_custom_css_class( $css, ' ' ) ), $this->settings['base'], $atts );

?>
<div id="<?php echo esc_attr( $element_id ); ?>" class="<?php echo esc_attr( $css_class ); ?>" data-height="<?php echo intval( $scroll_height ); ?>">
	
	<div class="lab-scroll-box-content" style="<?php printf( 'max-height: %dpx;', $scroll_height ); ?>">
		
		<?php
			// Box content
			echo apply_filters( 'the_content', $content ); 
		?>
		
	</div>
	
</div>