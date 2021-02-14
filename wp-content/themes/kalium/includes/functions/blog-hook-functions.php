<?php
/**
 * Kalium WordPress Theme
 *
 * Blog hook functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Initialize blog options.
 */
function _kalium_blog_initialize_options( $extend_options = [], $id = null ) {
	global $blog_options;

	// Single post page
	$is_single = is_single();

	// Blog template
	switch ( kalium_get_theme_option( 'blog_template' ) ) {
		// Standard
		case 'blog-masonry' :
			$blog_template = 'standard';
			break;

		// Rounded
		case 'blog-rounded' :
			$blog_template = 'rounded';
			break;

		// Square is default
		default:
			$blog_template = 'square';
	}

	// Blog Options Array
	$options = [
		// Blog instance id
		'id'            => 'blog-posts-main',

		// Template to use (3 types)
		'blog_template' => $blog_template,

		// Loop blog options
		'loop'          => [

			// Blog header
			'header'            => [
				'show'        => kalium_get_theme_option( 'blog_show_header_title' ),
				'title'       => kalium_get_theme_option( 'blog_title' ),
				'description' => kalium_get_theme_option( 'blog_description' ),
			],

			// Container classes
			'container_classes' => [ 'blog-posts' ],

			// Sidebar
			'sidebar'           => [

				// Visibility
				'visible'   => 'hide' !== kalium_get_theme_option( 'blog_sidebar_position' ),

				// Alignment
				'alignment' => kalium_get_theme_option( 'blog_sidebar_position' ),
			],

			// Post formats support
			'post_formats'      => kalium_get_theme_option( 'blog_post_formats' ),

			// Post title
			'post_title'        => kalium_get_theme_option( 'blog_post_title', true ),

			// Post excerpt
			'post_excerpt'      => kalium_get_theme_option( 'blog_post_excerpt', true ),

			// Post date
			'post_date'         => kalium_get_theme_option( 'blog_post_date', true ),

			// Post category
			'post_category'     => kalium_get_theme_option( 'blog_category', true ),

			// Post thumbnail
			'post_thumbnail'    => [

				// Visibility
				'visible'     => kalium_get_theme_option( 'blog_thumbnails' ),

				// Image sizes
				'size'        => 'thumbnail',

				// Placeholder
				'placeholder' => kalium_get_theme_option( 'blog_thumbnails_placeholder' ),

				// Hover layer
				'hover'       => [
					'type' => kalium_get_theme_option( 'blog_thumbnail_hover_effect' ),
					'icon' => kalium_get_theme_option( 'blog_post_hover_layer_icon' ),

					'custom' => [
						'image_id' => kalium_get_theme_option( 'blog_post_hover_layer_icon_custom' ),
						'width'    => kalium_get_theme_option( 'blog_post_hover_layer_icon_custom_width' )
					]
				],
			],

			// Post format icon
			'post_format_icon'  => kalium_get_theme_option( 'blog_post_type_icon' ),

			// Columns
			'columns'           => 1,

			// Row layout mode
			'row_layout_mode'   => kalium_get_theme_option( 'blog_masonry_layout_mode' ),

			// Pagination
			'pagination'        => [
				'type'      => kalium_get_theme_option( 'blog_pagination_type' ),
				'alignment' => kalium_get_theme_option( 'blog_pagination_position' ),
				'style'     => '_2' == kalium_get_theme_option( 'blog_endless_pagination_style' ) ? 'pulsating' : 'spinner',
			],

			// Other settings
			'other'             => [

				// Masonry spacing
				'columns_gap' => kalium_get_theme_option( 'blog_masonry_columns_gap' ),

				// Masonry borders
				'borders'     => kalium_get_theme_option( 'blog_masonry_borders', 'yes' ),
			]
		],

		// Single blog options
		'single'        => [

			// Share story
			'share'                    => [

				// Visibility
				'visible'  => kalium_get_theme_option( 'blog_share_story' ),

				// Share networks
				'networks' => kalium_get_theme_option( 'blog_share_story_networks' ),

				// Icons style
				'style'    => kalium_get_theme_option( 'blog_share_story_rounded_icons' ) ? 'icons' : 'plain',
			],

			// Sidebar
			'sidebar'                  => [

				// Visibility
				'visible'   => 'hide' !== kalium_get_theme_option( 'blog_single_sidebar_position' ),

				// Alignment
				'alignment' => kalium_get_theme_option( 'blog_single_sidebar_position' ),
			],

			// Post image
			'post_image'               => [

				// Visibility
				'visible'   => kalium_get_theme_option( 'blog_single_thumbnails' ),

				// Image size
				'size'      => 'default' == kalium_get_theme_option( 'blog_featured_image_size_type' ) ? 'blog-single-1' : 'original',

				// Image placement
				'placement' => 'full-width' == kalium_get_theme_option( 'blog_featured_image_placement' ) ? 'full-width' : 'boxed',
			],

			// Post title
			'post_title'               => kalium_get_theme_option( 'blog_single_title', true ),

			// Post tags
			'post_tags'                => kalium_get_theme_option( 'blog_tags' ),

			// Post category
			'post_category'            => kalium_get_theme_option( 'blog_category_single', true ),

			// Post date
			'post_date'                => kalium_get_theme_option( 'blog_post_date_single', true ),

			// Post comments
			'post_comments'            => 'hide' != kalium_get_theme_option( 'blog_comments' ),

			// Author
			'author'                   => [

				// Visibility
				'visible'   => kalium_get_theme_option( 'blog_author_info' ),

				// Author info placement
				'placement' => kalium_get_theme_option( 'blog_author_info_placement', 'left' )
			],

			// Prev next navigation
			'prev_next'                => kalium_get_theme_option( 'blog_post_prev_next' ),

			// Gallery carousel auto switch
			'gallery_autoswitch_image' => absint( kalium_get_theme_option( 'blog_gallery_autoswitch' ) )
		]
	];

	// Blog instance ID
	if ( $id ) {
		$options['id'] = sprintf( 'blog-posts-%s', esc_attr( $id ) );
	}

	// Blog settings based on blog template
	switch ( $blog_template ) {

		// Square post thumbnail
		case 'square' :
			$options['loop']['post_thumbnail']['size'] = 'blog-thumb-1';
			break;

		// Rounded post thumbnail
		case 'rounded' :
			$options['loop']['post_thumbnail']['size'] = 'blog-thumb-2';
			break;

		// Standard post item
		case 'standard' :
			$options['loop']['post_thumbnail']['size'] = 'blog-thumb-3';

			// Standard blog columns
			$options['loop']['columns'] = absint( ltrim( kalium_get_theme_option( 'blog_columns' ), '_' ) );
			break;
	}

	// Proportional thumbnails
	if ( kalium_get_theme_option( 'blog_loop_proportional_thumbnails' ) && 'rounded' !== $blog_template ) {
		$options['loop']['post_thumbnail']['size'] = 'large';
		//$options['single']['post_image']['size'] = 'large';
	}

	// Rounded blog template does not supports post formats on loop
	if ( ! $is_single && 'rounded' == $blog_template ) {
		$options['loop']['post_formats'] = false;
	}

	// Loop Standard Post Template
	if ( 'standard' == $blog_template && ! $is_single ) {
		$options['loop']['container_classes'][] = sprintf( 'columns-%d', $options['loop']['columns'] );
	}

	// When its assigned as blog page
	if ( is_home() && ( $post = get_queried_object() ) ) {
		$heading_title = kalium_get_field( 'heading_title', $post->ID );

		// Show heading title
		if ( $heading_title ) {
			$heading_title_type         = kalium_get_field( 'page_heading_title_type', $post->ID );
			$heading_custom_title       = kalium_get_field( 'page_heading_custom_title', $post->ID );
			$heading_description_type   = kalium_get_field( 'page_heading_description_type', $post->ID );
			$heading_custom_description = kalium_get_field( 'page_heading_custom_description', $post->ID );

			$options['loop']['header']['show']        = true;
			$options['loop']['header']['title']       = 'post_title' == $heading_title_type ? get_the_title( $post ) : $heading_custom_title;
			$options['loop']['header']['description'] = 'post_content' == $heading_description_type ? apply_filters( 'the_content', $post->post_content ) : $heading_custom_description;
		}
	}

	// Single
	if ( is_single() ) {
		$post_id = get_queried_object_id();

		// Featured image placement
		$featured_image_placement = kalium_get_field( 'featured_image_placing', $post_id );

		if ( $featured_image_placement && in_array( $featured_image_placement, [
				'container',
				'full-width',
				'hide'
			] ) ) {
			$options['single']['post_image']['placement'] = 'full-width' == $featured_image_placement ? 'full-width' : 'boxed';

			if ( 'hide' == $featured_image_placement ) {
				$options['single']['post_image']['visible'] = false;
			}
		}

		// Featured image size
		$post_image_size = kalium_get_field( 'post_image_size', $post_id );

		if ( in_array( $post_image_size, [ 'default', 'full' ] ) ) {
			$options['single']['post_image']['size'] = 'default' == $post_image_size ? 'blog-single-1' : 'original';
		}

		// When sidebar is present and author info is shown horizontally
		if ( $options['single']['author']['visible'] && $options['single']['sidebar']['visible'] && in_array( $options['single']['sidebar']['alignment'], [
				'left',
				'right'
			] ) ) {
			//$options['single']['author']['placement'] = 'bottom';
		}

		// Password protected post or attachment page disable few sections
		if ( post_password_required() || is_attachment() ) {
			$options['single']['post_image']['visible'] = false;
			$options['single']['author']['visible']     = false;
			$options['single']['share']['visible']      = false;
			$options['single']['post_tags']             = false;
			$options['single']['prev_next']             = false;
		}
	}

	// Extend/replace blog options
	if ( ! empty( $extend_options ) && is_array( $extend_options ) ) {
		// Remove ID
		if ( isset( $extend_options['id'] ) ) {
			unset( $extend_options['id'] );
		}

		$options = array_merge( $options, $extend_options );
	}

	// Blog options
	$blog_options = apply_filters( 'kalium_blog_options', $options );

	return $blog_options;
}

/**
 * Reset blog options global.
 */
function _kalium_blog_reset_options() {
	global $blog_options;
	$blog_options = [];
}

/**
 * Blog posts loop.
 */
function _kalium_blog_posts_loop() {

	// Enqueue Isotope
	kalium_enqueue_isotope_and_packery_library();

	// Args
	$args = [
		'id'      => kalium_blog_get_option( 'id' ),
		'classes' => kalium_blog_get_option( 'loop/container_classes' )
	];

	if ( 'fit-rows' === kalium_blog_get_option( 'loop/row_layout_mode' ) ) {
		$args['classes'][] = 'fit-rows';
	}

	// Gap
	$columns_gap = kalium_blog_get_option( 'loop/other/columns_gap' );

	if ( 'standard' == kalium_blog_get_template() && '' !== $columns_gap ) {
		$columns_gap       = intval( $columns_gap );
		$args['classes'][] = sprintf( 'columns-gap-%s', $columns_gap >= 0 ? $columns_gap : 'none' );
	}

	// Borderless layout
	if ( 'standard' === kalium_blog_get_option( 'blog_template' ) && 'no' === kalium_blog_get_option( 'loop/other/borders' ) ) {
		$args['classes'][] = 'blog-posts--borderless';
	}

	// Posts template
	kalium_get_template( 'blog/posts.php', $args );
}

/**
 * Blog archive classes.
 */
function _kalium_blog_container_classes( $classes ) {

	// Sidebar
	if ( kalium_blog_get_option( 'loop/sidebar/visible' ) ) {
		$sidebar_alignment = kalium_blog_get_option( 'loop/sidebar/alignment' );

		$classes[] = 'blog--has-sidebar';
		$classes[] = sprintf( 'blog--sidebar-alignment-%s', $sidebar_alignment );
	}

	if ( ! empty( $_classes ) && is_array( $_classes ) ) {
		$classes = array_merge( $classes, $_classes );
	}

	return $classes;
}

/**
 * Blog archive pagination.
 */
function _kalium_blog_archive_posts_pagination() {

	// Args
	$args = [];

	// Blog instance ID
	$blog_instance_id = kalium_blog_instance_id();
	$args['id']       = $blog_instance_id;

	// Pagination Type
	$pagination_type      = kalium_blog_get_option( 'loop/pagination/type' );
	$pagination_alignment = kalium_blog_get_option( 'loop/pagination/alignment' );
	$pagination_style     = kalium_blog_get_option( 'loop/pagination/style' );

	// Num pages
	$query          = $GLOBALS['wp_query'];
	$max_num_pages  = $query->max_num_pages;
	$posts_per_page = $query->query_vars['posts_per_page'];
	$found_posts    = absint( $query->found_posts );

	// Classes
	$classes   = [];
	$classes[] = sprintf( 'pagination--align-%s', $pagination_alignment );

	$args['extra_classes'] = $classes;

	// If there is more than one page
	if ( $max_num_pages > 1 ) {

		// Normal pagination
		if ( 'normal' == $pagination_type ) {
			$pagination_args = [
				'total' => $max_num_pages
			];

			$args['pagination_args'] = $pagination_args;

			kalium_get_template( 'global/pagination-normal.php', $args );
		} // Endless pagination
		else if ( in_array( $pagination_type, [ 'endless', 'endless-reveal' ] ) ) {
			$args['show_more_text']       = esc_html__( 'Show more', 'kalium' );
			$args['all_items_shown_text'] = esc_html__( 'All posts are shown', 'kalium' );
			$args['loading_style']        = $pagination_style;

			kalium_get_template( 'global/pagination-infinite-scroll.php', $args );

			// Endless pagination instance (JS)
			$infinite_scroll_pagination_args = [
				// Base query
				'base_query'     => $query->query,

				// Pagination
				'total_items'    => $found_posts,
				'posts_per_page' => $posts_per_page,
				'fetched_items'  => kalium_get_post_ids_from_query( $query ),

				// Auto reveal
				'auto_reveal'    => 'endless-reveal' == $pagination_type,

				// Loop template function
				'loop_template'  => 'kalium_blog_loop_post_template',

				// Action and callback
				'callback'       => 'kaliumBlogEndlessPaginationHandler',

				// Extra arguments (passed on Ajax Request)
				'args'           => [
					'blogInfiniteScroll' => 1
				]
			];

			kalium_infinite_scroll_pagination_js_object( $blog_instance_id, $infinite_scroll_pagination_args );

			// Enqueue some scripts that are required
			if ( kalium_blog_get_option( 'loop/post_formats' ) ) {
				kalium_enqueue_media_library();
			}
		}
	}
}

/**
 * Loop post thumbnail.
 */
function _kalium_blog_post_thumbnail() {
	global $post;

	// Args
	$args = [];

	// Thumbnail size
	$args['thumbnail_size'] = kalium_blog_get_option( 'loop/post_thumbnail/size' );

	// Supported post formats
	if ( kalium_blog_get_option( 'loop/post_formats' ) ) {
		$args['post_format_content'] = kalium_extract_post_format_content( $post );
	}

	// Show post thumbnails only if they are set to be visible
	if ( kalium_blog_get_option( 'loop/post_thumbnail/visible' ) ) {

		if ( kalium_blog_get_option( 'loop/post_thumbnail/placeholder' ) || has_post_thumbnail( $post ) || ! empty( $args['post_format_content'] ) ) {

			// Post thumbnail template
			kalium_get_template( 'blog/loop/post-thumbnail.php', $args );
		}
	}
}

/**
 * Loop post thumbnail hover layer.
 */
function _kalium_blog_post_hover_layer() {
	global $post;

	$post_format   = get_post_format( $post );
	$blog_template = kalium_blog_get_template();

	// Args
	$args = [];

	// Show hover layer or not
	$show_post_hover_layer = kalium_blog_get_option( 'loop/post_thumbnail/hover/type' );

	if ( kalium_blog_get_option( 'loop/post_formats' ) && in_array( $blog_template, [
			'square',
			'standard'
		] ) && in_array( $post_format, [ 'quote', 'gallery', 'video', 'audio' ] ) ) {
		$show_post_hover_layer = false;
	}

	// Hover layer is shown
	if ( $show_post_hover_layer ) {

		// Hover layer vars
		$hover_options = kalium_blog_get_option( 'loop/post_thumbnail/hover' );

		$hover_type = $hover_options['type'];
		$hover_icon = $hover_options['icon'];

		$args['hover_icon'] = $hover_icon;

		// Custom Hover Icon
		if ( 'custom' == $hover_icon ) {
			$atts          = [];
			$custom_hover  = $hover_options['custom'];
			$attachment_id = $custom_hover['image_id'];

			// Icon width
			$hover_icon_custom_width = $custom_hover['width'];

			if ( $attachment_id && is_numeric( $hover_icon_custom_width ) ) {
				$attachment = wp_get_attachment_image_src( $attachment_id, 'original' );

				if ( $attachment ) {
					$hover_icon_custom_height = absint( $attachment[2] * ( $hover_icon_custom_width / $attachment[1] ) );
					$atts['style']            = "width: {$hover_icon_custom_width}px; height: {$hover_icon_custom_height}px;";
				}
			}

			// Custom hover icon
			$hover_icon_custom = wp_get_attachment_image( $attachment_id, 'original', null, $atts );

			$args['hover_icon_custom'] = $hover_icon_custom;
		}

		// Hover layer classes
		$classes = [ 'post-hover' ];

		// Hover layer with no opacity
		if ( in_array( $hover_type, [ 'full-cover-no-opacity', 'distanced-no-opacity' ] ) ) {
			$classes[] = 'post-hover--no-opacity';
		}

		// Hover layer with spacing
		if ( in_array( $hover_type, [ 'distanced', 'distanced-no-opacity' ] ) ) {
			$classes[] = 'post-hover--distanced';
		}

		$args['classes'] = $classes;

		// Post thumbnail hover template
		kalium_get_template( 'blog/loop/post-thumbnail-hover.php', $args );
	}
}

/**
 * Blog post format icon.
 */
function _kalium_blog_post_format_icon() {
	global $post;

	if ( kalium_blog_get_option( 'loop/post_format_icon' ) ) {
		$post_format = get_post_format( $post );

		// Args
		$args = [];

		// Default post icon
		$icon = 'icon icon-basic-sheet-txt';

		// Available icons
		$post_format_icons = [
			'quote'   => 'fa fa-quote-left',
			'video'   => 'icon icon-basic-video',
			'audio'   => 'icon icon-music-note-multiple',
			'link'    => 'icon icon-basic-link',
			'image'   => 'icon icon-basic-photo',
			'gallery' => 'icon icon-basic-picture-multiple',
		];

		if ( $post_format && isset( $post_format_icons[ $post_format ] ) ) {
			$icon = $post_format_icons[ $post_format ];
		}

		$args['icon'] = $icon;

		// Post icon template
		kalium_get_template( 'blog/loop/post-icon.php', $args );
	}
}

/**
 * Blog post title.
 */
function _kalium_blog_post_title() {
	$heading_tag = is_single() ? 'h1' : 'h3';

	// Title wrap
	$heading_tag_open  = sprintf( '<%s class="post-title entry-title">', $heading_tag );
	$heading_tag_close = sprintf( '</%s>', $heading_tag );

	// Post link wrap (in the archive page)
	if ( ! is_single() ) {
		$heading_tag_open  .= sprintf( '<a href="%s" target="%s" rel="bookmark">', get_permalink(), kalium_blog_post_link_target() );
		$heading_tag_close = '</a>' . $heading_tag_close;
	}

	// Args
	$args = [
		'heading_tag_open'  => $heading_tag_open,
		'heading_tag_close' => $heading_tag_close,
	];

	if ( is_single() ) {
		$show_post_title = kalium_blog_get_option( 'single/post_title' );
	} else {
		$show_post_title = kalium_blog_get_option( 'loop/post_title' );
	}

	// Show title
	if ( $show_post_title ) {

		// Post title template
		kalium_get_template( 'blog/post-title.php', $args );
	}
}

/**
 * Single post layout
 */
function _kalium_blog_single_post_layout() {

	// Args
	$args = [];

	// Single post template
	kalium_get_template( 'blog/single.php', $args );
}

/**
 * Single post classes filter.
 */
function _kalium_blog_single_container_classes( $classes ) {

	// Post author placement
	if ( kalium_blog_get_option( 'single/author/visible' ) ) {
		$author_placement = kalium_blog_get_option( 'single/author/placement' );

		$classes[] = 'single-post--has-author-info';

		if ( in_array( $author_placement, [ 'left', 'right' ] ) ) {
			$classes[] = 'author-info--alignment-horizontal';
		}

		$classes[] = sprintf( 'author-info--alignment-%s', $author_placement );
	}

	// Sidebar
	if ( kalium_blog_get_option( 'single/sidebar/visible' ) ) {
		$sidebar_alignment = kalium_blog_get_option( 'single/sidebar/alignment' );

		$classes[] = 'single-post--has-sidebar';
		$classes[] = sprintf( 'single-post--sidebar-alignment-%s', $sidebar_alignment );
	}

	// Featured image missing
	if ( ! has_post_thumbnail() || ! kalium_blog_get_option( 'single/post_image/visible' ) ) {
		$classes[] = 'single-post--no-featured-image';
	}

	return $classes;
}

/**
 * Single post image or post format content.
 */
function _kalium_blog_single_post_image() {
	global $post;

	$show_post_image = kalium_blog_get_option( 'single/post_image/visible' );

	if ( $show_post_image ) {

		// Args
		$args = [];

		$args['post']           = get_post();
		$args['thumbnail_size'] = kalium_blog_get_option( 'single/post_image/size' );

		// Supported post formats
		if ( kalium_blog_get_option( 'post_formats' ) ) {
			$args['post_format_content'] = kalium_extract_post_format_content( $post );
		}

		// Enqueue slider for post image
		if ( apply_filters( 'kalium_blog_single_post_image_lightbox', true ) ) {
			kalium_enqueue_lightbox_library();
		}

		// Show only if there is post image or post format content
		if ( has_post_thumbnail() || ! empty( $args['post_format_content'] ) ) {

			// Post image template
			kalium_get_template( 'blog/single/post-image.php', $args );
		}
	}
}

/**
 * Single post image in boxed format.
 */
function _kalium_blog_single_post_image_boxed() {
	if ( 'boxed' === kalium_blog_get_option( 'single/post_image/placement' ) ) {
		_kalium_blog_single_post_image();
	}
}

/**
 * Single post prev and next navigation.
 */
function _kalium_blog_single_post_prev_next_navigation() {
	if ( kalium_blog_get_option( 'single/prev_next' ) ) {

		// Args
		$args = [];

		$adjacent_post_args = apply_filters( 'kalium_blog_single_post_prev_next_navigation', [
			'return' => 'id',
			'loop'   => true
		] );

		$prev_id = previous_post_link_plus( $adjacent_post_args );
		$next_id = next_post_link_plus( $adjacent_post_args );

		// Previous link
		if ( $prev_id ) {
			$prev               = get_post( $prev_id );
			$args['prev']       = $prev;
			$args['prev_title'] = esc_html__( 'Older Post', 'kalium' );
		}

		// Next link
		if ( $next_id ) {
			$next               = get_post( $next_id );
			$args['next']       = $next;
			$args['next_title'] = esc_html__( 'Newer Post', 'kalium' );
		}

		// Post navigation template
		kalium_get_template( 'global/post-navigation.php', $args );
	}
}

/**
 * Single post comments.
 */
function _kalium_blog_single_post_comments() {
	if ( apply_filters( 'kalium_blog_comments', true ) && false === post_password_required() ) {
		comments_template();
	}
}

/**
 * Add labeled input group class for comment form fields.
 */
function _kalium_blog_comment_form_defaults( $defaults ) {

	// Textarea
	$defaults['comment_field'] = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-textarea-row \2"', $defaults['comment_field'] );

	// Comment attributes
	$total_fields = count( $defaults['fields'] );

	foreach ( $defaults['fields'] as & $field ) {
		$field = preg_replace( '/(<p.*?)class="(.*?)"/', '\1class="labeled-input-row \2"', $field );
	}

	// Cookie consent
	if ( isset( $defaults['fields']['cookies'] ) ) {
		$defaults['class_form'] .= ' requires-cookie-consent';
	}

	return $defaults;
}

/**
 * Excerpt length when sidebar is present or is single columned.
 */
function _kalium_blog_custom_excerpt_length( $length ) {
	if ( kalium_blog_is_in_the_loop() ) {

		// Masonry mode with single column
		if ( 'standard' == kalium_blog_get_template() && 1 === kalium_blog_get_option( 'loop/columns' ) ) {
			return 70;
		}

		// Sidebar is present
		if ( kalium_blog_get_option( 'loop/sidebar/visible' ) ) {
			return 32;
		}
	}

	return $length;
}

/**
 * External post redirect.
 */
function _kalium_blog_external_post_format_redirect() {

	if ( is_single() && 'link' == get_post_format() && apply_filters( 'kalium_blog_external_link_redirect', true ) ) {
		$urls = wp_extract_urls( get_the_content() );

		if ( $urls ) {
			wp_redirect( current( $urls ) );
			exit;
		}
	}
}

/**
 * Blog post content, clear post format if its enabled to be parsed.
 */
function _kalium_blog_clear_post_format_from_the_content( $content ) {
	global $post, $wp_embed;

	// Image post
	if ( has_post_format( 'image' ) ) {
		$post_format = kalium_extract_post_format_content( $post );

		if ( ! empty( $post_format['content'] ) ) {
			$post_format_content = strip_tags( $post_format['content'], '<img>' );
			$content             = preg_replace( sprintf( '/%s/', preg_quote( $post_format_content, '/' ) ), '', $content );
		}
	} // Quote post
	else if ( has_post_format( 'quote' ) ) {

		$post_format = kalium_extract_post_format_content( $post );

		if ( ! empty( $post_format['content'] ) ) {
			$post_format_content = $post_format['content'];
			$content             = preg_replace( sprintf( '/%s/', preg_quote( $post_format_content, '/' ) ), '', $content );
		}
	} // Audio post
	else if ( has_post_format( 'audio' ) ) {
		$urls = wp_extract_urls( $content );

		if ( ! empty( $urls ) ) {
			$url       = reset( $urls );
			$has_media = $url !== $wp_embed->autoembed( $url );

			if ( $has_media ) {
				$content = preg_replace( sprintf( '/\[audio.*?%s.*?\]\[\\/audio\]/', preg_quote( $url, '/' ) ), '', $content ); // First clear [audio] shortcodes with this url
				$content = preg_replace( sprintf( '/%s/', preg_quote( $url, '/' ) ), '', $content ); // Then clear URLs only
			} else {
				$content = preg_replace( '/\[audio.*?\](\[\\/audio\])?/', '', $content );
			}
		}

	} // Video post
	else if ( has_post_format( 'video' ) ) {

		$urls = wp_extract_urls( $content );

		if ( ! empty( $urls ) ) {
			$url       = reset( $urls );
			$has_media = $url !== $wp_embed->autoembed( $url );

			$video_shortcode_regex = '/\[video.*?\](\[\\/video\])?/';

			// Remove first video shortcode
			if ( preg_match( $video_shortcode_regex, $content ) ) {
				$content = preg_replace( $video_shortcode_regex, '', $content );
			} // Replace known embeds
			else if ( $has_media ) {
				$content = preg_replace( sprintf( '/%s/', preg_quote( $url, '/' ) ), '', $content );
			}
		}
	}

	return $content;
}

/**
 * Change "href" for link post formats.
 *
 * @param string  $permalink
 * @param WP_Post $post
 *
 * @return string
 */
function _kalium_blog_post_format_link_url( $permalink, $post ) {
	if ( kalium_blog_is_external_url_post( $post ) ) {
		$urls = wp_extract_urls( get_the_content( null, false, $post ) );

		if ( ! empty( $urls ) ) {
			return $urls[0];
		}
	}

	return $permalink;
}

/**
 * Single post comments visibility.
 *
 * @param bool $visible
 *
 * @return bool
 */
function _kalium_blog_comments_visibility( $visible ) {
	return kalium_blog_get_option( 'single/post_comments' );
}
