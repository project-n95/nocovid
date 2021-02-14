<?php
/**
 *    Kalium WordPress Theme
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Like feature for portfolio items.
 */
function laborator_update_like_count() {
	$output = [
		'liked' => false,
		'count' => 0,
	];

	$post_id = intval( $_GET['post_id'] );
	$user_ip = get_the_user_ip();

	if ( filter_var( $post_id, FILTER_VALIDATE_INT ) ) {
		$the_post = get_post( $post_id );

		if ( $the_post ) {
			$likes = $the_post->post_likes;
			$likes = is_array( $likes ) ? $likes : [];

			if ( ! in_array( $user_ip, $likes ) ) {
				// Like Post
				$output['liked'] = true;

				$likes[]         = $user_ip;
				$output['count'] = count( $likes );

				update_post_meta( $post_id, 'post_likes', $likes );
			} else {
				// Unlike Post
				$output['liked'] = false;

				$key = array_search( $user_ip, $likes );

				if ( false !== $key ) {
					unset( $likes[ $key ] );
				}

				$output['count'] = count( $likes );

				update_post_meta( $post_id, 'post_likes', $likes );
			}

			if ( function_exists( 'wp_cache_post_change' ) ) {
				wp_cache_post_change( $post_id );
			}
		}
	}

	echo json_encode( $output );
	exit();
}

add_action( 'wp_ajax_laborator_update_likes', 'laborator_update_like_count' );
add_action( 'wp_ajax_nopriv_laborator_update_likes', 'laborator_update_like_count' );

/**
 * Portfolio pagination in archive page.
 */
function kalium_portfolio_user_pagination( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'portfolio' ) ) {
		$portfolio_args = kalium_get_portfolio_query( [ 'no_query' => true ] );
		$query->set( 'posts_per_page', $portfolio_args['per_page'] );
	}
}

add_action( 'pre_get_posts', 'kalium_portfolio_user_pagination' );

/**
 * Blog page content.
 */
function kalium_blog_page_content_for_vc( $query ) {

	if ( ! is_admin() && $query->is_posts_page ) {
		add_action( 'kalium_header_main_heading_title_before', 'kalium_blog_archive_vc_content' );
	}
}

function kalium_blog_archive_vc_content() {
	$blog_page       = get_queried_object();
	$is_vc_container = preg_match( "/\[vc_row.*?\]/i", $blog_page->post_content );

	if ( $is_vc_container ) {
		?>
        <div class="vc-container">
			<?php echo apply_filters( 'the_content', $blog_page->post_content ); ?>
        </div>
		<?php
	}
}

add_action( 'pre_get_posts', 'kalium_blog_page_content_for_vc' );

/**
 * Create font size groups.
 */
function kalium_font_size_groups() {

	// Headings
	$headings = [
		'H1' => 'h1',
		'H2' => 'h2',
		'H3' => 'h3',
		'H4' => 'h4',
		'H5' => 'h5',
		'H6' => 'h6'
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Headings', 'Set font size for the headings and page titles.', $headings );

	// Paragraphs
	$paragraphs = [
		'P' => 'body, p, .section-title p, .single-portfolio-holder .details .project-description p'
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Paragraphs', 'Set font size for paragraphs and general body class.', $paragraphs );

	// Header
	$header = [
		'Default Text'   => '.site-header .header-block__item',
		'Top Header Bar' => '.site-header .top-header-bar .header-block__item',
	];

	TypoLab_Font_Sizes::add_font_size_group( 'Header', 'Set font size for header and elements in the header.', $header );

	// Standard Menu
	$first_level   = '.main-header.menu-type-standard-menu .standard-menu-container div.menu>ul>li>a, .main-header.menu-type-standard-menu .standard-menu-container ul.menu>li>a';
	$submenu_level = '.main-header.menu-type-standard-menu .standard-menu-container div.menu>ul ul li a, .main-header.menu-type-standard-menu .standard-menu-container ul.menu ul li a';

	$standard_menu = [
		'Main Menu Items' => $first_level,
		'Sub Menu Items'  => $submenu_level
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Standard Menu', 'Set font size for menu and submenu items for Standard Menu type.', $standard_menu );

	// Fullscreen Menu
	$first_level   = '.main-header.menu-type-full-bg-menu .fullscreen-menu nav ul li a';
	$submenu_level = '.main-header.menu-type-full-bg-menu .fullscreen-menu nav div.menu>ul ul li a, .main-header.menu-type-full-bg-menu .fullscreen-menu nav ul.menu ul li a';

	$fullscreen_menu = [
		'Main Menu Items' => $first_level,
		'Sub Menu Items'  => $submenu_level
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Fullscreen Menu', 'Set font size for menu and submenu items for Fullscreen Menu type.', $fullscreen_menu );

	// Top Menu
	$first_level   = '.top-menu-container .top-menu ul li a';
	$submenu_level = '.top-menu div.menu>ul>li ul>li>a, .top-menu ul.menu>li ul>li>a';
	$widgets_title = '.top-menu-container .widget h3';
	$widgets_text  = '.top-menu-container .widget, .top-menu-container .widget p, .top-menu-container .widget div';

	$top_menu = [
		'Main Menu Items' => $first_level,
		'Sub Menu Items'  => $submenu_level,
		'Widgets Title'   => $widgets_title,
		'Widgets Content' => $widgets_text
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Top Menu', 'Set font size for menu and submenu items for Top Menu type.', $top_menu );

	// Sidebar Menu
	$first_level   = '.sidebar-menu-wrapper .sidebar-menu-container .sidebar-main-menu div.menu>ul>li>a, .sidebar-menu-wrapper .sidebar-menu-container .sidebar-main-menu ul.menu>li>a';
	$submenu_level = '.sidebar-menu-wrapper .sidebar-menu-container .sidebar-main-menu div.menu>ul li ul li:hover>a, .sidebar-menu-wrapper .sidebar-menu-container .sidebar-main-menu ul.menu li ul li>a';
	$widgets_title = '.sidebar-menu-wrapper .sidebar-menu-container .sidebar-menu-widgets .widget .widget-title';
	$widgets_text  = '.sidebar-menu-wrapper .widget, .sidebar-menu-wrapper .widget p, .sidebar-menu-wrapper .widget div';

	$sidebar_menu = [
		'Main Menu Items' => $first_level,
		'Sub Menu Items'  => $submenu_level,
		'Widgets Title'   => $widgets_title,
		'Widgets Content' => $widgets_text
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Sidebar Menu', 'Set font size for menu and submenu items for Sidebar Menu type.', $sidebar_menu );

	// Mobile Menu
	$first_level   = '.mobile-menu-wrapper .mobile-menu-container div.menu>ul>li>a, .mobile-menu-wrapper .mobile-menu-container ul.menu>li>a, .mobile-menu-wrapper .mobile-menu-container .cart-icon-link-mobile-container a, .mobile-menu-wrapper .mobile-menu-container .search-form input';
	$submenu_level = '.mobile-menu-wrapper .mobile-menu-container div.menu>ul>li ul>li>a, .mobile-menu-wrapper .mobile-menu-container ul.menu>li ul>li>a';

	$mobile_menu_menu = [
		'Main Menu Items' => $first_level,
		'Sub Menu Items'  => $submenu_level
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Mobile Menu', 'Set font size for menu and submenu items for Mobile Menu type.', $mobile_menu_menu );

	// Portfolio
	$portfolio_title            = '.portfolio-holder .thumb .hover-state .info h3, .portfolio-holder .item-box .info h3';
	$portfolio_categories       = '.portfolio-holder .thumb .hover-state .info p, .portfolio-holder .item-box .info h3';
	$portfolio_title_single     = '.single-portfolio-holder .title h1, .single-portfolio-holder.portfolio-type-5 .portfolio-description-container .portfolio-description-showinfo h3';
	$portfolio_subtiles         = '.single-portfolio-holder .section-title p';
	$portfolio_content          = '.portfolio-description-showinfo p, .single-portfolio-holder .details .project-description p, .gallery-item-description .post-formatting p';
	$portfolio_services_title   = '.single-portfolio-holder .details .services h3';
	$portfolio_services_content = '.single-portfolio-holder .details .services ul li';
	$portfolio_viewsite_link    = '.single-portfolio-holder .details .link';

	$portfolio = [
		'Titles'            => $portfolio_title,
		'Categories'        => $portfolio_categories,
		'Single Title'      => $portfolio_title_single,
		'Subtitles'         => $portfolio_subtiles,
		'Portfolio Content' => $portfolio_content,
		'Services Title'    => $portfolio_services_title,
		'Services Content'  => $portfolio_services_content,
		'Launch Link'       => $portfolio_viewsite_link,
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Portfolio', 'Set font sizes for portfolio section.', $portfolio );

	// Shop
	if ( kalium()->is->woocommerce_active() ) {
		$shop_title           = '.woocommerce .product .item-info h3 a, .woocommerce .product .item-info .price ins, .woocommerce .product .item-info .price>.amount';
		$shop_title_single    = '.woocommerce .item-info h1, .woocommerce .single-product .summary .single_variation_wrap .single_variation>.price>.amount, .woocommerce .single-product .summary div[itemprop=offers]>.price>.amount';
		$shop_categories      = '.woocommerce .product.catalog-layout-transparent-bg .item-info .product-terms a';
		$shop_product_content = '.woocommerce .item-info p, .woocommerce .item-info .product_meta, .woocommerce .single-product .summary .variations .label label';
		$shop_buttons         = '.woocommerce .item-info .group_table .button, .woocommerce .item-info form.cart .button';

		$shop = [
			'Titles'          => $shop_title,
			'Single Title'    => $shop_title_single,
			'Categories'      => $shop_categories,
			'Product Content' => $shop_product_content,
			'Buttons'         => $shop_buttons,
		];
		TypoLab_Font_Sizes::add_font_size_group( 'Shop', 'Set font sizes for shop section.', $shop );
	}

	// Blog
	$post_title_loop   = '.blog-posts .box-holder .post-info h2, .wpb_wrapper .lab-blog-posts .blog-post-entry .blog-post-content-container .blog-post-title';
	$post_title_single = '.single-blog-holder .blog-title h1';
	$post_excerpt      = '.blog-post-excerpt p, .post-info p';
	$post_content      = '.blog-content-holder .post-content';

	$blog = [
		'Titles'       => $post_title_loop,
		'Single Title' => $post_title_single,
		'Post Excerpt' => $post_excerpt,
		'Post Content' => $post_content
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Blog', 'Set font sizes for blog titles and content.', $blog );

	// Footer
	$widgets_title = '.site-footer .footer-widgets .widget h1, .site-footer .footer-widgets .widget h2, .site-footer .footer-widgets .widget h3';
	$widgets_text  = '.site-footer .footer-widgets .widget .textwidget, .site-footer .footer-widgets .widget p';
	$copyrights    = '.copyrights, .site-footer .footer-bottom-content a, .site-footer .footer-bottom-content p';

	$footer = [
		'Widgets Title'   => $widgets_title,
		'Widgets Content' => $widgets_text,
		'Copyrights'      => $copyrights
	];
	TypoLab_Font_Sizes::add_font_size_group( 'Footer', 'Set font sizes for footer elements.', $footer );
}

add_action( 'typolab_add_font_size_groups', 'kalium_font_size_groups' );

/**
 * Mobile menu breakpoint.
 */
function kalium_mobile_menu_breakpoint() {
	$breakpoint     = kalium_get_mobile_menu_breakpoint();
	$breakpoint_one = $breakpoint + 1;

	$media_min = "screen and (min-width:{$breakpoint_one}px)";
	$media_max = "screen and (max-width:{$breakpoint}px)";

	echo sprintf( '<script>var mobile_menu_breakpoint = %s;</script>', $breakpoint );

	// Hide elements outside of mobile menu breakpoint
	$breakpoint_outside_hide   = [];
	$breakpoint_outside_hide[] = '.mobile-menu-wrapper';
	$breakpoint_outside_hide[] = '.mobile-menu-overlay';
	$breakpoint_outside_hide[] = '.header-block__item--mobile-menu-toggle';

	kalium_append_custom_css( implode( ',', $breakpoint_outside_hide ), 'display: none;', $media_min );

	// Hide elements inside of mobile menu breakpoint
	$breakpoint_inside_hide   = [];
	$breakpoint_inside_hide[] = '.header-block__item--standard-menu-container';

	kalium_append_custom_css( implode( ',', $breakpoint_inside_hide ), 'display: none;', $media_max );
}

add_action( 'wp_head', 'kalium_mobile_menu_breakpoint' );
