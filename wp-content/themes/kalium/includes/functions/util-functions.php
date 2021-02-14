<?php
/**
 * Kalium WordPress Theme
 *
 * Util functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Filter terms array and remove duplicates.
 *
 * @param array $terms
 *
 * @return array
 */
function kalium_unique_terms( $terms ) {
	if ( ! is_array( $terms ) ) {
		return $terms;
	}

	$terms_unique = [];

	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			if ( ! isset( $terms_unique[ $term->term_id ] ) ) {
				$terms_unique[ $term->term_id ] = $term;
			}
		}
	}

	return array_values( $terms_unique );
}

/**
 * Convert an english word to number.
 *
 * @param string|int $word
 *
 * @return int
 */
function kalium_get_number_from_word( $word ) {
	if ( is_numeric( $word ) ) {
		return $word;
	}

	switch ( $word ) {
		case 'ten':
			return 10;
			break;
		case 'nine':
			return 9;
			break;
		case 'eight':
			return 8;
			break;
		case 'seven':
			return 7;
			break;
		case 'six':
			return 6;
			break;
		case 'five':
			return 5;
			break;
		case 'four':
			return 4;
			break;
		case 'three':
			return 3;
			break;
		case 'two':
			return 2;
			break;
		case 'one':
			return 1;
			break;
	}

	return 0;
}

/**
 * Clean post excerpt.
 *
 * @param string $content
 * @param bool   $strip_tags
 *
 * @return string
 */
function kalium_clean_excerpt( $content, $strip_tags = false ) {
	$content = strip_shortcodes( $content );
	$content = preg_replace( '#<style.*?>(.*?)</style>#i', '', $content );
	$content = preg_replace( '#<script.*?>(.*?)</script>#i', '', $content );

	return $strip_tags ? strip_tags( $content ) : $content;
}

/**
 * Format color value.
 *
 * @param string $color
 *
 * @return string
 */
function kalium_format_color_value( $color ) {
	$color_formatted = '#';

	if ( preg_match( '#\#?([a-f0-9]+)#', $color, $matches ) ) {
		$color     = strtolower( $matches[1] );
		$color_len = strlen( $color );

		if ( 3 == $color_len || 6 == $color_len ) {
			$color_formatted .= $color;
		} else if ( $color_len < 6 ) {
			$last            = substr( $color, - 1, 1 );
			$color_formatted .= $color . str_repeat( $last, 6 - $color_len );
		} else if ( $color_len > 6 ) {
			$color_formatted .= substr( $color, 0, 6 );
		}
	} else {
		$color_formatted .= 'ffffff';
	}

	return $color_formatted;
}

/**
 * Get Post Ids from WP_Query.
 *
 * @param WP_Query $query
 *
 * @return array
 */
function kalium_get_post_ids_from_query( $query ) {
	$ids = [];

	foreach ( $query->posts as $post ) {
		if ( is_object( $post ) ) {
			$ids[] = $post->ID;
		} else if ( is_numeric( $post ) ) {
			$ids[] = $post;
		}
	}

	return $ids;
}

/**
 * Get enabled options (SMOF Theme Options array).
 *
 * @param array $items
 *
 * @return array
 */
function kalium_get_enabled_options( $items ) {
	$enabled = [];

	if ( isset( $items['visible'] ) ) {
		foreach ( $items['visible'] as $item_id => $item ) {
			if ( $item_id == 'placebo' ) {
				continue;
			}

			$enabled[ $item_id ] = $item;
		}
	}

	return $enabled;
}

/**
 * Extract aspect ratio from string.
 *
 * @param string $str
 *
 * @return array
 */
function kalium_extract_aspect_ratio( $str = '' ) {
	$ratio = [];

	if ( ! empty( $str ) && preg_match( '/^(?<w>[0-9]+)(:|x)(?<h>[0-9]+)$/', trim( $str ), $matches ) ) {
		$ratio = [
			'width'  => $matches['w'],
			'height' => $matches['h']
		];
	}

	return $ratio;
}

/**
 * Wrap image with image placeholder element.
 *
 * @param string $image
 *
 * @return string
 */
function kalium_image_placeholder_wrap_element( $image ) {
	if ( false !== strpos( $image, '<img' ) ) {
		return kalium()->images->get_image( $image );
	}

	return $image;
}

/**
 * Default Value Set for Visual Composer Loop Parameter Type.
 *
 * @param string $query
 * @param string $field
 * @param string $value
 *
 * @return string
 */
function kalium_vc_loop_param_set_default_value( &$query, $field, $value = '' ) {
	if ( ! preg_match( '/(\|?)' . preg_quote( $field ) . ':/', $query ) ) {
		$query .= "|{$field}:{$value}";
	}

	return ltrim( '|', $query );
}

/**
 * Compress text function.
 *
 * @param string $buffer
 *
 * @return string|
 */
function kalium_compress_text( $buffer ) {

	/* remove comments */
	$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );

	/* remove tabs, spaces, newlines, etc. */
	$buffer = str_replace( [ "\r\n", "\r", "\n", "\t", '	', '	', '	' ], '', $buffer );

	return $buffer;
}
