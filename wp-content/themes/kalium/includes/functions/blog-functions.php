<?php
/**
 * Kalium WordPress Theme
 *
 * Blog core functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Get blog option value.
 *
 * @param string $option_name
 *
 * @return mixed|WP_Error|null
 */
function kalium_blog_get_option( $option_name ) {
	global $blog_options;

	// If blog options are not initialized
	if ( empty( $blog_options ) ) {
		_kalium_blog_initialize_options();
	}

	// Get option
	$option_path = explode( '/', $option_name );

	$option_value = null;

	while ( $key = array_shift( $option_path ) ) {

		if ( is_null( $option_value ) && isset( $blog_options[ $key ] ) ) {
			$option_value = $blog_options[ $key ];
		} elseif ( isset( $option_value[ $key ] ) ) {
			$option_value = $option_value[ $key ];
		} else {
			return new WP_Error( 'kalium_blog_error', sprintf( "Blog option <strong>%s</strong> doesn't exists!", $option_name ) );
		}
	}

	return $option_value;
}

/**
 * Get current blog posts template.
 *
 * @return string
 */
function kalium_blog_get_template() {
	return kalium_blog_get_option( 'blog_template' );
}

/**
 * Get current instance ID of blog.
 *
 * @return string
 */
function kalium_blog_instance_id() {
	return kalium_blog_get_option( 'id' );
}

/**
 * Check if its inside Kalium blog loop.
 *
 * @return bool
 */
function kalium_blog_is_in_the_loop() {
	global $blog_options;

	return in_the_loop() && ! is_single() && ! empty( $blog_options );
}

/**
 * Get post featured image link or return post image link instead.
 *
 * @param WP_Post $post
 *
 * @return bool|false|string|WP_Error
 */
function kalium_blog_post_image_link( $post ) {
	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail_url( $post, 'original' );
	} else if ( is_object( $post ) && 'attachment' == $post->post_type ) {
		return $post->guid;
	}

	return get_permalink( $post );
}

/**
 * Extract post format content.
 *
 * @param WP_Post $post
 *
 * @return array|null
 */
function kalium_extract_post_format_content( $post = null ) {
	$result = null;

	if ( ! $post ) {
		$post = get_post();
	}

	if ( $post instanceof WP_Post ) {
		$post_content_plain = $post->post_content;

		// Thumbnail size to use
		if ( is_single() ) {
			$thumbnail_size = kalium_blog_get_option( 'single/post_image/size' );
		} else {
			$thumbnail_size = kalium_blog_get_option( 'loop/post_thumbnail/size' );
		}

		// Extract post format
		$post_format = get_post_format( $post );

		switch ( $post_format ) {

			// Image post format
			case 'image':

				// Find image within tag
				if ( preg_match( "/(<a.*?href=(\"|')(?<href>.*?)(\"|').*?>)?<img.*?\s+src=(\"|')(?<image>.*?)(\"|').*?>(<\/a>)?/", $post_content_plain, $matches ) ) {
					$href      = $matches['href'];
					$image_url = $matches['image'];

					// Use href if its image type
					if ( $href && preg_match( '/\.(png|jpe?g|gif)$/i', $href ) ) {
						$image_url = $href;
					}

					$result = [
						'type'    => $post_format,
						'content' => $matches[0],
						'media'   => $image_url
					];
				} // Find image urls
				else if ( $urls = wp_extract_urls( $post_content_plain ) ) {
					$image_url = '';
					$urls      = array_reverse( $urls );

					while ( ! $image_url && ( $url = array_pop( $urls ) ) ) {
						if ( preg_match( '#\.(jpe?g|gif|png)$#i', $url ) ) {
							$image_url = $url;
						}
					}

					if ( $image_url ) {
						$result = [
							'type'    => $post_format,
							'content' => $image_url,
							'media'   => $image_url,
						];
					}
				}
				break;

			// Gallery post format
			case 'gallery':
				$gallery_images = kalium_get_field( 'post_slider_images', $post->ID );

				// Assign featured image as well
				if ( has_post_thumbnail( $post ) ) {
					$featured_image = [
						'id' => get_post_thumbnail_id( $post ),
					];

					if ( ! is_array( $gallery_images ) ) {
						$gallery_images = [];
					}

					if ( apply_filters( 'kalium_blog_post_gallery_format_include_featured_image', true ) ) {
						array_unshift( $gallery_images, $featured_image );
					}
				}

				// Only when has gallery items
				if ( ! empty( $gallery_images ) ) {
					$gallery_html = '';

					foreach ( $gallery_images as $gallery_image ) {
						if ( ! empty( $gallery_image['id'] ) ) {
							$image      = kalium_get_attachment_image( $gallery_image['id'], $thumbnail_size );
							$image_link = is_single() ? kalium_blog_post_image_link( get_post( $gallery_image['id'] ) ) : get_permalink( $post );

							$gallery_html .= sprintf( '<li><a href="%s">%s</a></li>', $image_link, $image );
						}
					}

					// Gallery has items
					if ( $gallery_html ) {
						$gallery_autoswitch_image = kalium_blog_get_option( 'single/gallery_autoswitch_image' );

						if ( is_single() && $gallery_autoswitch_image > 0 ) {
							$gallery_html = sprintf( '<ul class="%s" data-autoswitch="%d">%s</ul>', 'post-gallery-images', $gallery_autoswitch_image, $gallery_html );
						} else {
							$gallery_html = sprintf( '<ul class="%s">%s</ul>', 'post-gallery-images', $gallery_html );
						}

						$result = [
							'type'    => $post_format,
							'content' => '',
							'media'   => $gallery_html
						];
					}
				}
				break;

			// Audio
			case 'video':
			case 'audio':
				if ( 'audio' === $post_format ) {
					$autoplay = is_single() ? kalium_get_field( 'auto_play_audio', $post->ID ) : false;
				} else {
					$autoplay   = is_single() ? kalium_get_field( 'auto_play_video', $post->ID ) : false;
					$resolution = kalium_get_field( 'video_resolution', $post->ID );
				}

				// Media attributes
				$media_atts = [];

				// Poster
				if ( apply_filters( 'kalium_blog_media_use_featured_image_poster', true ) && has_post_thumbnail( $post ) && ( $featured_image = wp_get_attachment_image( get_post_thumbnail_id( $post ), $thumbnail_size ) ) ) {
					$featured_image_arr  = kalium()->helpers->parse_attributes( $featured_image );
					$featured_image_atts = $featured_image_arr['attributes'];

					$media_atts['poster'] = $featured_image_atts['src'];
					$media_atts['width']  = $featured_image_atts['width'];
					$media_atts['height'] = $featured_image_atts['height'];
				}

				// Autoplay
				if ( $autoplay ) {
					$media_atts['autoplay'] = 'autoplay';
				}

				// Video resolution
				if ( ! empty( $resolution ) ) {
					$resolution = kalium_extract_aspect_ratio( $resolution );
					$media_atts = array_merge( $media_atts, $resolution );
				}

				// Media element
				$media = kalium()->media->parse_media( $post_content_plain, $media_atts );

				if ( $media ) {
					$result = [
						'type'    => $post_format,
						'content' => $media,
						'media'   => $media,
					];
				}
				break;

			// Quotes
			case 'quote':
				if ( preg_match( "/^\s*<blockquote.*?>(?<quote>.*?)(?<cite><cite>(?<author>.*?)<\/cite>)?<\/blockquote>/s", $post_content_plain, $matches ) ) {
					$content = $matches[0];
					$quote   = $matches['quote'];
					$author  = kalium_get_array_key( $matches, 'author' );

					$result = [
						'type'    => $post_format,
						'content' => $content,
						'quote'   => $quote,
						'author'  => $author,
					];
				}
				break;
		}

		// Generate media
		if ( is_array( $result ) ) {

			// Generate image placeholder
			if ( 'image' === $result['type'] ) {
				$result['generated'] = sprintf(
					'<a href="%1$s" target="%2$s" rel="bookmark">%3$s</a>',
					esc_url( get_permalink( $post ) ),
					esc_attr( kalium_blog_post_link_target() ),
					kalium_get_attachment_image( $result['media'] )
				);
			}
		}
	}

	return $result;
}

/**
 * Show post content format media.
 *
 * @param array $result
 * @param bool  $return
 *
 * @return string|void
 */
function kalium_show_post_format_content( $result, $return = false ) {
	$html = '';

	// Check if its valid result from extracted post format content
	if ( is_array( $result ) && isset( $result['type'] ) ) {

		switch ( $result['type'] ) {
			// Image
			case 'image' :
				$html = $result['generated'];
				break;

			// Gallery
			case 'gallery' :
				$html = $result['media'];

				// This requires slick slider gallery
				kalium_enqueue_flickity_library();
				break;

			// Video + audio
			case 'video' :
			case 'audio' :
				$html = $result['media'];
				break;

			// Quote
			case 'quote' :
				$quote  = $result['quote'];
				$author = $result['author'];

				if ( $author ) {
					$quote .= "<cite>{$author}</cite>";
				}

				$html = sprintf( '<div class="post-quote"><blockquote>%s</blockquote></div>', $quote );

				break;
		}

	}

	if ( $return ) {
		return $html;
	}

	echo $html;
}

/**
 * Archive page container class.
 *
 * @param array $class
 */
function kalium_blog_container_class( $class = [] ) {
	$classes = [
		'blog',
	];

	// Blog template
	$classes[] = sprintf( 'blog--%s', kalium_blog_get_template() );

	// Extra classes
	if ( ! empty( $class ) && is_array( $class ) ) {
		$classes = array_merge( $classes, $class );
	}

	$classes = apply_filters( 'kalium_blog_container_class', $classes );

	// Class attribute
	kalium_class_attr( $classes );
}

/**
 * Single post container class.
 *
 * @param array $class
 */
function kalium_blog_single_container_class( $class = [] ) {
	$classes = [
		'single-post',
	];

	// Extra classes
	if ( ! empty( $class ) && is_array( $class ) ) {
		$classes = array_merge( $classes, $class );
	}

	$classes = apply_filters( 'kalium_blog_single_container_class', $classes );

	kalium_class_attr( $classes );
}

/**
 * Checks if given post is external post.
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function kalium_blog_is_external_url_post( $post ) {
	if ( $post instanceof WP_Post && 'link' === get_post_format( $post ) ) {
		$links = wp_extract_urls( get_the_content( null, false, $post ) );

		return ! empty( $links );
	}

	return false;
}

/**
 * Returns post link target.
 *
 * @retun string
 *
 * @param WP_Post $post
 *
 * @return mixed|string
 */
function kalium_blog_post_link_target( $post = null ) {
	$_post       = get_post( $post );
	$link_target = '';

	// External post
	if ( $_post instanceof WP_Post && kalium_blog_is_external_url_post( $_post ) && preg_match( '#href=("|\')' . preg_quote( get_permalink( $_post ) ) . '("|\')[^>]+?target=("|\')(?<target>[a-z_]+)("|\')#i', get_the_content( null, false, $_post ), $matches ) ) {
		$link_target = $matches['target'];
	}

	return apply_filters( 'kalium_blog_post_link_target', $link_target );
}
