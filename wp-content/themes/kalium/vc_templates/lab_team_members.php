<?php
/**
 *	Team Members
 *
 *	Laborator.co
 *	www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

global $team_member_index, $columns_count, $reveal_effect, $hover_style, $img_size, $layout_type;

// Atts
if ( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

$element_id = 'team-members-' . mt_rand( 100000, 999999 );

if ( $hover_bg ) {
	kalium_append_custom_css( "#{$element_id} .member .hover-state", "background-color: {$hover_bg}" );
	kalium_append_custom_css( "#{$element_id} .thumb:hover .hover-state", "opacity: 1" );
}

if ( $hover_txt ) {
	kalium_append_custom_css( "#{$element_id} .member .hover-state *", "color: {$hover_txt}" );
	kalium_append_custom_css( "#{$element_id} .member .hover-state a:after", "background-color: {$hover_txt}" );
}

// Element Class
$class = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

// Thumb Size
if ( ! preg_match( '/^[0-9]+x[0-9]+$/', $img_size ) ) {
	$img_size = "460x460";
}

$thumb_size = explode( 'x', $img_size );

// Show Team Members
$team_member_index = 0;

?>
<div id="<?php echo esc_attr( $element_id ); ?>" class="<?php echo esc_attr( $el_class ); ?>">
	<div class="team-holder">
		<div class="row">
			<?php echo wpb_js_remove_wpautop( $content ); ?>
		</div>
	</div>
</div>
<style>
#<?php echo esc_attr( $element_id ); ?> .member-empty-spacing {
	padding-top: <?php echo $thumb_size[1] / $thumb_size[0] * 100; ?>%;
}
</style>
<?php
