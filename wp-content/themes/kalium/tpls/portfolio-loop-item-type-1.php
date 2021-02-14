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

// Get Portfolio Item Details
include locate_template( 'tpls/portfolio-loop-item-details.php' );

// Main Vars
$portfolio_image_size = 'portfolio-img-1';
$hover_effect         = $portfolio_args['layouts']['type_1']['hover_effect'];
$hover_transparency   = $portfolio_args['layouts']['type_1']['hover_transparency'];
$hover_style          = $portfolio_args['layouts']['type_1']['hover_style'];

// Hover effect style
$custom_hover_effect_style = '';

if ( $hover_effect_style != 'inherit' ) {
	$hover_effect = $hover_effect_style;
}

// Custom value for Transparency
if ( in_array( $custom_hover_color_transparency, array( 'opacity', 'no-opacity' ) ) ) {
	$hover_transparency = $custom_hover_color_transparency;
}

// Disable Order
if ( 'none' == $hover_layer_options ) {
	$hover_effect = 'none';
}

// Padding
$item_class[] = 'has-padding';

// Item Class
$item_class[] = kalium_portfolio_get_columns_class( $portfolio_args['columns'] );

// Hover State Class
$hover_state_class = array();

$hover_state_class[] = 'on-hover';
$hover_state_class[] = 'opacity-' . ( $hover_transparency == 'opacity' ? 'yes' : 'no' );
$hover_state_class[] = 'hover-style-' . $hover_style;

if ( $hover_effect == 'distanced' ) {
	$hover_state_class[] = 'distanced';
}


// Dynamic Image Height
if ( $portfolio_args['layouts']['type_1']['dynamic_image_height'] ) {
	$portfolio_image_size = 'portfolio-img-3';
	$item_class[]         = 'dynamic-height-image';
}

// Show Animated Eye on Hover
if ( 'animated-eye' == $portfolio_args['layouts']['type_1']['hover_layer_icon'] ) {
	$item_class[] = 'animated-eye-icon';
}

// Item Thumbnail
$image = kalium_get_attachment_image( $post_thumbnail_id, apply_filters( 'kalium_portfolio_loop_thumbnail_size', $portfolio_image_size, 'type-1' ) );
?>
<div <?php post_class( $item_class ); ?> data-portfolio-item-id="<?php echo $portfolio_item_id; ?>"<?php if ( $portfolio_terms_slugs ) : ?> data-terms="<?php echo implode( ' ', $portfolio_terms_slugs ); ?>"<?php endif; ?>>

	<?php
	// Custom Background color for this item
	if ( $custom_hover_background_color ) {
		kalium_append_custom_css( "#{$portfolio_args['id']}.portfolio-holder .post-{$portfolio_item_id} .item-box .on-hover", "background-color: {$custom_hover_background_color} !important;" );
	}
	?>

	<?php do_action( 'kalium_portfolio_item_before', $portfolio_item_type ); ?>

    <div class="item-box <?php echo esc_attr( $show_effect ); ?>"<?php if ( $reveal_delay ) : ?> data-wow-delay="<?php echo esc_attr( $reveal_delay ); ?>s"<?php endif; ?>>
        <div class="photo">
            <a href="<?php echo esc_url( $portfolio_item_href ); ?>" class="item-link"<?php echo when_match( $portfolio_item_new_window, 'target="_blank" rel="noopener"' ); ?>>
				<?php echo $image; ?>

				<?php if ( 'none' !== $hover_effect ) : ?>
                    <span class="<?php echo implode( ' ', $hover_state_class ); ?>">
					<?php if ( 'custom' == $portfolio_args['layouts']['type_1']['hover_layer_icon'] ) : ?>
                        <span class="custom-hover-icon">
						<?php echo $portfolio_args['layouts']['type_1']['hover_layer_icon_markup']; ?>
						</span>
					<?php else: ?>
                        <i class="icon icon-basic-eye"></i>
					<?php endif; ?>
				</span>
				<?php endif; ?>
            </a>
        </div>

        <div class="info">
            <h3>
                <a href="<?php echo esc_url( $portfolio_item_href ); ?>" class="item-link"<?php echo when_match( $portfolio_item_new_window, 'target="_blank" rel="noopener"' ); ?>>
					<?php echo wp_kses_post( $portfolio_item_title ); ?>
                </a>
            </h3>

			<?php include locate_template( 'tpls/portfolio-loop-item-categories.php' ); ?>
        </div>
    </div>

	<?php do_action( 'kalium_portfolio_item_after' ); ?>

</div>