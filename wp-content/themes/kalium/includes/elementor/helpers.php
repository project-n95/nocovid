<?php

namespace Kalium\Elementor;

/**
 * Kalium WordPress Theme
 *
 * Kalium Elementor helpers class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Helpers {

	/**
	 * Get terms option.
	 *
	 * @param string $taxonomy
	 * @param array  $args
	 *
	 * @return array
	 */
	public static function get_terms_options( $taxonomy, $args = [] ) {
		$terms_options = [];

		// Args
		$args = wp_parse_args( $args, [
			'field'   => 'id',
			'default' => null,
		] );

		// Default value
		if ( ! empty( $args['default'] ) ) {
			if ( is_array( $args['default'] ) ) {
				$keys = array_keys( $args['default'] );

				$terms_options[ $keys[0] ] = $args['default'][ $keys[0] ];
			} else {
				$terms_options[''] = $args['default'];
			}
		}

		// Terms list
		$terms = get_terms( [
			'taxonomy' => $taxonomy,
		] );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$option_value = 'slug' === $args['field'] ? $term->slug : $term->term_id;

				$terms_options[ $option_value ] = $term->name;
			}
		}

		return $terms_options;
	}

	/**
	 * Get post type title.
	 *
	 * @param string $post_type
	 *
	 * @return string|null
	 */
	public static function get_post_type_title( $post_type ) {
		$post_type_obj = get_post_type_object( $post_type );

		if ( is_null( $post_type_obj ) ) {
			return null;
		}

		return $post_type_obj->labels->name;
	}

	/**
	 * Get posts list options.
	 *
	 * @param array $query_args
	 *
	 * @return array|null
	 */
	public static function get_posts( $query_args = [] ) {
		$query_args = wp_parse_args( $query_args, [
			'post_status' => 'publish',
			'posts_per_page' => -1,
		] );

		$query = new \WP_Query();
		$posts = $query->query( $query_args );

		if ( ! empty( $posts ) ) {
			return wp_list_pluck( $posts, 'post_title', 'ID' );
		}
	}

	/**
	 * Get authors list options.
	 *
	 * @return array|null
	 */
	public static function get_authors() {
		$users = get_users( [
			'who'                 => 'authors',
			'has_published_posts' => true,
			'fields'              => [
				'ID',
				'display_name',
			],
		] );

		if ( ! empty( $users ) ) {
			return wp_list_pluck( $users, 'display_name', 'ID' );
		}
	}
}