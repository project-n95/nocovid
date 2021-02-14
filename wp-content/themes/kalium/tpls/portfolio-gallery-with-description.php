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

$img_presentation_role = array(
	'role' => 'presentation'
);

?>
<div class="gallery gallery-type-description<?php
when_match( $image_spacing == 'nospacing', 'no-spacing' );
when_match( $full_width_gallery, 'full-width-container' );
when_match( 'below' == $image_captions_position, 'captions-below' );
when_match( 'hide' == $image_captions_position, 'captions-hide' );
?>">
	<?php
	foreach ( $gallery_items as $i => $gallery_item ) :

		$main_thumbnail_size = 1;

		// General Vars
		$description           = $gallery_item['description'];
		$description_width     = $gallery_item['description_width'];
		$description_alignment = $gallery_item['description_alignment'];

		$main_thumbnail_size = 'portfolio-single-img-';
		$thumb_size          = 2;


		// Column Classes
		$row_classes       = array( 'row' );
		$description_class = array( 'col-sm-5' );
		$image_class       = array( 'col-sm-7', 'zz-type-' . $gallery_item['acf_fc_layout'] );

		switch ( $description_width ) {
			case "4-12":
				$description_class = array( 'col-sm-4' );
				$image_class       = array( 'col-sm-8' );
				$thumb_size        = 1;
				break;

			case "6-12":
				$description_class = array( 'col-sm-6' );
				$image_class       = array( 'col-sm-6' );
				break;
		}

		$image_class[] = 'nivo';
		$image_class[] = 'media-type-' . $gallery_item['acf_fc_layout'];


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

		$row_classes[] = $item_animation_classes;

		// Description Alignment
		if ( $description_alignment == 'right' ) {
			$description_class[] = 'pull-right-md';
		}

		$main_thumbnail_size .= $thumb_size;
		$main_thumbnail_size = apply_filters( 'kalium_single_portfolio_gallery_image', $main_thumbnail_size );

		// Row-Start
		?>
        <div class="<?php echo implode( ' ', $row_classes ); ?>">
			<?php

			// Image Type
			if ( $gallery_item['acf_fc_layout'] == 'image' ) :

				$img = $gallery_item['image'];
				$caption = nl2br( make_clickable( $img['caption'] ) );
				$alt_text = $img['alt'];
				$href = $img['url'];

				if ( ! $img['id'] ) {
					continue;
				}

				$is_video = $alt_text && preg_match( "/(youtube\.com|vimeo\.com)/i", $alt_text );

				?>
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php
					when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}";
					?>">
                        <div class="post-formatting">
							<?php echo $description; // Escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">

                    <div class="photo">
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
			if ( $gallery_item['acf_fc_layout'] == 'images_slider' ) :

				$gallery_images = $gallery_item['images'];
				$auto_switch = $gallery_item['auto_switch'];

				if ( ! is_array( $gallery_images ) || ! $gallery_images ) {
					continue;
				}

				kalium_enqueue_slick_slider_library();

				?>
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php
					when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}";
					?>">
                        <div class="post-formatting">
							<?php echo $description; // Escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">
                    <div class="portfolio-images-slider <?php echo $item_animation_classes; ?>" data-autoswitch="<?php echo esc_attr( $auto_switch ); ?>">
						<?php
						foreach ( $gallery_images as $j => $image ) :

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
			if ( $gallery_item['acf_fc_layout'] == 'comparison_images' ) :

				$image_1 = $gallery_item['image_1'];
				$image_2 = $gallery_item['image_2'];

				$image_1_label = $image_1['title'];
				$image_2_label = $image_2['title'];

				$image_1_attachment = wp_get_attachment_image_src( $image_1['id'], $main_thumbnail_size );
				$image_1_id         = laborator_generate_as_element( array(
					$image_1_attachment[1],
					$image_1_attachment[2]
				) );


				?>
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php
					when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}";
					?>">
                        <div class="post-formatting">
							<?php echo $description; // Escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">

                    <figure class="comparison-image-slider <?php echo esc_attr( $image_1_id ); ?>">

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
			<?php

			endif;
			// End: Comparison Images


			// YouTube Video
			if ( $gallery_item['acf_fc_layout'] == 'youtube_video' ) :

				$video_url = $gallery_item['video_url'];
				$video_resolution = $gallery_item['video_resolution'];
				$video_poster = $gallery_item['video_poster'];

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
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}"; ?>">
                        <div class="post-formatting">
							<?php echo $description; // escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">

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
			if ( $gallery_item['acf_fc_layout'] == 'vimeo_video' ) :

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
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}"; ?>">
                        <div class="post-formatting">
							<?php echo $description; // escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">

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
			if ( $gallery_item['acf_fc_layout'] == 'selfhosted_video' ) :

				$video_file = $gallery_item['video_file'];
				$video_resolution = $gallery_item['video_resolution'];
				$video_poster = $gallery_item['video_poster'];

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
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}"; ?>">

                        <div class="post-formatting">
							<?php echo $description; // escaped by ACF plugin
							?>
                        </div>

                    </div>

                    <div class="lgrad"></div>

                </div>

                <div class="<?php echo implode( ' ', $image_class ); ?>">

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


			// HTML
			if ( $gallery_item['acf_fc_layout'] == 'html' ) :

				?>
                <div class="<?php echo implode( ' ', $description_class ); ?>">

                    <div class="gallery-item-description hidden<?php
					when_match( $i == 0, 'first-entry' );
					echo " description-{$description_alignment}";
					?>">
                        <div class="post-formatting">
							<?php echo $description; // Escaped by ACF plugin
							?>
                        </div>
                    </div>
                    <div class="lgrad"></div>

                </div>
                <div class="<?php echo implode( ' ', $image_class ); ?>">

                    <div class="post-formatting">
						<?php echo $gallery_item['content']; ?>
                    </div>
                </div>
			<?php

			endif;
			// End: HTML

			?>
        </div>
	<?php

	endforeach;
	?>
</div>