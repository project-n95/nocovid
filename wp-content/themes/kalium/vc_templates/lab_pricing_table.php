<?php
/**
 *    Pricing Table Shortcode
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

$title       = $atts['title'];
$table_style = $atts['table_style'];

$unique_id = 'divider-' . mt_rand( 1000, 10000 );

if ( function_exists( 'uniqid' ) ) {
	$unique_id .= uniqid();
}

$plan_features      = $atts['plan_features'];
$plan_features_list = array_filter( explode( PHP_EOL, $plan_features ) );

$purchase_link = vc_build_link( $atts['purchase_link'] );

// Plan Description Text
$plan_description      = $atts['plan_description'];
$plan_description_safe = vc_value_from_safe( $plan_description );

if ( strpos( $plan_description, '#E-' ) == 0 ) {
	$plan_description = $plan_description_safe;
}

// Custom Class
$css_classes = array(
	$this->getExtraClass( $atts['el_class'] ),
	'pricing-table',
	'pricing-table--' . $table_style,
	vc_shortcode_custom_css_class( $atts['css'] ),
);

$css_class = preg_replace( '/\s+/', ' ', apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, implode( ' ', array_filter( $css_classes ) ), $this->settings['base'], $atts ) );

// Colors
$background_color                = $atts['background_color'];
$header_background_color         = $atts['header_background_color'];
$header_text_color               = $atts['header_text_color'];
$title_background_color          = $atts['title_background_color'];
$title_text_color                = $atts['title_text_color'];
$list_text_color                 = $atts['list_text_color'];
$list_separator_text_color       = $atts['list_separator_text_color'];
$purchase_background_color       = $atts['purchase_background_color'];
$purchase_background_hover_color = $atts['purchase_background_hover_color'];
$purchase_text_color             = $atts['purchase_text_color'];
$purchase_text_hover_color       = $atts['purchase_text_hover_color'];

if ( $background_color ) {
	kalium_append_custom_css( "#{$unique_id} .plan", "background-color: {$background_color};" );
}

if ( $header_background_color ) {
	$header_text_color = empty( $header_text_color ) ? '#ffffff' : $header_text_color;

	kalium_append_custom_css( "#{$unique_id} .plan .plan-head", "background-color: {$header_background_color};" );
}

if ( $header_background_color || $header_text_color ) {
	$header_text_color = empty( $header_text_color ) ? '#ffffff' : $header_text_color;
	kalium_append_custom_css( "#{$unique_id} .plan .plan-head *", "color: {$header_text_color};" );
}

if ( $title_background_color ) {
	kalium_append_custom_css( "#{$unique_id} .plan .plan-name", "background-color: {$title_background_color};" );
}

if ( $title_background_color || $title_text_color ) {
	$title_text_color = empty( $title_text_color ) ? '#ffffff' : $title_text_color;
	kalium_append_custom_css( "#{$unique_id} .plan .plan-name", "color: {$title_text_color};" );
}

if ( $list_text_color ) {
	kalium_append_custom_css( "#{$unique_id} .plan .plan-row", "color: {$list_text_color};" );
}

if ( $list_separator_text_color ) {
	kalium_append_custom_css( "#{$unique_id} .plan li", "border-bottom-color: {$list_separator_text_color};" );
}

if ( $purchase_background_color ) {
	$purchase_text_color = empty( $purchase_text_color ) ? '#ffffff' : $purchase_text_color;
	kalium_append_custom_css( "#{$unique_id} .plan .plan-action .btn", "background-color: {$purchase_background_color}; color: {$purchase_text_color};" );
}

if ( $purchase_background_hover_color ) {
    kalium_append_custom_css( "#{$unique_id} .plan .plan-action .btn:hover", "background-color: {$purchase_background_hover_color};" );
}

if ( $purchase_text_hover_color ) {
	kalium_append_custom_css( "#{$unique_id} .plan .plan-action .btn:hover", "color: {$purchase_text_hover_color};" );
}

// Border
$border = shortcode_atts( array(
	'border_color'  => '',
	'border_width'  => '',
	'border_radius' => '',
), $atts );

$border_style = array();

if ( ! empty( $border['border_color'] ) ) {
	$border_style[] = 'border-color: ' . $border['border_color'];
}

if ( is_numeric( $border['border_width'] ) ) {
	$border_style[] = 'border-style: solid';
	$border_style[] = 'border-width: ' . $border['border_width'] . 'px';
}

if ( is_numeric( $border['border_radius'] ) ) {
	$border_style[] = 'border-radius: ' . $border['border_radius'] . 'px';
}

if ( count( $border_style ) ) {
	kalium_append_custom_css( "#{$unique_id} .plan", implode( ';', $border_style ) );
}
?>
<div id="<?php echo $unique_id; ?>" class="<?php echo $css_class; ?>">

    <ul class="plan">
        <li class="plan-head">
            <p class="price"><?php echo $atts['plan_price']; ?></p>
			<?php
			if ( $plan_description ) :
				echo wpautop( do_shortcode( $plan_description ) );
			endif;
			?>
        </li>

		<?php if ( $title ) : ?>
            <li class="plan-name"><?php echo $title; ?></li>
		<?php endif; ?>

		<?php
		foreach ( $plan_features_list as $feature ) :

			$feature = preg_replace( '/\*(.*?)\*/', '<strong>$1</strong>', $feature );
			?>
            <li class="plan-row">
				<?php echo do_shortcode( $feature ); ?>
            </li>
		<?php

		endforeach;
		?>

		<?php if ( ! empty( $purchase_link['title'] ) ) : ?>
            <li class="plan-action">
                <a href="<?php echo esc_url( $purchase_link['url'] ); ?>" target="<?php echo esc_attr( $purchase_link['target'] ); ?>" class="btn btn-primary">
					<?php echo esc_html( $purchase_link['title'] ); ?>
                </a>
            </li>
		<?php endif; ?>
    </ul>

</div>