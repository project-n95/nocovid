<?php
/**
 *	Divider
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

$unique_id = 'divider-' . mt_rand(1000, 10000);

if( function_exists( 'uniqid' ) ) {
	$unique_id .= uniqid();
}

$css_classes = array(
	$this->getExtraClass( $el_class ),
	vc_shortcode_custom_css_class( $css ),
);

$css_class = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $css_classes ) ), $this->settings['base'], $atts ) );

if ( $type == 'text' ) {
	
	if ( $text_color ) {
		
		if ( $text_style == 4 && empty( $text_color_font ) ) {
			$text_color_font = '#ffffff';
		}
		
		$text_color_font = empty( $text_color_font ) ? $text_color : $text_color_font;
		
		kalium_append_custom_css( "#{$unique_id} .lab-divider-content span", "color: {$text_color_font};" );
		
		switch ( $text_style ) {
				
			case '1':
				kalium_append_custom_css( "#{$unique_id} div span", "border-color: {$text_color};" );
				break;
			
			case '2':
			case '3':
				kalium_append_custom_css( "#{$unique_id} .divider-line span ", "border-top-color: {$text_color};" );
				break;
				
			case '4':
				kalium_append_custom_css( "#{$unique_id} div span", "border-color: {$text_color};" );
				kalium_append_custom_css( "#{$unique_id} .lab-divider-content span", "background-color: {$text_color};" );
				break;
				
			case '5':
				kalium_append_custom_css( "#{$unique_id} .divider-line span", "background-color: {$text_color};" );
				break;
				
		}
	}
	
	?>	
	<div id="<?php echo $unique_id; ?>" class="lab-divider divider-type-<?php echo $text_style; echo $css_class ? " {$css_class}" : ''; ?>">
		<div class="divider-line divider-left">
			<span></span>
			<span class="double-line"></span>
		</div>
	
		<div class="lab-divider-content">
			<span><?php echo do_shortcode( $title ); ?></span>
		</div>
		
		<div class="divider-line divider-right">
			<span></span>
			<span class="double-line"></span>
		</div>
	</div>
	<?php
} else {
	$width = floatval( $plain_width );
	
	$plain_style_attr = '';
	
	if( $width && is_numeric( $width ) && $width > 0 && $width <= 100 ) {
		$plain_style_attr .= 'display: block; width: ' . $width . ( strstr( $plain_width, 'px' ) ? 'px' : '%' ) . ';';
	}
	
	if( $plain_color ) {
		$plain_style_attr .= ' border-bottom-color: ' . $plain_color;
	}
	
	kalium_append_custom_css( "#{$unique_id} .lab-divider-content", $plain_style_attr );
	
	?>
	<div id="<?php echo $unique_id; ?>" class="lab-divider divider-plain type-<?php echo $plain_style; echo $css_class ? " {$css_class}" : ''; ?>">
		<div class="lab-divider-content"></div>
	</div>
	<?php
}