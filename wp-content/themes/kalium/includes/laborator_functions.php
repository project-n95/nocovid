<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Print attribute values based on boolean value
 */
function when_match( $bool, $str = '', $otherwise_str = '', $echo = true ) {
	$str = trim( $bool ? $str : $otherwise_str );

	if ( $str ) {
		$str = ' ' . $str;

		if ( $echo ) {
			echo $str;

			return '';
		}
	}

	return $str;
}

/**
 * Aspect ratio element generator.
 *
 * @param array $size
 *
 * @return string|null
 */
function laborator_generate_as_element( $size ) {
	global $as_element_id;

	if ( ! isset( $as_element_id ) ) {
		$as_element_id = 1;
	}

	if ( isset( $size['width'] ) ) {
		$size[0] = $size['width'];
	}

	if ( isset( $size['height'] ) ) {
		$size[1] = $size['height'];
	}

	if ( $size[0] == 0 ) {
		return null;
	}

	$element_id  = "arel-" . $as_element_id;
	$element_css = 'padding-bottom: ' . kalium()->images->calculate_aspect_ratio( $size[0], $size[1] ) . '% !important;';

	$as_element_id ++;

	if ( defined( 'DOING_AJAX' ) ) {
		$element_id .= '-' . time() . mt_rand( 100, 999 );
	}

	kalium_append_custom_css( ".{$element_id}", $element_css );

	return $element_id;
}

/**
 * Get User IP.
 *
 * @return string
 */
function get_the_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

/**
 * Less Generator
 */
function kalium_generate_less_style( $files = [], $vars = [] ) {
	try {
		if ( ! class_exists( 'Less_Parser' ) ) {
			include_once kalium()->locate_file( 'includes/libraries/lessphp/Less.php' );
		}

		// Compile Less
		$less_options = array(
			'compress' => true
		);

		$css = '';

		$less = new Less_Parser( $less_options );

		foreach ( $files as $file => $type ) {
			if ( $type == 'parse' ) {
				$css_contents = file_get_contents( $file );

				// Replace Vars
				foreach ( $vars as $var => $value ) {
					if ( trim( $value ) ) {
						$css_contents = preg_replace( "/(@{$var}):\s*.*?;/", '$1: ' . $value . ';', $css_contents );
					}
				}

				$less->parse( $css_contents );
			} else {
				$less->parseFile( $file );
			}
		}

		$css = $less->getCss();
	} catch ( Exception $e ) {
	}

	return $css;
}

/**
 * Get Available Terms for current WP_Query object
 *
 * @param array  $args
 * @param string $taxonomy
 * @param bool   $ignore_paged_var
 *
 * @return array|WP_Error
 */
function laborator_get_available_terms_for_query( $args, $taxonomy = 'category', $ignore_paged_var = true ) {

	// Remove pagination argument
	if ( $ignore_paged_var ) {
		unset( $args['paged'] );
	}

	$posts = new WP_Query( array_merge( $args, [
		'fields'         => 'ids',
		'posts_per_page' => - 1
	] ) );

	$post_ids = $posts->posts;
	$term_ids = []; // Terms IDs Array

	$object_terms = wp_get_object_terms( $post_ids, $taxonomy );

	// In case when taxonomy doesn't exists
	if ( is_wp_error( $object_terms ) ) {
		return [];
	}

	if ( ! empty( $object_terms ) ) {
		foreach ( $object_terms as $term ) {
			$term_ids[] = $term->term_id;
		}
	}

	// Unique terms only
	$already_added_ids = [];

	foreach ( $object_terms as $i => $term ) {
		$term_id = $term->term_id;

		if ( in_array( $term_id, $already_added_ids ) ) {
			unset( $object_terms[ $i ] );
			continue;
		}

		$already_added_ids[] = $term_id;
	}

	// Order Terms
	if ( is_array( $object_terms ) && isset( $object_terms[0] ) && $object_terms[0] instanceof WP_Term && isset( $object_terms[0]->term_order ) ) {
		uasort( $object_terms, 'kalium_sort_terms_taxonomy_order_fn' );
	}

	// Fix Missing Parent Categories
	foreach ( $object_terms as & $term ) {
		if ( ! in_array( $term->parent, $term_ids ) ) {
			$term->parent = 0;
		}
	}

	return $object_terms;
}

/**
 * Sort terms by term order function.
 *
 * @param WP_Term $a
 * @param WP_Term $b
 *
 * @return int
 */
function kalium_sort_terms_taxonomy_order_fn( $a, $b ) {
	return $a->term_order > $b->term_order ? 1 : - 1;
}

/**
 * Append content to the footer.
 *
 * @param string $str
 */
function kalium_append_content_to_footer( $str ) {
	global $kalium_append_footer_html;

	if ( ! isset( $kalium_append_footer_html ) ) {
		$kalium_append_footer_html = [];
	}

	if ( defined( 'DOING_AJAX' ) ) {
		echo $str;
	} else {
		$kalium_append_footer_html[] = $str;
	}
}

/**
 * Get Custom Skin File Name
 */
function kalium_get_custom_skin_filename() {
	if ( is_multisite() ) {
		return apply_filters( 'kalium_multisite_custom_skin_name', 'custom-skin-' . get_current_blog_id() . '.css', get_current_blog_id() );
	}

	return apply_filters( 'kalium_custom_skin_name', 'custom-skin.css' );
}

/**
 * Get custom skin relative (or absolute) file path
 *
 * @param $absolute (default: false) - If set to true, absolute path will be returned
 *
 * @return string - File path (either relative or absolute)
 */
function kalium_get_custom_skin_file_path( $absolute = false ) {
	$custom_skin_filename = kalium_get_custom_skin_filename();
	$relative_theme_path  = ltrim( substr( get_stylesheet_directory(), strlen( WP_CONTENT_DIR ) ), DIRECTORY_SEPARATOR );

	// Skin path
	$custom_skin_filepath = $relative_theme_path . '/assets/css/' . $custom_skin_filename;

	// Child theme skin path
	if ( is_child_theme() ) {
		$custom_skin_filepath = $relative_theme_path . '/' . $custom_skin_filename;
	}

	// Absolute path
	if ( $absolute ) {
		$custom_skin_filepath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $custom_skin_filepath;
	}

	return apply_filters( 'kalium_custom_skin_path', $custom_skin_filepath );
}

/**
 * Get custom skin url.
 */
function kalium_get_custom_skin_file_url() {
	$custom_skin_filename = kalium_get_custom_skin_filename();
	$custom_skin_url      = get_stylesheet_directory_uri() . '/assets/css/' . $custom_skin_filename;

	if ( is_child_theme() ) {
		$custom_skin_url = get_stylesheet_directory_uri() . '/' . $custom_skin_filename;
	}

	return apply_filters( 'kalium_custom_skin_url', $custom_skin_url );
}

/**
 * Maybe generate custom skin file.
 */
function kalium_use_filebased_custom_skin_maybe_generate() {
	$custom_skin_path_full = kalium_get_custom_skin_file_path( true );

	// Create skin file in case it does not exists
	if ( file_exists( $custom_skin_path_full ) === false ) {
		@touch( $custom_skin_path_full );
	}

	if ( is_writable( $custom_skin_path_full ) === true ) {

		if ( ! trim( @file_get_contents( $custom_skin_path_full ) ) ) {
			return laborator_custom_skin_generate( null, true );
		}

		return true;
	}

	return false;
}

/**
 * Enqueue custom skin file.
 */
function kalium_use_filebased_custom_skin_enqueue() {

	// Generate Skin Hash (Prevent Cache Issues)
	$skin_colors_vars = [
		'custom_skin_bg_color',
		'custom_skin_link_color',
		'custom_skin_link_color',
		'custom_skin_headings_color',
		'custom_skin_paragraph_color',
		'custom_skin_footer_bg_color',
		'custom_skin_borders_color',
	];
	$skin_colors_hash = '';

	foreach ( $skin_colors_vars as $var ) {
		$skin_colors_hash .= kalium_get_theme_option( $var );
	}

	$skin_colors_hash = md5( kalium()->get_version() . $skin_colors_hash );

	if ( defined( 'KALIUM_VERSION_DEBUG' ) ) {
		$skin_colors_hash = md5( $skin_colors_hash . time() );
	}

	// Enqueue skin
	wp_enqueue_style( 'custom-skin', kalium_get_custom_skin_file_url(), null, $skin_colors_hash );
}

/**
 * Generate Custom Skin File.
 */
function kalium_generate_custom_skin_file() {
	$custom_skin_filename = kalium_get_custom_skin_filename();
	$custom_skin_path     = get_stylesheet_directory() . '/assets/css/' . $custom_skin_filename;

	if ( is_child_theme() ) {
		$custom_skin_path = get_stylesheet_directory() . '/' . $custom_skin_filename;
	}

	if ( is_writable( $custom_skin_path ) ) {
		$kalium_skin_custom_css = get_option( 'kalium_skin_custom_css' );

		$fp = @fopen( $custom_skin_path, 'w' );
		@fwrite( $fp, $kalium_skin_custom_css );
		@fclose( $fp );

		return true;
	}

	return false;
}

/**
 * Get Post Likes
 */
function get_post_likes( $post_id = null ) {
	global $post;

	$user_ip  = get_the_user_ip();
	$the_post = $post_id ? get_post( $post_id ) : $post;
	$likes    = $the_post->post_likes;

	if ( ! is_array( $likes ) ) {
		$likes = array();
	}

	$output = array(
		'liked' => in_array( $user_ip, $likes ),
		'count' => count( $likes )
	);

	return $output;
}

/**
 * Get attachment sizes and srcset
 */
function kalium_image_get_srcset_and_sizes_from_attachment( $attachment_id, $image = null, $image_size = 'original' ) {
	$srcset = $sizes = [];

	if ( $image != false ) {
		$size_array     = [ absint( $image[1] ), absint( $image[2] ) ];
		$image_metadata = wp_get_attachment_metadata( $attachment_id );

		$srcset = wp_calculate_image_srcset( $size_array, $image[0], $image_metadata, $attachment_id );
		$sizes  = wp_calculate_image_sizes( $size_array, $image[0], $image_metadata, $attachment_id );
	}

	return [ $srcset, $sizes ];
}
