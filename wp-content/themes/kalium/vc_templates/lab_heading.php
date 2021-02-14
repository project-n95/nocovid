<?php
/**
 *    Heading Box
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
if ( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

// Element Class
$class     = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

$css_class = "section-title {$css_class}";

?>
<div class="<?php echo esc_attr( $css_class ) . vc_shortcode_custom_css_class( $css, ' ' ); ?>">
    <<?php echo $title_tag; ?>><?php echo esc_html( $title ); ?></<?php echo $title_tag; ?>>
<?php echo wp_kses_post( wpautop( $content ) ); ?>
</div>