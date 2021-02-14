<?php
/**
 *    Portfolio Items
 *
 *    Laborator.co
 *    www.laborator.co
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_portfolio_items/portfolio.svg' );

// Portfolio Filter Categories
$portfolio_categories = get_terms( [
	'taxonomy'   => 'portfolio_category',
	'hide_empty' => true,
] );

$portfolio_categories_opts = [
	'Default (All)' => 'default',
];

if ( ! is_wp_error( $portfolio_categories ) ) {
	foreach ( $portfolio_categories as $portfolio_category ) {
		$portfolio_categories_opts[ $portfolio_category->name ] = $portfolio_category->slug;
	}
}

// Portfolio Items
vc_map( [
	'base'        => 'lab_portfolio_items',
	'name'        => 'Portfolio Items',
	"description" => "Show portfolio items",
	'category'    => [ 'Laborator', 'Portfolio' ],
	'icon'        => $lab_vc_element_icon,
	'params'      => [
		[
			'type'        => 'loop',
			'heading'     => 'Portfolio Items',
			'param_name'  => 'portfolio_query',
			'settings'    => [
				'size'      => [ 'hidden' => false, 'value' => 4 * 3 ],
				'order_by'  => [ 'value' => 'date' ],
				'post_type' => [ 'value' => 'portfolio', 'hidden' => false ]
			],
			'description' => 'Create WordPress loop, to populate content from your site.'
		],
		[
			'type'        => 'textfield',
			'heading'     => 'Title',
			'param_name'  => 'title',
			'admin_label' => true,
			'value'       => '',
			'description' => 'Main title of this widget. (Optional)'
		],
		[
			'type'        => 'dropdown',
			'heading'     => 'Title tag',
			'param_name'  => 'title_tag',
			'std'         => 'h2',
			'value'       => [
				'H1' => 'h1',
				'H2' => 'h2',
				'H3' => 'h3',
				'H4' => 'h4',
			],
			'description' => 'Select title tag for widget title.',
		],
		[
			'type'        => 'textarea',
			'heading'     => 'Description',
			'param_name'  => 'description',
			'value'       => '',
			'description' => 'Description under main portfolio title. (Optional)'
		],
		[
			'type'        => 'dropdown',
			'heading'     => 'Category Filter',
			'param_name'  => 'category_filter',
			'value'       => [
				'Yes' => 'yes',
				'No'  => 'no',
			],
			'description' => 'Show category filter above the portfolio items.',
		],
		[
			'type'        => 'dropdown',
			'heading'     => 'Default filter category',
			'param_name'  => 'default_filter_category',
			'value'       => $portfolio_categories_opts,
			'description' => 'Set default category to filter portfolio items at first page load.',
			'dependency'  => [
				'element' => 'category_filter',
				'value'   => [ 'yes' ]
			],
		],
		[
			'type'       => 'checkbox',
			'heading'    => 'Hide "All" filter link from portfolio',
			'param_name' => 'filter_category_hide_all',
			'value'      => [
				'Yes' => 'yes',
			],
			'dependency' => [
				'element'            => 'default_filter_category',
				'value_not_equal_to' => [ 'default' ]
			],
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Portfolio Type',
			'admin_label' => true,
			'param_name'  => 'portfolio_type',
			'std'         => 'type-1',
			'value'       => [
				'Thumbnails with Visible Titles' => 'type-1',
				'Thumbnails with Titles Inside'  => 'type-2',
			],
			'description' => 'Select portfolio type to show items.'
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Columns',
			'admin_label' => true,
			'param_name'  => 'columns',
			'std'         => 'inherit',
			'value'       => [
				'Inherit from Theme Options' => 'inherit',
				'1 Item per Row'             => 1,
				'2 Items per Row'            => 2,
				'3 Items per Row'            => 3,
				'4 Items per Row'            => 4,
				'5 Items per Row'            => 5,
				'6 Items per Row'            => 6,
			],
			'description' => 'Number of columns to show portfolio items.'
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Reveal Effect',
			'param_name'  => 'reveal_effect',
			'std'         => 'inherit',
			'value'       => [
				'Inherit from Theme Options'  => 'inherit',
				'None'                        => 'none',
				'Fade'                        => 'fade',
				'Slide and Fade'              => 'slidenfade',
				'Zoom In'                     => 'zoom',
				'Fade (one by one)'           => 'fade-one',
				'Slide and Fade (one by one)' => 'slidenfade-one',
				'Zoom In (one by one)'        => 'zoom-one',
			],
			'description' => 'Reveal effect for portfolio items.'
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Item Spacing',
			'param_name'  => 'portfolio_spacing',
			'description' => 'Spacing between portfolio items.',
			'std'         => 'inherit',
			'value'       => [
				'Inherit from Theme Options' => 'inherit',
				'Yes'                        => 'yes',
				'No'                         => 'no',
			],
			'dependency'  => [
				'element' => 'portfolio_type',
				'value'   => [ 'type-2' ]
			],
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Dynamic Image Height',
			'param_name'  => 'dynamic_image_height',
			'description' => 'Use proportional image height for each item.',
			'std'         => 'no',
			'value'       => [
				'Yes' => 'yes',
				'No'  => 'no',
			],
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Title and Filter Container',
			'param_name'  => 'portfolio_full_width_title_container',
			'description' => 'Include title and filter within container.',
			'std'         => 'yes',
			'value'       => [
				'Inherit from Theme Options' => 'inherit',
				'Yes'                        => 'yes',
				'No'                         => 'no',
			],
			'dependency'  => [
				'element' => 'portfolio_full_width',
				'value'   => [ 'yes', 'no' ]
			],
		],
		[
			'group'       => 'Layout',
			'type'        => 'dropdown',
			'heading'     => 'Full-width Container',
			'param_name'  => 'portfolio_full_width',
			'description' => 'Extend portfolio container to the browser edge. <br><small>Note: If you  use full-width container, you need to set this VC row container to Full width as well.</small>',
			'std'         => 'inherit',
			'value'       => [
				'Inherit from Theme Options' => 'inherit',
				'Yes'                        => 'yes',
				'No'                         => 'no',
			],
		],
		[
			'group'       => 'Pagination',
			'type'        => 'dropdown',
			'heading'     => 'Pagination Type',
			'param_name'  => 'pagination_type',
			'description' => 'Select pagination type to use with this widget.',
			'std'         => 'static',
			'value'       => [
				'No "Show More" button'     => 'hide',
				'Static "Show More" button' => 'static',
				'Endless Pagination'        => 'endless',
			],
		],
		[
			'group'       => 'Pagination',
			'type'        => 'vc_link',
			'heading'     => 'More Link',
			'param_name'  => 'more_link',
			'value'       => '',
			'description' => 'This will show "More" button in the end of portfolio items.',
			'dependency'  => [
				'element' => 'pagination_type',
				'value'   => [ 'static' ]
			],
		],
		[
			'group'      => 'Pagination',
			'type'       => 'checkbox',
			'heading'    => 'Auto Reveal',
			'param_name' => 'endless_auto_reveal',
			'value'      => [
				'Yes' => 'yes',
			],
			'dependency' => [
				'element' => 'pagination_type',
				'value'   => [ 'endless' ]
			],
		],
		[
			'group'      => 'Pagination',
			'type'       => 'textfield',
			'heading'    => 'Show more button text',
			'param_name' => 'endless_show_more_button_text',
			'value'      => 'Show More',
			'dependency' => [
				'element' => 'pagination_type',
				'value'   => [ 'endless' ]
			],
		],
		[
			'group'      => 'Pagination',
			'type'       => 'textfield',
			'heading'    => 'No more items to show text',
			'param_name' => 'endless_no_more_items_button_text',
			'value'      => 'No more portfolio items to show',
			'dependency' => [
				'element' => 'pagination_type',
				'value'   => [ 'endless' ]
			],
		],
		[
			'group'       => 'Pagination',
			'type'        => 'textfield',
			'heading'     => 'Number of Items to Fetch',
			'param_name'  => 'endless_per_page',
			'value'       => '',
			'description' => 'Apart from "Items per Page", you can set custom number of items to fetch when "Show More" is clicked (Optional)',
			'dependency'  => [
				'element' => 'pagination_type',
				'value'   => [ 'endless' ]
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => 'Extra class name',
			'param_name'  => 'el_class',
			'description' => 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.'
		],
		[
			'type'       => 'css_editor',
			'heading'    => 'Css',
			'param_name' => 'css',
			'group'      => 'Design options'
		]
	]
] );

class WPBakeryShortCode_Lab_Portfolio_Items extends WPBakeryShortCode {
}