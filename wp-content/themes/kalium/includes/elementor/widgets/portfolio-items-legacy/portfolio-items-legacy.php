<?php namespace Kalium\Elementor\Widgets;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Kalium\Elementor\Helpers;
use \Kalium\Elementor\Control_Sets\Query_Posts;

/**
 * Kalium WordPress Theme
 *
 * Portfolio items Elementor widget.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Portfolio_Items_Legacy extends Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'kalium-portfolio-items-legacy';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'Portfolio Items';
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-apps';
	}

	/**
	 * Widget category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'kalium-elements' ];
	}

	/**
	 * Script depends.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return kalium_enqueue_handle( 'isotope', true );
	}

	/**
	 * Widget controls.
	 *
	 * @return void
	 */
	public function _register_controls() {

		// Inherit from theme options
		$label_inherit_theme_options = 'Inherit from Theme Options';

		/**
		 * Heading section.
		 */

		// Heading section start
		$this->start_controls_section( 'section_heading', [
			'label' => 'Heading',
		] );

		// Control: Title
		$this->add_control( 'title', [
			'label'       => 'Title',
			'label_block' => true,
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Portfolio',
		] );

		// Control: Description
		$this->add_control( 'description', [
			'label'   => 'Description',
			'type'    => Controls_Manager::WYSIWYG,
			'default' => 'Our everyday work is presented here, we do what we love,
Case studies, video presentations and photo-shootings below.',
		] );

		// Control: Category filter
		$this->add_control( 'category_filter', [
			'label'       => 'Category Filter',
			'description' => 'Show category filter above the portfolio items.',
			'type'        => Controls_Manager::SWITCHER,
			'label_on'    => 'Yes',
			'label_off'   => 'No',
			'default'     => 'yes',
		] );

		// Control: Default selected category
		$this->add_control( 'default_category_filter', [
			'label'       => 'Default Category Filter',
			'description' => 'Set default category to filter portfolio items at first page load.',
			'type'        => Controls_Manager::SELECT,
			'options'     => Helpers::get_terms_options( 'portfolio_category', [
				'field'   => 'slug',
				'default' => 'All',
			] ),
			'condition'   => [
				'category_filter' => 'yes',
			],
		] );

		// Heading section end
		$this->end_controls_section();

		/**
		 * Query section.
		 */

		// Query portfolio section
		Query_Posts::add_controls( $this, [
			'post_type'  => 'portfolio',
			'term_field' => 'slug',
		] );

		/**
		 * Pagination section.
		 */

		// Pagination section start
		$this->start_controls_section( 'section_pagination', [
			'label' => 'Pagination',
		] );

		// Control: Pagination type
		$this->add_control( 'pagination_type', [
			'label'   => 'Pagination Type',
			'type'    => Controls_Manager::SELECT,
			'options' => [
				''                => 'No pagination',
				'static-link'     => 'Static link',
				'infinite-scroll' => 'Infinite scroll',
			],
			'default' => 'infinite-scroll',
		] );

		// Control: Show More Button Text
		$this->add_control( 'pagination_static_link_button_text', [
			'label'       => 'Show More Button Text',
			'label_block' => true,
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Show More',
			'condition'   => [ 'pagination_type' => 'static-link' ],
		] );

		// Control Button URL
		$this->add_control( 'pagination_static_link_button_link', [
			'label'       => 'Button Link',
			'type'        => Controls_Manager::URL,
			'placeholder' => 'Paste URL or type a page title',
			'condition'   => [ 'pagination_type' => 'static-link' ],
		] );

		// Control: Auto reveal
		$this->add_control( 'pagination_infinite_scroll_auto_reveal', [
			'label'       => 'Auto Reveal',
			'description' => 'Load next page as soon as user reaches the end of viewport.',
			'type'        => Controls_Manager::SWITCHER,
			'label_on'    => 'Yes',
			'label_off'   => 'No',
			'condition'   => [ 'pagination_type' => 'infinite-scroll' ],
		] );

		// Control: Number of Items to Fetch
		$this->add_control( 'pagination_infinite_scroll_per_page', [
			'label'       => 'Number of Items to Fetch',
			'description' => 'Portfolio items to load on next page.',
			'type'        => Controls_Manager::NUMBER,
			'condition'   => [ 'pagination_type' => 'infinite-scroll' ],
		] );

		// Control: Show More Button Text
		$this->add_control( 'pagination_infinite_scroll_button_text', [
			'label'       => 'Show More Button Text',
			'label_block' => true,
			'type'        => Controls_Manager::TEXT,
			'default'     => 'Show More',
			'condition'   => [ 'pagination_type' => 'infinite-scroll' ],
		] );

		// Control: Reached End Text
		$this->add_control( 'pagination_infinite_scroll_reached_end_text', [
			'label'       => 'Reached End Text',
			'label_block' => true,
			'type'        => Controls_Manager::TEXT,
			'default'     => 'No more portfolio items to show',
			'condition'   => [ 'pagination_type' => 'infinite-scroll' ],
		] );

		// Pagination section end
		$this->end_controls_section();

		/**
		 * Layout section.
		 */

		// Layout section start
		$this->start_controls_section( 'section_layout', [
			'label' => 'Layout',
			'tab'   => Controls_Manager::TAB_LAYOUT,
		] );

		// Control: Portfolio item layout
		$this->add_control( 'portfolio_item_layout_type', [
			'label'       => 'Item layout type',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				''       => $label_inherit_theme_options,
				'type-1' => 'Item thumbnail + details below',
				'type-2' => 'Item thumbnail + details over thumbnail',
			],
		] );

		// Control: Portfolio columns
		$this->add_control( 'portfolio_columns', [
			'label'       => 'Columns',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				'' => $label_inherit_theme_options,
				1  => '1 item per row',
				2  => '2 items per row',
				3  => '3 items per row',
				4  => '4 items per row',
				5  => '5 items per row',
				6  => '6 items per row',
			],
		] );

		// Control: Reveal effect
		$this->add_control( 'portfolio_items_reveal_effect', [
			'label'       => 'Reveal Effect',
			'label_block' => true,
			'type'        => Controls_Manager::SELECT,
			'options'     => [
				''               => $label_inherit_theme_options,
				'none'           => 'None',
				'fade'           => 'Fade',
				'slidenfade'     => 'Slide and fade',
				'zoom'           => 'Zoom in',
				'fade-one'       => 'Fade (one by one)',
				'slidenfade-one' => 'Slide and fade (one by one)',
				'zoom-one'       => 'Zoom in (one by one)',
			],
		] );

		// Control: Category filter
		$this->add_control( 'portfolio_dynamic_image_height', [
			'label'     => 'Dynamic image height',
			'type'      => Controls_Manager::SWITCHER,
			'label_on'  => 'Yes',
			'label_off' => 'No',
		] );

		// Layout section end
		$this->end_controls_section();
	}

	/**
	 * Render widget.
	 *
	 * @return void
	 */
	public function render() {
		$settings = $this->get_settings_for_display();

		// Heading
		$title                   = $settings['title'];
		$description             = $settings['description'];
		$category_filter         = kalium_validate_boolean( $settings['category_filter'] );
		$default_category_filter = $settings['default_category_filter'];

		// Query
		$query      = Query_Posts::get_value( $this, [
			'post_type' => 'portfolio',
		] );
		$query_args = Query_Posts::to_query_args( $query, [
			'term_field' => 'slug',
		] );

		// Pagination
		$pagination_type                             = kalium_get_array_key( $settings, 'pagination_type', kalium_get_theme_option( 'portfolio_pagination_type' ) );
		$pagination_static_link_button_text          = kalium_get_array_key( $settings, 'pagination_static_link_button_text', 'Show More' );
		$pagination_static_link_button_link          = $settings['pagination_static_link_button_link'];
		$pagination_infinite_scroll_auto_reveal      = kalium_validate_boolean( $settings['pagination_infinite_scroll_auto_reveal'] );
		$pagination_infinite_scroll_per_page         = $settings['pagination_infinite_scroll_per_page'];
		$pagination_infinite_scroll_button_text      = kalium_get_array_key( $settings, 'pagination_infinite_scroll_button_text', 'Show More' );
		$pagination_infinite_scroll_reached_end_text = kalium_get_array_key( $settings, 'pagination_infinite_scroll_reached_end_text', 'No more portfolio items to show' );

		// Layout
		$columns              = $settings['portfolio_columns'];
		$item_layout_type     = kalium_get_array_key( $settings, 'portfolio_item_layout_type', kalium_get_theme_option( 'portfolio_type' ) );
		$items_reveal_effect  = kalium_get_array_key( $settings, 'portfolio_items_reveal_effect', kalium_get_theme_option( 'portfolio_reveal_effect' ) );
		$dynamic_image_height = kalium_validate_boolean( $settings['portfolio_dynamic_image_height'] );

		// Since the columns count is determined by item layout type, get default if not not defined
		if ( ! $columns ) {
			$columns_count = kalium_get_theme_option(
				kalium_conditional(
					'type-1' === $item_layout_type,
					'portfolio_type_1_columns_count',
					'portfolio_type_2_columns_count'
				)
			);

			// Convert to numeric value
			if ( ! empty( $columns_count ) && ! is_numeric( $columns_count ) ) {
				$columns = substr( $columns_count, 0, 1 );
			}
		}

		// Dynamic image height turns portfolio item layout type 3 to 2
		if ( 'type-3' === $item_layout_type && ! $dynamic_image_height ) {
			$item_layout_type = 'type-2';
		}

		// Convert columns to number
		if ( is_numeric( $columns ) ) {
			$columns = intval( $columns );
		}

		// Classes
		$container_classes = [
			'portfolio-container-and-title',
			"portfolio-loop-layout-{$item_layout_type}",
		];

		// Portfolio query args
		$portfolio_query_args = [
			'query_args'    => $query_args,
			'vc_mode'       => true,
			'vc_attributes' => [
				'title'                => $title,
				'category_filter'      => kalium_conditional( $category_filter, 'yes', 'no' ),
				'description'          => $description,
				'columns'              => $columns,
				'portfolio_type'       => $item_layout_type,
				'reveal_effect'        => $items_reveal_effect,
				'dynamic_image_height' => $dynamic_image_height,
			],
		];

		// Default filter category
		$portfolio_query_args['vc_attributes']['default_filter_category']  = $default_category_filter;
		$portfolio_query_args['vc_attributes']['filter_category_hide_all'] = $category_filter && '' !== $default_category_filter ? $default_category_filter : false;

		// Infinite scroll pagination
		if ( 'infinite-scroll' === $pagination_type ) {
			$portfolio_query_args['endless_per_page']   = $pagination_infinite_scroll_per_page;
			$portfolio_query_args['pagination']['type'] = $pagination_infinite_scroll_auto_reveal ? 'endless-reveal' : 'endless';

			$portfolio_query_args['vc_attributes']['endless_show_more_button_text']     = $pagination_infinite_scroll_button_text;
			$portfolio_query_args['vc_attributes']['endless_no_more_items_button_text'] = $pagination_infinite_scroll_reached_end_text;
			$portfolio_query_args['vc_attributes']['endless_auto_reveal']               = kalium_conditional( $pagination_infinite_scroll_auto_reveal, 'yes', '' );
		}

		// Portfolio query
		$portfolio_args            = kalium_get_portfolio_query( $portfolio_query_args );
		$portfolio_query           = $portfolio_args['portfolio_query'];
		$GLOBALS['portfolio_args'] = $portfolio_args;

		// Portfolio Container Class
		$portfolio_container_classes = [
			'portfolio-holder',
			'portfolio-' . $portfolio_args['layout_type'],
		];

		// Sort items by clicking on the category (under title)
		if ( apply_filters( 'portfolio_container_isotope_category_sort_by_js', true ) ) {
			$portfolio_container_classes[] = 'sort-by-js';
		}

		// Masonry Layout
		if ( 'type-1' === $portfolio_args['layout_type'] && $portfolio_args['layouts']['type_1']['dynamic_image_height'] || 'type-2' === $portfolio_args['layout_type'] ) {
			$portfolio_container_classes[] = 'is-masonry-layout';
		}

		// Merged Layout
		if ( 'type-2' === $portfolio_args['layout_type'] && 'merged' === $portfolio_args['layouts']['type_2']['grid_spacing'] ) {
			$portfolio_container_classes[] = 'merged-item-spacing';
		}

		// Item Spacing
		if ( 'type-2' === $portfolio_args['layout_type'] && 'normal' === $portfolio_args['layouts']['type_2']['grid_spacing'] && is_numeric( $portfolio_args['layouts']['type_2']['default_spacing'] ) ) {
			$spacing_in_px                 = $portfolio_args['layouts']['type_2']['default_spacing'] / 2 . 'px';
			$portfolio_container_classes[] = 'portfolio-loop-custom-item-spacing';

			kalium_append_custom_css( '.page-container > .row', "margin: 0 -" . $spacing_in_px );
			kalium_append_custom_css( '.portfolio-holder.portfolio-loop-custom-item-spacing .type-portfolio[data-portfolio-item-id]', "padding: {$spacing_in_px};" );
			kalium_append_custom_css( '.portfolio-holder .portfolio-item.masonry-portfolio-item.has-post-thumbnail .masonry-box .masonry-thumb', "margin: {$spacing_in_px};" );
		}

		// Widget output
		include 'render.php';
	}
}