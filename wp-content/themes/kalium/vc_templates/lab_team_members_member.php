<?php
/**
 *    Team Member
 *
 *    Laborator.co
 *    www.laborator.co
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

if ( strpos( $description, '#E-' ) !== false ) {
	$description = vc_value_from_safe( $description );
	$description = nl2br( $description );
}

// Element Class
$class     = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

// If no image return empty
if ( ! $image ) {
	return;
}

$thumb_size = $img_size;

$link = vc_build_link( $link );

// Fix the issue with {space}_blank target
if ( isset( $link['target' ] ) ) {
	$link['target'] = trim( $link['target'] );
}

// Image size
if ( ! empty( $thumb_size ) && strpos( $thumb_size, 'x' ) !== false ) {
    $thumb_size = explode( 'x', $thumb_size );
}

// Wow Effect
$wow_effect     = $reveal_effect;
$wow_one_by_one = false;

if ( preg_match( '/-one/', $wow_effect ) ) {
	$wow_one_by_one = true;
	$wow_effect     = str_replace( '-one', '', $wow_effect );
}

// Item Class
$item_class = array();

switch ( $columns_count ) {
	case 1:
		$item_class[]  = 'col-sm-12';
		$wow_max_delay = 0.2;
		break;

	case 2:
		$item_class[]  = 'col-sm-6';
		$wow_max_delay = 0.5;
		break;

	case 3:
		$item_class[]  = 'col-md-4 col-sm-6';
		$wow_max_delay = 1.2;
		break;

	case 4:
		$item_class[]  = 'col-md-3 col-sm-6';
		$wow_max_delay = 1.5;
		break;
}

$wow_delay = min( $team_member_index * 0.1, $wow_max_delay );

// Member Details
ob_start();

?>
    <div class="member-details">
        <h2>
			<?php if ( $link['url'] ) : ?>
                <a href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>"
                   title="<?php echo esc_attr( $link['title'] ); ?>"><?php echo esc_html( $name ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $name ); ?>
			<?php endif; ?>
        </h2>
		<?php if ( $sub_title ) : ?>
            <p class="job-title"><?php echo esc_html( $sub_title ); ?></p>
		<?php endif; ?>
    </div>
<?php

$member_details = ob_get_clean();
$visible_titles = 'visible-titles' == $layout_type;

$css_class .= " layout-{$layout_type}";

?>
    <div class="<?php echo implode( ' ', $item_class ); ?>">

        <div class="member<?php echo esc_attr( $css_class );
		when_match( $wow_effect, "wow {$wow_effect}" ); ?>"
             data-wow-duration="1s"<?php if ( $wow_one_by_one ) : ?> data-wow-delay="<?php echo esc_attr( $wow_delay ); ?>s"<?php endif; ?>>
            <div class="thumb">
				<?php if ( $hover_style != 'none' && ( $visible_titles && $description || ! $visible_titles ) ) : ?>
                    <div class="hover-state padding<?php when_match( $hover_style == 'distanced', 'with-spacing' ); ?>"<?php if ( ! empty( $link['url'] ) ) : ?> data-href="<?php echo $link['url']; ?>"<?php endif; ?>>
						<?php if ( $description ) : ?>
                            <div class="social">
								<?php echo $description; ?>
                            </div>
						<?php endif; ?>

						<?php if ( ! $visible_titles ) : ?>
							<?php echo $member_details; ?>
						<?php endif; ?>
                    </div>
				<?php endif; ?>

				<?php if ( $link['url'] ) : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>"
                       title="<?php echo esc_attr( $link['title'] ); ?>">
						<?php echo kalium_get_attachment_image( $image, $thumb_size ); ?>
                    </a>
				<?php else : ?>
					<?php echo kalium_get_attachment_image( $image, $thumb_size ); ?>
				<?php endif; ?>
            </div>

			<?php if ( $visible_titles ) : ?>
				<?php echo $member_details; ?>
			<?php endif; ?>
        </div>

    </div>
<?php


# End of File
$team_member_index ++;