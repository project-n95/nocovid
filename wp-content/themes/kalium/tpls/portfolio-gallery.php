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

$masonry_mode_gallery = kalium_get_field( 'masonry_mode_gallery' );

$img_presentation_role = [
	'role' => 'presentation',
];

// Container classes
$gallery_classes = [ 'gallery' ];

if ( $full_width_gallery ) {
	$gallery_classes[] = 'full-width-container';
}

if ( 'nospacing' === $image_spacing ) {
	$gallery_classes[] = 'no-spacing';
}

if ( 'below' === $image_captions_position ) {
	$gallery_classes[] = 'captions-below';
}

if ( 'hide' === $image_captions_position ) {
	$gallery_classes[] = 'captions-hide';
}

if ( $masonry_mode_gallery ) {
	$gallery_classes[] = 'masonry-mode-gallery';

	// Enqueue Isotope
	kalium_enqueue_isotope_and_packery_library();
}

?>
<div <?php kalium_class_attr( $gallery_classes ); ?>>

    <div class="row nivo">
		<?php
		foreach ( $gallery_items as $i => $gallery_item ) :

			$main_thumbnail_size = 1;

			// General Vars
			$column_width = isset( $gallery_item['column_width'] ) ? $gallery_item['column_width'] : '1-2';

			// Column Classes
			$column_classes = [ 'col-xs-12' ];

			if ( $column_width == '1-2' ) {
				$column_classes[]    = 'col-sm-6';
				$main_thumbnail_size = 2;
			} elseif ( $column_width == '1-3' ) {
				$column_classes[]    = 'col-sm-4';
				$main_thumbnail_size = 3;
			} elseif ( $column_width == '2-3' ) {
				$column_classes[]    = 'col-sm-8';
				$main_thumbnail_size = 2;
			} elseif ( $column_width == '1-4' ) {
				$column_classes[]    = 'col-sm-3';
				$main_thumbnail_size = 4;
			}

			$main_thumbnail_size = apply_filters( 'kalium_single_portfolio_gallery_image', 'portfolio-single-img-' . $main_thumbnail_size );

			$item_classes           = [ 'photo' ];
			$item_animation_classes = '';

			switch ( $images_reveal_effect ) {
				case 'slidenfade':
					$item_animation_classes = 'wow fadeInLab';
					break;

				case 'fade':
					$item_animation_classes = 'wow fadeIn';
					break;

				default:
					$item_animation_classes = 'wow';
			}

			$item_classes[]       = $item_animation_classes;


			// Image Type
			if ( 'image' == $gallery_item['acf_fc_layout'] ) :

				$img = $gallery_item['image'];
				$caption          = nl2br( make_clickable( $img['caption'] ) );
				$alt_text         = $img['alt'];
				$href             = $img['url'];

				if ( ! $img['id'] ) {
					continue;
				}

				$is_video = $alt_text && preg_match( '/(youtube\.com|vimeo\.com)/i', $alt_text );

				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="<?php echo implode( ' ', $item_classes ); ?>">

                        <a href="<?php echo $is_video ? esc_url( $alt_text ) : esc_url( $href ); ?>" data-lightbox-gallery="post-gallery">
							<?php
							echo kalium_get_attachment_image( $img['id'], $main_thumbnail_size, $img_presentation_role );
							?>
                        </a>

						<?php if ( $caption ) : ?>

                            <div class="caption">
								<?php echo wp_kses_post( $caption ); ?>
                            </div>

						<?php endif; ?>

                    </div>

                </div>
			<?php

			endif;
			// End: Image Type


			// Image Slider
			if ( 'images_slider' == $gallery_item['acf_fc_layout'] ) :

				$gallery_images = $gallery_item['images'];
				$auto_switch      = $gallery_item['auto_switch'];

				if ( ! is_array( $gallery_images ) || ! $gallery_images ) {
					continue;
				}

				kalium_enqueue_slick_slider_library();

				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="portfolio-images-slider <?php echo $item_animation_classes; ?>"<?php if ( $auto_switch ) : ?> data-autoswitch="<?php echo esc_attr( $auto_switch ); ?>"<?php endif; ?>>
						<?php
						foreach ( $gallery_images as $j => $image ) :

							$img_class = when_match( $j > 0, 'hidden', '', false );
							$caption = $image['caption'];

							?>
                            <div class="image-slide nivo">

                                <a href="<?php echo esc_url( $image['url'] ); ?>" title="<?php echo esc_attr( apply_filters( 'kalium_portfolio_lightbox_image_caption', $caption ) ); ?>" data-lightbox-gallery="post-gallery-<?php echo esc_attr( $i ); ?>">

									<?php
									echo kalium_get_attachment_image( $image['id'], $main_thumbnail_size, $img_presentation_role, ( $j > 0 ? array( 'class' => 'hidden' ) : '' ) );
									?>

                                </a>

                            </div>
						<?php

						endforeach;
						?>
                    </div>

                </div>
			<?php

			endif;
			// End: Image Slider


			// Comparison Images
			if ( 'comparison_images' == $gallery_item['acf_fc_layout'] ) :

				$image_1 = $gallery_item['image_1'];
				$image_2          = $gallery_item['image_2'];

				$image_1_label = $image_1['title'];
				$image_2_label = $image_2['title'];

				$image_1_attachment = wp_get_attachment_image_src( $image_1['id'], $main_thumbnail_size );
				$image_1_id         = laborator_generate_as_element( array(
					$image_1_attachment[1],
					$image_1_attachment[2]
				) );

				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="image-placeholder">
                        <figure class="comparison-image-slider image-placeholder-bg <?php echo esc_attr( $image_1_id ); ?>">

                            <img data-src="<?php echo esc_url( $image_1_attachment[0] ); ?>" class="lazyload"/>

							<?php if ( $image_1_label ) : ?>
                                <span class="cd-image-label" data-type="original"><?php echo esc_html( $image_1_label ); ?></span>
							<?php endif; ?>

                            <div class="cd-resize-img">
								<?php echo wp_get_attachment_image( $image_2['id'], $main_thumbnail_size ); ?>
								<?php if ( $image_2_label ) : ?>
                                    <span class="cd-image-label" data-type="modified"><?php echo esc_html( $image_2_label ); ?></span>
								<?php endif; ?>
                            </div>

                            <span class="cd-handle"></span>
                        </figure>
                    </div>

                </div>
			<?php

			endif;
			// End: Comparison Images


			// YouTube Video
			if ( 'youtube_video' == $gallery_item['acf_fc_layout'] ) :

				$video_url = $gallery_item['video_url'];
				$video_resolution = $gallery_item['video_resolution'];
				$video_poster     = $gallery_item['video_poster'];

				$default_player = $gallery_item['default_youtube_player'];
				$autoplay       = $gallery_item['auto_play'];
				$loop           = $gallery_item['loop'];


				// Video atts
				$atts = array();

				if ( ! empty( $video_resolution ) ) {
					$atts = array_merge( $atts, kalium_extract_aspect_ratio( $video_resolution ) );
				}

				if ( ! empty( $video_poster['url'] ) ) {
					$atts['poster'] = $video_poster['url'];
				}

				if ( $autoplay ) {
					$atts['autoplay'] = true;
				}

				if ( $loop ) {
					$atts['loop'] = true;
				}

				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="<?php echo $item_animation_classes; ?>">

                        <div class="portfolio-video">

							<?php
							/**
							 * Display Youtube video
							 */
							if ( $default_player ) {
								echo kalium()->media->embed_youtube( $video_url, $atts );
							} else {
								echo kalium()->media->parse_media( $video_url, $atts );
							}
							?>

                        </div>

                    </div>

                </div>
			<?php

			endif;
			// End: YouTube Video


			// Vimeo Video
			if ( 'vimeo_video' == $gallery_item['acf_fc_layout'] ) :

				$video_url = $gallery_item['video_url'];
				$video_resolution = $gallery_item['video_resolution'];

				$autoplay = $gallery_item['auto_play'];
				$loop     = $gallery_item['loop'];

				$atts = array();

				if ( ! empty( $video_resolution ) ) {
					$atts = array_merge( $atts, kalium_extract_aspect_ratio( $video_resolution ) );
				}

				if ( $autoplay ) {
					$atts['autoplay'] = true;
				}

				if ( $loop ) {
					$atts['loop'] = true;
				}

				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="<?php echo $item_animation_classes; ?>">

                        <div class="portfolio-video">

							<?php
							/**
							 * Display Vimeo video
							 */
							echo kalium()->media->embed_vimeo( $video_url, $atts );
							?>

                        </div>

                    </div>

                </div>
			<?php

			endif;
			// End: Vimeo Video


			// Self-Hosted Video
			if ( 'selfhosted_video' == $gallery_item['acf_fc_layout'] ) :

				$video_file = $gallery_item['video_file'];
				$video_resolution = $gallery_item['video_resolution'];
				$video_poster     = $gallery_item['video_poster'];

				$video_src = $video_file['url'];

				$autoplay = $gallery_item['auto_play'];
				$loop     = $gallery_item['loop'];

				// Video Resolution
				if ( ! preg_match( '/^[0-9]+:[0-9]+$/', $video_resolution ) ) {
					$video_resolution = '16:9';
				}

				$video_resolution = kalium_extract_aspect_ratio( $video_resolution );

				// Video atts
				$atts = $video_resolution;

				if ( ! empty( $video_poster['url'] ) ) {
					$atts['poster'] = $video_poster['url'];
				}

				if ( $autoplay ) {
					$atts['autoplay'] = true;
				}

				if ( $loop ) {
					$atts['loop'] = true;
				}
				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>

                    <div class="<?php echo $item_animation_classes; ?>">

                        <div class="portfolio-video">

							<?php
							/**
							 * Display self-hosted video
							 */
							echo kalium()->media->parse_media( $video_src, $atts );
							?>

                        </div>

                    </div>

                </div>
			<?php

			endif;
			// End: Self-Hosted Video


			// Text Quote
			if ( 'text_quote' == $gallery_item['acf_fc_layout'] ) :

				$quote_text = $gallery_item['quote_text'];
				$quote_author     = $gallery_item['quote_author'];
				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>
                    <blockquote>
						<?php echo do_shortcode( $quote_text ); ?>

						<?php if ( $quote_author ) : ?>
                            <span>- <?php echo wp_kses_post( $quote_author ); ?></span>
						<?php endif; ?>
                    </blockquote>
                </div>
			<?php

			endif;
			// End: Text Quote

			// HTML
			if ( $gallery_item['acf_fc_layout'] == 'html' ) :
				?>
                <div <?php kalium_class_attr( $column_classes ); ?>>
                    <div class="post-formatting">
						<?php echo $gallery_item['content']; ?>
                    </div>
                </div>
			<?php
			endif;
			// End: HTML

		endforeach;
		?>
    </div>

</div>