<?php

namespace Kalium\Elementor\Control_Sets;

use Elementor\Controls_Manager;
use \Kalium\Elementor\Helpers;

/**
 * Kalium WordPress Theme
 *
 * Custom Query control set.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Query_Posts {

	/**
	 * Add controls.
	 *
	 * @param \Elementor\Widget_Base $widget
	 * @param array                  $args
	 */
	public static function add_controls( $widget, $args = [] ) {

		// Args
		$args = self::parse_args( $args );

		// Args vars
		$setting_prefix     = $args['setting_prefix'] . '_';
		$post_types         = $args['post_type'];
		$post_types_options = $args['post_type_options'];
		$taxonomies         = $args['taxonomies'];

		// Posts list
		$posts_list = Helpers::get_posts( [
			'post_type' => $post_types,
		] );

		// Controls section start
		$widget->start_controls_section( $args['section_id'], [
			'label' => $args['section_label'],
		] );

		// Control: Post type
		$widget->add_control( $setting_prefix . 'post_type', [
			'label'   => 'Post Type',
			'type'    => Controls_Manager::SELECT,
			'options' => $post_types_options,
			'default' => kalium()->helpers->array_first( $post_types_options, true ),
		] );

		// Taxonomy query
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_id   = $taxonomy['taxonomy'];
			$taxonomy_name = $taxonomy['name'];
			$control_name  = "{$setting_prefix}tax_{$taxonomy_id}";
			$terms_options = [];
			$terms         = get_terms( [
				'taxonomy' => $taxonomy_id,
			] );

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$value = 'slug' === $args['term_field'] ? $term->slug : $term->term_id;

					$terms_options[ $value ] = $term->name;
				}
			}

			// Control: Taxonomy query
			$widget->add_control( $control_name, [
				'label'       => $taxonomy_name,
				'label_block' => true,
				'type'        => Controls_Manager::SELECT2,
				'options'     => $terms_options,
				'multiple'    => true,
				'condition'   => [ "{$setting_prefix}post_type" => $taxonomy['post_type'] ],
			] );
		}

		// Control: Author
		$widget->add_control( $setting_prefix . 'author', [
			'label'       => 'Author',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'options'     => Helpers::get_authors(),
			'multiple'    => true,
		] );

		// Control: Include
		$widget->add_control( $setting_prefix . 'include', [
			'label'       => 'Include',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'options'     => $posts_list,
			'multiple'    => true,
		] );

		// Control: Exclude
		$widget->add_control( $setting_prefix . 'exclude', [
			'label'       => 'Exclude',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT2,
			'options'     => $posts_list,
			'multiple'    => true,
		] );

		// Control: Posts per page
		$widget->add_control( $setting_prefix . 'posts_per_page', [
			'label'   => 'Posts Per Page',
			'type'    => Controls_Manager::NUMBER,
			'default' => $args['control_defaults']['posts_per_page'],
		] );

		// Control: Order by
		$widget->add_control( $setting_prefix . 'order_by', [
			'label'   => 'Order by',
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''              => 'Default',
				'ID'            => 'Post ID',
				'author'        => 'Post Author',
				'title'         => 'Title',
				'date'          => 'Date',
				'modified'      => 'Last Modified Date',
				'parent'        => 'Parent Id',
				'rand'          => 'Random',
				'comment_count' => 'Comment Count',
				'menu_order'    => 'Menu Order',
			],
		] );

		// Control: Sort order
		$widget->add_control( $setting_prefix . 'order', [
			'label'   => 'Sort Order',
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''     => 'Default',
				'asc'  => 'ASC',
				'desc' => 'DESC',
			],
		] );

		// Controls section end
		$widget->end_controls_section();
	}

	/**
	 * Get controls value grouped in array.
	 *
	 * @param \Elementor\Widget_Base $widget
	 * @param array                  $args
	 *
	 * @return array
	 */
	public static function get_value( $widget, $args = [] ) {

		// Vars
		$return   = [];
		$settings = $widget->get_settings_for_display();
		$args     = self::parse_args( $args );

		// Args vars
		$setting_prefix = $args['setting_prefix'] . '_';
		$post_types     = $args['post_type'];
		$taxonomies     = $args['taxonomies'];

		// Tax values
		$tax_values = [];

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy['post_type'], $post_types ) ) {
				$taxonomy_name = $taxonomy['taxonomy'];
				$taxonomy_var  = $setting_prefix . 'tax_' . $taxonomy_name;

				$tax_values[ $taxonomy_name ] = kalium_get_array_key( $settings, $taxonomy_var );
			}
		}

		/**
		 * Object value.
		 */

		// Post type
		$return['post_type'] = kalium_get_array_key( $settings, $setting_prefix . 'post_type' );

		// Tax values
		$return['tax'] = $tax_values;

		// Author
		$return['author'] = kalium_get_array_key( $settings, $setting_prefix . 'author' );

		// Include
		$return['include'] = kalium_get_array_key( $settings, $setting_prefix . 'include', [] );

		// Exclude
		$return['exclude'] = kalium_get_array_key( $settings, $setting_prefix . 'exclude', [] );

		// Posts per page
		$return['posts_per_page'] = kalium_get_array_key( $settings, $setting_prefix . 'posts_per_page', $args['control_defaults']['posts_per_page'] );

		// Order by
		$return['order_by'] = kalium_get_array_key( $settings, $setting_prefix . 'order_by' );

		// Sort order
		$return['order'] = kalium_get_array_key( $settings, $setting_prefix . 'order' );

		return $return;
	}

	/**
	 * Convert controls value to WP query args array.
	 *
	 * @param array $value
	 * @param array $args
	 *
	 * @return array
	 */
	public static function to_query_args( $value, $args = [] ) {

		// Query and args
		$query_args = [];
		$args       = wp_parse_args( $args, [
			'term_field' => 'id',
		] );

		// Term field
		$term_field = kalium_conditional( 'slug' === $args['term_field'], 'slug', 'id' );

		// Post type
		if ( ! empty( $value['post_type'] ) ) {
			$query_args['post_type'] = $value['post_type'];
		}

		// Tax query
		if ( ! empty( $value['tax'] ) ) {
			$query_args['tax_query']['relation'] = 'AND';

			foreach ( $value['tax'] as $taxonomy => $terms ) {
				if ( ! empty( $terms ) ) {
					$query_args['tax_query'][] = [
						'taxonomy' => $taxonomy,
						'field'    => $term_field,
						'terms'    => $terms,
					];
				}
			}
		}

		// Author
		if ( ! empty( $value['author'] ) ) {
			$query_args['author'] = $value['author'];
		}

		// Post in
		if ( ! empty( $value['include'] ) ) {
			$query_args['post__in'] = $value['include'];
		}

		// Post not in
		if ( ! empty( $value['exclude'] ) ) {
			$query_args['post__not_in'] = $value['exclude'];
		}

		// Posts per page
		if ( ! empty( $value['posts_per_page'] ) ) {
			$query_args['posts_per_page'] = $value['posts_per_page'];
		}

		// Order by
		if ( ! empty( $value['order_by'] ) ) {
			$query_args['order_by'] = $value['order_by'];
		}

		// Sort order
		if ( ! empty( $value['order'] ) ) {
			$query_args['order'] = $value['order'];
		}

		return $query_args;
	}

	/**
	 * Parse args.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private static function parse_args( $args ) {

		// Control defaults
		$control_defaults = [
			'posts_per_page' => 12,
		];

		// Args
		$args = wp_parse_args( $args, [
			'section_id'       => 'query',
			'setting_prefix'   => 'query',
			'section_label'    => 'Query',
			'post_type'        => [ 'post' ],
			'term_field'       => 'id',
			'control_defaults' => $control_defaults,
		] );

		// Post types
		$post_types         = is_array( $args['post_type'] ) ? $args['post_type'] : explode( ',', $args['post_type'] );
		$post_types_options = [];

		foreach ( $post_types as $post_type ) {
			if ( $post_type_name = Helpers::get_post_type_title( $post_type ) ) {
				$post_types_options[ $post_type ] = $post_type_name;
			}
		}

		// Post types
		$args['post_type']         = array_keys( $post_types_options );
		$args['post_type_options'] = $post_types_options;

		// Taxonomies
		$taxonomies = [];

		foreach ( $post_types_options as $post_type => $name ) {
			$object_taxonomies = get_object_taxonomies( $post_type, 'object' );

			if ( ! empty( $object_taxonomies ) ) {
				foreach ( $object_taxonomies as $object_taxonomy ) {
					$taxonomies[] = [
						'post_type' => $post_type,
						'taxonomy'  => $object_taxonomy->name,
						'name'      => $object_taxonomy->labels->name,
					];
				}
			}
		}

		// Taxonomies
		$args['taxonomies'] = $taxonomies;

		return $args;
	}
}