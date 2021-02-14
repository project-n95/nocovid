<?php
/**
 * Kalium WordPress Theme
 *
 * Core hook functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Enqueue styles.
 *
 * @return void
 */
function _kalium_enqueue_styles() {

	// Enqueue Kalium theme scripts and styles
	kalium_enqueue( 'theme-style' );

	// Somebody don't want to include style.css of the theme
	if ( ! wp_validate_boolean( kalium_get_theme_option( 'do_not_enqueue_style_css', true ) ) ) {
		kalium_enqueue( 'style-css' );
	}

	// Use custom skin
	if ( kalium_get_theme_option( 'use_custom_skin' ) ) {
		kalium_use_filebased_custom_skin_maybe_generate();
		kalium_use_filebased_custom_skin_enqueue();
	}
}

/**
 * Enqueue Kalium scripts and libraries.
 *
 * @return void
 */
function _kalium_enqueue_scripts() {

	// Built-in jQuery
	wp_enqueue_script( 'jquery' );

	// GSAP library
	kalium_enqueue_gsap_library();

	// ScrollMagic library
	kalium_enqueue_scrollmagic_library();

	// Sticky Header
	if ( kalium_get_theme_option( 'sticky_header' ) ) {
		kalium_enqueue_sticky_header();
	}

	// Single post enqueue
	if ( is_single() ) {

		// Fluidbox
		kalium_enqueue( 'fluidbox' );

		// Comment reply script
		if ( comments_open() ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}

/**
 * Enqueue Kalium Main JS file.
 *
 * @return void
 */
function _kalium_enqueue_main_js_file() {
	kalium_enqueue( 'main-js' );
}

/**
 * Custom JavaScript in head and footer.
 *
 * @return void
 */
function _kalium_wp_head_custom_js() {

	// Custom JavaScript in Header
	$user_custom_js_head = kalium_get_theme_option( 'user_custom_js_head' );

	if ( ! empty( $user_custom_js_head ) ) {
		if ( ! preg_match( "/\<\w+/", $user_custom_js_head ) ) {
			$user_custom_js_head = '<script> ' . $user_custom_js_head . ' </script>';
		}

		echo $user_custom_js_head;
	}
}

/**
 * Custom User JavaScript print in the end.
 *
 * @return void
 */
function _kalium_wp_footer_custom_js() {

	// Custom JavaScript in Footer
	$user_custom_js = kalium_get_theme_option( 'user_custom_js' );

	if ( ! empty( $user_custom_js ) ) {
		if ( ! preg_match( "/\<\w+/", $user_custom_js ) ) {
			$user_custom_js = sprintf( '<script>%s</script>', $user_custom_js );
		}

		echo $user_custom_js;
	}
}

/**
 * Theme widgets init.
 *
 * @return void
 */
function _kalium_widgets_init() {

	// Widget wrappers
	$before_widget = '<div id="%1$s" class="widget %2$s">';
	$after_widget  = '</div>';

	// Core widgets
	$widgets = [
		// Blog Sidebar
		[
			'id'   => 'blog_sidebar',
			'name' => 'Blog Archive',
		],
		// Sidebar on single post
		[
			'id'   => 'blog_sidebar_single',
			'name' => 'Single Post',
		],
		// Footer Sidebar
		[
			'id'   => 'footer_sidebar',
			'name' => 'Footer',
		],
		// Top Menu Sidebar
		[
			'id'   => 'top_menu_sidebar',
			'name' => 'Top Menu',
		],
		// Sidebar Menu Widgets
		[
			'id'   => 'sidebar_menu_sidebar',
			'name' => 'Sidebar Menu',
		],
		// Shop Sidebar
		[
			'id'   => 'shop_sidebar',
			'name' => 'Shop Archive',
		],
		// Sidebar on single post
		[
			'id'   => 'shop_sidebar_single',
			'name' => 'Single Product',
		],
	];

	// Load sidebars (when the plugin is inactive)
	if ( ( $custom_sidebars = get_option( 'cs_sidebars', null ) ) && false == kalium()->is->plugin_active( 'custom-sidebars/customsidebars.php' ) ) {
		foreach ( $custom_sidebars as $widget ) {
			$widgets[] = [
				'id'          => $widget['id'],
				'name'        => $widget['name'],
				'description' => 'Inherited from Custom Sidebars plugin'
			];
		}
	}

	// Kalium Widgets Filter
	$widgets = apply_filters( 'kalium_widgets_array', $widgets );

	// Initialize widgets
	foreach ( $widgets as $widget ) {
		register_sidebar( [
			'id'            => $widget['id'],
			'name'          => $widget['name'],
			'before_widget' => $before_widget,
			'after_widget'  => $after_widget,
			'description'   => kalium_get_array_key( $widget, 'description' )
		] );
	}
}

/**
 * Parse footer styles.
 *
 * @return void
 */
function _kalium_append_custom_css() {
	global $kalium_append_custom_css;

	if ( empty( $kalium_append_custom_css ) ) {
		return;
	}

	echo sprintf( '<style data-appended-custom-css="true">%s</style>', kalium_compress_text( implode( "\n\n", $kalium_append_custom_css ) ) );
}

/**
 * Append content to the footer.
 *
 * @return void
 */
function _kalium_append_footer_html() {
	global $kalium_append_footer_html;

	if ( ! empty( $kalium_append_footer_html ) ) {
		echo implode( PHP_EOL, $kalium_append_footer_html );
	}
}

/**
 * Print scripts in the header.
 *
 * @return void
 */
function _kalium_wp_print_scripts() {
	?>
    <script type="text/javascript">
		var ajaxurl = ajaxurl || '<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>';
		<?php if ( defined( 'ICL_LANGUAGE_CODE' ) ) : ?>
		var icl_language_code = <?php echo json_encode( ICL_LANGUAGE_CODE ); ?>;
		<?php endif; ?>
    </script>
	<?php
}

/**
 * Handler function for Endless Pagination via AJAX.
 *
 * @return void
 */
function _kalium_endless_pagination_get_paged_items() {
	$response = [
		'hasMore'        => false,
		'hasItems'       => false,
		'hasQueryFilter' => false,
	];

	$loop_handler  = kalium()->request->xhr_input( 'loop-handler' );
	$loop_template = kalium()->request->xhr_input( 'loop-template' );
	$base_query    = kalium()->request->xhr_input( 'base-query' );
	$args          = kalium()->request->xhr_input( 'args' );
	$pagination    = kalium()->request->xhr_input( 'pagination' );
	$query_filter  = kalium()->request->xhr_input( 'query-filter' );

	// Execute attached "pre" actions
	do_action( 'kalium_endless_pagination_pre_get_paged_items', $args );

	// Query
	$fetched_ids    = array_map( 'absint', $pagination['fetchedItems'] );
	$posts_per_page = absint( $pagination['perPage'] );
	$total_items    = absint( $pagination['totalItems'] );

	$wp_query_args = (array) $base_query;

	// Extra query filter
	if ( ! empty( $query_filter ) && is_array( $query_filter ) ) {
		$wp_query_args = array_merge( $wp_query_args, $query_filter );

		$response['hasQueryFilter'] = true;
	}

	// Set pagination data
	$wp_query_args = array_merge( $wp_query_args, [
		'post_status'    => 'publish',
		'posts_per_page' => $posts_per_page,
		'post__not_in'   => $fetched_ids,
	] );

	// Custom loop handler
	if ( $loop_handler ) {
		wp_send_json_success( call_user_func( $loop_handler, $posts_per_page, $total_items, $fetched_ids, $wp_query_args ) );
	}

	query_posts( $wp_query_args );

	// Load items
	if ( have_posts() ) {
		$new_fetched_ids = [];

		ob_start();

		// Posts loop
		while ( have_posts() ) {
			the_post();

			// Fetched ID
			$new_fetched_ids[] = get_the_id();

			// Loop template
			if ( function_exists( $loop_template ) ) {
				call_user_func( $loop_template );
			}
		}

		// Reset query
		wp_reset_postdata();
		wp_reset_query();

		$response['fetchedItems'] = $new_fetched_ids;
		$response['items']        = ob_get_clean();
		$response['hasMore']      = count( $fetched_ids ) + count( $new_fetched_ids ) < $total_items;
		$response['hasItems']     = true;
	}

	wp_send_json_success( $response );
}

/**
 * Kalium image placeholders style.
 *
 * @return void
 */
function _kalium_image_placeholder_set_style() {

	// Placeholder color
	$background_color = kalium_get_theme_option( 'image_loading_placeholder_bg' );

	if ( ! empty( $background_color ) ) {
		kalium()->images->set_placeholder_color( $background_color );
	}

	// Placeholder gradient color
	if ( kalium_get_theme_option( 'image_loading_placeholder_use_gradient' ) ) {
		kalium()->images->set_placeholder_gradient( $background_color, kalium_get_theme_option( 'image_loading_placeholder_gradient_bg' ), kalium_get_theme_option( 'image_loading_placeholder_gradient_type' ) );
	}

	// Placeholder dominant color
	if ( kalium_get_theme_option( 'image_loading_placeholder_dominant_color' ) ) {
		kalium()->images->use_dominant_color();
	}

	// Images Fluid Width
	kalium()->images->set_fluid_width( apply_filters( 'kalium_images_fluid_width', true ) );

	// Set loader types
	switch ( kalium_get_theme_option( 'image_loading_placeholder_type' ) ) {

		// Preselected
		case 'preselected':
			// Select spinner to use
			$spinner_id = kalium_get_theme_option( 'image_loading_placeholder_preselected_loader' );

			kalium()->images->set_loading_spinner( $spinner_id, array(
				'holder'    => 'span',
				'alignment' => kalium_get_theme_option( 'image_loading_placeholder_preselected_loader_position' ),
				'spacing'   => kalium_get_theme_option( 'image_loading_placeholder_preselected_spacing' ),
				'color'     => kalium_get_theme_option( 'image_loading_placeholder_preselected_loader_color' ),
				'scale'     => intval( kalium_get_theme_option( 'image_loading_placeholder_preselected_size' ) ) / 100,
			) );
			break;

		// Custom preloader
		case 'custom':
			$loader_image = kalium_get_theme_option( 'image_loading_placeholder_custom_image' );

			if ( $loader_image ) {
				$loader_image_width = kalium_get_theme_option( 'image_loading_placeholder_custom_image_width' );
				$loader_position    = kalium_get_theme_option( 'image_loading_placeholder_custom_loader_position' );
				$loader_spacing     = kalium_get_theme_option( 'image_loading_placeholder_custom_spacing' );

				kalium()->images->set_custom_preloader( $loader_image, array(
					'width'     => $loader_image_width,
					'alignment' => $loader_position,
					'spacing'   => $loader_spacing
				) );
			}
			break;
	}
}

/**
 * Get Google API Key Array for ACF.
 *
 * @return array
 */
function _kalium_google_api_key_acf() {
	return [
		'libraries' => 'places',
		'key'       => kalium_get_google_api(),
	];
}

/**
 * Set sidebar skin classes.
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_set_widgets_classes( $classes = [] ) {
	$skin = kalium_get_theme_option( 'sidebar_skin' );

	if ( in_array( $skin, [ 'bordered', 'background-fill' ] ) ) {
		$classes[] = sprintf( 'widget-area--skin-%s', $skin );
	}

	return $classes;
}

/**
 * Assign footer classes.
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_footer_classes( $classes ) {
	$fixed        = kalium_get_theme_option( 'footer_fixed' );
	$full_width   = kalium_get_theme_option( 'footer_fullwidth' );
	$style        = kalium_get_theme_option( 'footer_style' );
	$bottom_style = kalium_get_theme_option( 'footer_bottom_style' );

	$classes[] = 'footer-bottom-' . $bottom_style;

	if ( $fixed ) {
		$classes[] = 'fixed-footer';

		if ( $fixed == 'fixed-fade' ) {
			$classes[] = 'fixed-footer-fade';
		} else if ( $fixed == 'fixed-slide' ) {
			$classes[] = 'fixed-footer-slide';
		}
	}

	if ( $style ) {
		$classes[] = 'site-footer-' . $style;
		$classes[] = 'main-footer-' . $style; // Deprecated
	}

	// Full-width footer
	if ( $full_width ) {
		$classes[] = 'footer-fullwidth';
	}

	return $classes;
}

/**
 * Kalium get default excerpt length.
 */
function _kalium_get_default_excerpt_length() {
	return apply_filters( 'kalium_get_default_excerpt_length', 55 );
}

/**
 * Excerpt more dots.
 */
function _kalium_get_default_excerpt_more() {
	return apply_filters( 'kalium_get_default_excerpt_more', '&hellip;' );
}

/**
 * Kalium admin bar item.
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function _kalium_admin_bar_entry( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$plugin_updates = kalium_plugin_updates_count();

	// Add Admin Bar Menu Links
	$wp_admin_bar->add_menu( [
		'id'    => 'laborator-options',
		'title' => sprintf( '<span class="ab-icon">%1$s</span><span class="ab-label">%2$s</span>', get_laborator_admin_menu_logo(), wp_get_theme() ),
		'href'  => is_admin() ? home_url() : admin_url( 'admin.php?page=laborator_options' ),
		'meta'  => [ 'target' => is_admin() ? '_blank' : '_self' ]
	] );

	$wp_admin_bar->add_menu( [
		'parent' => 'laborator-options',
		'id'     => 'laborator-options-theme',
		'title'  => 'Theme Options',
		'href'   => admin_url( 'admin.php?page=laborator_options' )
	] );

	$wp_admin_bar->add_menu( [
		'parent' => 'laborator-options',
		'id'     => 'laborator-options-typolab',
		'title'  => 'Typography',
		'href'   => admin_url( 'admin.php?page=typolab' )
	] );

	if ( $plugin_updates > 0 ) {
		$wp_admin_bar->add_menu( [
			'parent' => 'laborator-options',
			'id'     => 'install-plugins',
			'title'  => sprintf( 'Update Plugins <span class="kalium-update-badge">%d</span>', $plugin_updates ),
			'href'   => Kalium_About::get_tab_link( 'plugins' ),
		] );
	}

	$wp_admin_bar->add_menu( [
		'parent' => 'laborator-options',
		'id'     => 'laborator-custom-css',
		'title'  => 'Custom CSS',
		'href'   => admin_url( 'admin.php?page=laborator_custom_css' )
	] );

	$wp_admin_bar->add_menu( [
		'parent' => 'laborator-options',
		'id'     => 'kalium-demos',
		'title'  => 'Demos',
		'href'   => admin_url( 'admin.php?page=kalium&tab=demos' )
	] );

	$wp_admin_bar->add_menu( [
		'parent' => 'laborator-options',
		'id'     => 'laborator-help',
		'title'  => 'Help',
		'href'   => Kalium_About::get_tab_link( 'help' ),
	] );

	// Network Admin Links
	if ( ! is_admin() ) {
		$wp_admin_bar->add_menu( [
			'parent' => 'site-name',
			'id'     => 'site-name-themeoptions',
			'title'  => 'Theme Options',
			'href'   => admin_url( 'admin.php?page=laborator_options' ),
		] );

		$wp_admin_bar->add_menu( [
			'parent' => 'site-name',
			'id'     => 'site-name-typolab',
			'title'  => 'Typography',
			'href'   => admin_url( 'admin.php?page=typolab' ),
		] );
	}
}

/**
 * Laborator admin menu item class.
 *
 * @param array $menu
 *
 * @return array
 */
function _laborator_options_admin_menu_icon( $menu ) {

	foreach ( $menu as $i => & $menu_item ) {

		// Laborator options
		if ( 'laborator_options' === $menu_item[2] ) {
			$menu_item[0] = get_laborator_admin_menu_logo() . $menu_item[0];
			$menu_item[4] .= ' menu-item-laborator';
			$menu_item[6] = '';
		}
	}

	return $menu;
}

/**
 * Go to Top Feature.
 *
 * @return void
 */
function _kalium_go_to_top_link() {
	if ( ! kalium_get_theme_option( 'footer_go_to_top' ) ) {
		return;
	}

	$activate_when = kalium_get_theme_option( 'footer_go_to_top_activate' );
	$button_type   = kalium_get_theme_option( 'footer_go_to_top_type' );
	$position      = kalium_get_theme_option( 'footer_go_to_top_position' );

	// Type
	$type = 'pixels';

	if ( strpos( $activate_when, '%' ) ) {
		$type = 'percentage';
	} else if ( trim( strtolower( $activate_when ) ) === 'footer' ) {
		$type = 'footer';
	}

	// Value
	$value = in_array( $type, [ 'pixels', 'percentage' ] ) ? intval( $activate_when ) : $activate_when;

	// Classes
	$classes = [
		'go-to-top',
		'position-' . $position,
	];

	// Shape
	if ( 'circle' === $button_type ) {
		$classes[] = 'rounded';
	}

	?>
    <a href="#top" <?php kalium_class_attr( $classes ); ?> data-type="<?php echo esc_attr( $type ); ?>" data-val="<?php echo esc_attr( $value ); ?>">
        <i class="flaticon-bottom4"></i>
    </a>
	<?php
}

/**
 * Page Custom CSS.
 *
 * @return void
 */
function _kalium_page_custom_css() {
	$queried_object_id = kalium_get_queried_object_id();

	if ( $queried_object_id && ( $page_custom_css = kalium()->acf->get_field( 'page_custom_css', $queried_object_id ) ) ) {
		$page_custom_css = str_replace( '{{ID}}', $queried_object_id, $page_custom_css );
		$page_custom_css = sprintf( '<style data-page-custom-css="true">%s</style>', $page_custom_css );

		if ( apply_filters( 'kalium_page_custom_css_append_header', true ) ) {
			add_action( 'wp_print_styles', kalium_hook_echo_value( $page_custom_css ) );
		} else {
			add_action( 'wp_footer', kalium_hook_echo_value( $page_custom_css ) );
		}
	}
}

/**
 * Add open graph meta in header.
 *
 * @return void
 */
function _kalium_wp_head_open_graph_meta() {

	// Only show if open graph meta is allowed
	if ( ! apply_filters( 'kalium_open_graph_meta', true ) || ! is_singular() ) {
		return;
	}

	// Current post
	$post = get_queried_object();

	// Excerpt, clean styles
	$excerpt = kalium_clean_excerpt( get_the_excerpt(), true );
	?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>">
    <meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">

	<?php if ( $excerpt ) : ?>
        <meta property="og:description" content="<?php echo esc_attr( $excerpt ); ?>">
	<?php endif; ?>

	<?php if ( has_post_thumbnail( $post ) ) : $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'original' ); ?>
        <meta property="og:image" content="<?php echo esc_url( $image[0] ); ?>">
        <link itemprop="image" href="<?php echo esc_url( $image[0] ); ?>">

		<?php if ( apply_filters( 'kalium_meta_google_thumbnail', true ) ) : $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'thumbnail' ); ?>
            <!--
		  <PageMap>
		    <DataObject type="thumbnail">
		      <Attribute name="src" value="<?php echo esc_url( $image[0] ); ?>"/>
		      <Attribute name="width" value="<?php echo esc_attr( $image[1] ); ?>"/>
		      <Attribute name="height" value="<?php echo esc_attr( $image[2] ); ?>"/>
		    </DataObject>
		  </PageMap>
		-->
		<?php endif; ?>

	<?php endif;
}

/**
 * Grid container custom width.
 *
 * @return void
 */
function _kalium_grid_container_max_width() {
	$grid_container_width     = kalium_get_theme_option( 'grid_container_width' );
	$grid_container_fullwidth = kalium_get_theme_option( 'grid_container_fullwidth' );

	// Custom in-page container width
	$queried_object_id = kalium_get_queried_object_id();

	if ( is_singular() && kalium_get_field( 'custom_grid_container_width', $queried_object_id ) ) {
		$grid_container_width     = kalium_get_field( 'grid_container_width', $queried_object_id );
		$grid_container_fullwidth = kalium_get_field( 'grid_container_fullwidth', $queried_object_id );
	}

	// Full width container
	if ( $grid_container_fullwidth ) {
		$grid_container_width = 0;
	}

	// Set container width
	if ( is_numeric( $grid_container_width ) ) {
		$grid_container_width = abs( $grid_container_width );
		$unit                 = 'px';
		$min_width            = 1200;

		// Selectors
		$selectors = [
			'.container',
			'.content-area',
			'.vc-container .vc-row-container--stretch-content .vc_inner.container-fixed',
		];

		// CSS props
		$css_props = [];

		// 100% width
		if ( 0 === $grid_container_width ) {
			$grid_container_width = 100;
			$unit                 = '%';
		}

		// Width prop
		$css_props[] = 'width: ' . $grid_container_width . $unit;

		// Width prop with calc()
		if ( 100 === $grid_container_width && '%' === $unit ) {
			$css_props[] = 'width: calc(100% - 60px)';
		}

		// Set minimum breaking point
		if ( 'px' === $unit ) {
			$min_width = $grid_container_width + 30;
		}

		// Custom container width
		echo sprintf(
			'<style data-grid-container-width> @media (min-width: %1$spx){ %2$s { %3$s } }</style>',
			$min_width,
			implode( ',', array_map( 'esc_attr', $selectors ) ),
			implode( ';', $css_props )
		);
	}
}

/**
 * Custom breadcrumb placement on certain page types.
 *
 * @return void
 */
function _kalium_custom_breadcrumb_placement() {
	if ( ! kalium()->is->breadcrumb_navxt_active() ) {
		return;
	}

	$breadcrumb_template_function = 'kalium_breadcrumb_display';

	$remove_default_breadcrumb_hook = function () use ( $breadcrumb_template_function ) {
		remove_action( 'kalium_wrapper_start', $breadcrumb_template_function, 20 );
	};

	// Single portfolio pages
	if ( is_singular( 'portfolio' ) ) {
		$item_type = kalium_get_field( 'item_type', kalium_get_queried_object_id() );

		// Side type
		if ( 'type-1' === $item_type ) {

			// Full background
			if ( 'fullbg' === kalium_get_field( 'gallery_type' ) ) {
				$remove_default_breadcrumb_hook();
				add_action( 'kalium_portfolio_type_side_portfolio_before_title', $breadcrumb_template_function );
			}
		} // Columned type
		else if ( 'type-2' === $item_type ) {

			if ( kalium_get_field( 'show_featured_image', kalium_get_queried_object_id() ) && 'absolute' === kalium_header_get_option( 'position' ) ) {
				$remove_default_breadcrumb_hook();
				add_action( 'kalium_portfolio_type_columned_before_gallery', $breadcrumb_template_function );
				add_filter( 'kalium_breadcrumb_force_show', '__return_true' );
			}
		} // Fullscreen type
		else if ( 'type-5' === $item_type ) {
			$remove_default_breadcrumb_hook();
			add_action( 'kalium_portfolio_type_fullscreen_before_title', $breadcrumb_template_function );
		}
	} // Single post
	else if ( is_singular( 'post' ) ) {

		// Featured image placement
		$post_image_placement = kalium_blog_get_option( 'single/post_image/placement' );

		// Fullwidth image
		if ( 'full-width' === $post_image_placement ) {
			$remove_default_breadcrumb_hook();
			add_action( 'kalium_blog_single_post_details', $breadcrumb_template_function, 5 );
			add_filter( 'kalium_breadcrumb_force_show', '__return_true' );
		}
	}
}

/**
 * Exclude post types from search.
 *
 * @param WP_Query $query
 *
 * @since 3.1.3
 */
function _kalium_exclude_post_types_from_search( $query ) {
	if ( $exclude_post_types = kalium_get_theme_option( 'exclude_search_post_types', [] ) ) {
		$exclude_post_types = array_keys( array_filter( $exclude_post_types ) );

		if ( ! is_admin() && ! empty( $exclude_post_types ) && $query->is_main_query() && $query->is_search() ) {
			$post_types = get_post_types( [
				'public'             => true,
				'publicly_queryable' => true,
				'_builtin'           => false,
			] );

			// Assign "post" post type automattically
			$post_types['post'] = 'post';

			// Allowed post types
			$allowed_post_types = array_diff( array_values( $post_types ), $exclude_post_types );

			// Set query
			$query->set( 'post_type', $allowed_post_types );
		}
	}
}
