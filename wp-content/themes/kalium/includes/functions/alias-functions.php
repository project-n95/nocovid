<?php
/**
 * Kalium WordPress Theme
 *
 * Alias functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Get Theme Options data entry.
 *
 * @param string $option_name
 * @param string $default_value
 *
 * @return mixed
 *
 * @deprecated 3.0
 * @see        kalium_get_theme_option()
 */
function get_data( $option_name = null, $default_value = '' ) {
	return kalium_get_theme_option( $option_name, $default_value );
}

/**
 * Get element from array by key (fail safe).
 *
 * @param array  $arr
 * @param string $key
 *
 * @return mixed|null
 *
 * @deprecated 3.0
 * @see        kalium_get_array_key()
 */
function get_array_key( $arr, $key, $default_value = null ) {
	return kalium_get_array_key( $arr, $key, $default_value );
}

/**
 * Custom style generator.
 *
 * @param string $selector
 * @param string $props
 * @param string $media
 * @param bool   $footer
 *
 * @return void
 *
 * @deprecated 3.0
 * @see        kalium_append_custom_css()
 */
function generate_custom_style( $selector, $props = '', $media = '', $footer = false ) {
	kalium_append_custom_css( $selector, $props, $media, $footer );
}

/**
 * Get SVG file contents from theme directory.
 *
 * @param string $svg_path
 * @param string $id
 * @param int[]  $size
 * @param bool   $is_asset
 *
 * @return string
 *
 * @deprecated 3.0
 * @see        kalium_get_svg_file()
 */
function laborator_get_svg( $svg_path, $id = null, $size = [ 24, 24 ], $is_asset = true ) {
	$file_path = $is_asset ? "assets/{$svg_path}" : $svg_path;

	return kalium_get_svg_file( $file_path, $id, $size );
}

/**
 * Share network story.
 *
 * @param string $network
 * @param null   $post_id
 * @param string $class
 * @param bool   $icon
 *
 * @deprecated 3.0.9
 * @see        kalium_social_network_share_post_link()
 */
function share_story_network_link( $network, $post_id = null, $class = '', $icon = false ) {
	kalium_social_network_share_post_link( $network, $post_id, [
		'icon_only' => $icon,
		'class'     => $class,
	] );
}
