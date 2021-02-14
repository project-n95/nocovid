<?php
/**
 * Kalium WordPress Theme
 *
 * WP filters.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Add Do-shortcode for text widgets
 */
function execute_shortcodes_in_widget_text( $text ) {
	return do_shortcode( $text );
}

add_filter( 'widget_text', 'execute_shortcodes_in_widget_text' );

/**
 * Date shortcode.
 *
 * @param array  $atts
 * @param string $content
 *
 * @return string
 */
function laborator_shortcode_date( $atts = [], $content = '' ) {
	// Atts
	$atts = wp_parse_args( $atts, [
		'format' => get_option( 'date_format' ),
	] );

	return date_i18n( $atts['format'] );
}

if ( ! shortcode_exists( 'date' ) ) {
	add_shortcode( 'date', 'laborator_shortcode_date' );
}

/**
 * Shortcode for Social Networks [lab_social_networks]
 *
 * @param array  $atts
 * @param string $content
 *
 * @return string
 *
 * @deprecated 3.0
 */
function kalium_shortcode_deprecated_social_networks( $atts = [], $content = '' ) {
	$custom_icon = kalium_get_theme_option( 'social_network_custom_link_icon' );

	$social_order      = kalium_get_theme_option( 'social_order' );
	$social_order_list = apply_filters( 'kalium_social_networks_array', [
		'fb'  => [
			'title' => 'Facebook',
			'icon'  => 'fab fa-facebook'
		],
		'tw'  => [
			'title' => 'Twitter',
			'icon'  => 'fab fa-twitter'
		],
		'lin' => [
			'title' => 'LinkedIn',
			'icon'  => 'fab fa-linkedin'
		],
		'yt'  => [
			'title' => 'YouTube',
			'icon'  => 'fab fa-youtube'
		],
		'vm'  => [
			'title' => 'Vimeo',
			'icon'  => 'fab fa-vimeo'
		],
		'drb' => [
			'title' => 'Dribbble',
			'icon'  => 'fab fa-dribbble'
		],
		'ig'  => [
			'title' => 'Instagram',
			'icon'  => 'fab fa-instagram'
		],
		'pi'  => [
			'title' => 'Pinterest',
			'icon'  => 'fab fa-pinterest'
		],
		'vk'  => [
			'title' => 'VKontakte',
			'icon'  => 'fab fa-vk'
		],
		'fl'  => [
			'title' => 'Flickr',
			'icon'  => 'fab fa-flickr'
		],
		'be'  => [
			'title' => 'Behance',
			'icon'  => 'fab fa-behance'
		],
		'fs'  => [
			'title' => 'Foursquare',
			'icon'  => 'fab fa-foursquare'
		],
		'sk'  => [
			'title' => 'Skype',
			'icon'  => 'fab fa-skype'
		],
		'tu'  => [
			'title' => 'Tumblr',
			'icon'  => 'fab fa-tumblr'
		],
		'da'  => [
			'title' => 'DeviantArt',
			'icon'  => 'fab fa-deviantart'
		],
		'gh'  => [
			'title' => 'GitHub',
			'icon'  => 'fab fa-github'
		],
		'sc'  => [
			'title' => 'SoundCloud',
			'icon'  => 'fab fa-soundcloud'
		],
		'hz'  => [
			'title' => 'Houzz',
			'icon'  => 'fab fa-houzz'
		],
		'px'  => [
			'title'  => '500px',
			'icon'   => 'fab fa-500px',
			'prefix' => 'social',
		],
		'xi'  => [
			'title' => 'Xing',
			'icon'  => 'fab fa-xing'
		],
		'sp'  => [
			'title' => 'Spotify',
			'icon'  => 'fab fa-spotify'
		],
		'sn'  => [
			'title' => 'Snapchat',
			'icon'  => 'fab fa-snapchat-ghost',
			'dark'  => true
		],
		'em'  => [
			'title' => __( 'Email', 'kalium' ),
			'icon'  => 'far fa-envelope'
		],
		'yp'  => [
			'title' => 'Yelp',
			'icon'  => 'fab fa-yelp'
		],
		'ta'  => [
			'title' => 'TripAdvisor',
			'icon'  => 'fab fa-tripadvisor'
		],
		'tc'  => [
			'title' => 'Twitch',
			'icon'  => 'fab fa-twitch'
		],
		'wa'  => [
			'title' => 'WhatsApp',
			'icon'  => 'fab fa-whatsapp'
		],

		'custom' => [
			'title' => kalium_get_theme_option( 'social_network_custom_link_title' ),
			'href'  => kalium_get_theme_option( 'social_network_custom_link_link' ),
			'icon'  => $custom_icon ? $custom_icon : 'fas fa-plus',
		],
	] );

	// Social Networks Class
	$class = 'social-networks';

	if ( isset( $atts['class'] ) ) {
		$class .= ' ' . $atts['class'];
	}

	// Rounded Social Networks
	if ( is_array( $atts ) && in_array( 'rounded', $atts ) ) {
		$class .= ' rounded';
	} else {
		$class .= ' textual';
	}

	// Colored Text
	if ( is_array( $atts ) && ( in_array( 'colored', $atts ) || 'hover' == kalium_get_array_key( $atts, 'colored' ) ) ) {

		if ( is_array( $atts ) && 'hover' == kalium_get_array_key( $atts, 'colored' ) ) {
			$class .= ' colored-hover';
		} else {
			$class .= ' colored';
		}
	} // Colored Background
	else if ( is_array( $atts ) && ( in_array( 'colored-bg', $atts ) || 'hover' == kalium_get_array_key( $atts, 'colored-bg' ) ) ) {

		if ( is_array( $atts ) && 'hover' == kalium_get_array_key( $atts, 'colored-bg' ) ) {
			$class .= ' colored-bg-hover';
		} else {
			$class .= ' colored-bg';
		}
	}

	$html = '<ul class="' . esc_attr( $class ) . '">';

	foreach ( $social_order['visible'] as $key => $title ) {

		if ( $key == 'placebo' || ! isset( $social_order_list[ $key ] ) ) {
			continue;
		}

		$sn = $social_order_list[ $key ];

		$href  = kalium_get_theme_option( "social_network_link_{$key}" );
		$class = sanitize_title( $title );

		// Prefixed
		if ( isset( $sn['prefix'] ) ) {
			$class = "{$sn['prefix']}-" . $class;
		}

		if ( $key == 'custom' ) {
			$title = $sn['title'];
			$href  = $sn['href'];
			$class = 'custom';
		}

		$title_span = $title;

		if ( isset( $atts['class'] ) && strpos( $atts['class'], 'rounded' ) >= 0 ) {
			$title_span = $title;
		}

		$link_target = kalium_get_theme_option( 'social_networks_target_attr', '_blank' );

		if ( is_email( $href ) ) {
			$link_target = '_self';
			$subject     = kalium_get_theme_option( 'social_network_link_em_subject' );

			$href = "mailto:{$href}";

			if ( $subject ) {
				$href .= '?subject=' . esc_attr( $subject );
			}
		}

		// Dark Class
		if ( ! empty( $sn['dark'] ) ) {
			$class .= ' dark';
		}

		$html .= '<li>';
		$html .= '<a href="' . $href . '" target="' . $link_target . '" class="' . $class . '" title="' . $title . '" aria-label="' . esc_attr( $title ) . '" rel="noopener">';
		$html .= '<i class="' . $sn['icon'] . '"></i>';
		$html .= '<span class="name">' . apply_filters( 'kalium_social_networks_name', $title_span, $title ) . '</span>';
		$html .= '</a>';
		$html .= '</li>';
	}

	$html .= '</ul>';


	return apply_filters( 'kalium_shortcode_social_networks', $html );

}

add_shortcode( 'lab_social_networks', 'kalium_shortcode_deprecated_social_networks' );

/**
 * Excerpt Length & More
 */
function laborator_supershort_excerpt_length() {
	return 18;
}

/**
 * Footer visibility options.
 */
function kalium_footer_options_action() {
	if ( is_paged() ) {
		return;
	}

	// Post id
	$queried_object_id = kalium_get_queried_object_id();

	// In page settings
	if ( $queried_object_id ) {

		// Footer Visibility
		$footer_visibility = kalium_get_field( 'footer_visibility', $queried_object_id );

		if ( in_array( $footer_visibility, array( 'show', 'hide' ) ) ) {
			add_filter( 'kalium_show_footer', ( $footer_visibility == 'hide' ? '__return_false' : '__return_true' ), 10 );
		}

		// Fixed Footer
		$fixed_footer = kalium_get_field( 'fixed_footer', $queried_object_id );

		if ( in_array( $fixed_footer, array( 'normal', 'fixed', 'fixed-fade', 'fixed-slide' ) ) ) {

			if ( 'normal' === $fixed_footer ) {
				$fixed_footer = '';
			}

			add_filter( 'get_data_footer_fixed', kalium_hook_return_value( $fixed_footer ) );
		}

		// Footer width
		$footer_fullwidth = kalium_get_field( 'footer_fullwidth', $queried_object_id );

		if ( in_array( $footer_fullwidth, array( 'yes', 'no' ) ) ) {
			add_filter( 'get_data_footer_fullwidth', $footer_fullwidth == 'yes' ? '__return_true' : '__return_false' );
		}
	}
}

add_action( 'wp', 'kalium_footer_options_action' );

/**
 * Skin Compiler
 */
function laborator_custom_skin_generate( $data, $force_regenerate = false ) {

	if ( ! $force_regenerate ) {
		if ( ! defined( 'DOING_AJAX' ) ) {
			return $data;
		} elseif ( ! in_array( $_REQUEST['action'], [
			'of_ajax_post_action',
			'kalium_demos_import_content_pack',
		] ) ) {
			return $data;
		}
	} else {
		$data = kalium_get_theme_option();
	}

	if ( isset( $data['use_custom_skin'] ) && $data['use_custom_skin'] ) {
		update_option( 'kalium_skin_custom_css', '' );

		$colors = array();

		$custom_skin_bg_color        = $data['custom_skin_bg_color'];
		$custom_skin_link_color      = $data['custom_skin_link_color'];
		$custom_skin_headings_color  = $data['custom_skin_headings_color'];
		$custom_skin_paragraph_color = $data['custom_skin_paragraph_color'];
		$custom_skin_footer_bg_color = $data['custom_skin_footer_bg_color'];
		$custom_skin_borders_color   = $data['custom_skin_borders_color'];

		$custom_skin_bg_color        = $custom_skin_bg_color ? kalium_format_color_value( $custom_skin_bg_color ) : '#FFFFFF';
		$custom_skin_link_color      = $custom_skin_link_color ? kalium_format_color_value( $custom_skin_link_color ) : '#F6364D';
		$custom_skin_headings_color  = $custom_skin_headings_color ? kalium_format_color_value( $custom_skin_headings_color ) : '#F6364D';
		$custom_skin_paragraph_color = $custom_skin_paragraph_color ? kalium_format_color_value( $custom_skin_paragraph_color ) : '#777777';
		$custom_skin_footer_bg_color = $custom_skin_footer_bg_color ? kalium_format_color_value( $custom_skin_footer_bg_color ) : '#FAFAFA';
		$custom_skin_borders_color   = $custom_skin_borders_color ? kalium_format_color_value( $custom_skin_borders_color ) : '#EEEEEE';

		$files = array(
			kalium()->locate_file( 'assets/vendors/less-legacy/lesshat.less' )        => 'include',
			kalium()->locate_file( 'assets/vendors/less-legacy/skin-generator.less' ) => 'parse',
		);

		$vars = array(
			'bg-color'   => $custom_skin_bg_color,
			'link-color' => $custom_skin_link_color,
			'heading'    => $custom_skin_headings_color,
			'paragraph'  => $custom_skin_paragraph_color,
			'footer'     => $custom_skin_footer_bg_color,
			'border'     => $custom_skin_borders_color,
		);

		$css_style = kalium_generate_less_style( $files, $vars );

		update_option( 'kalium_skin_custom_css', $css_style );
		kalium_generate_custom_skin_file();
	}

	return $data;
}

add_filter( 'of_options_before_save', 'laborator_custom_skin_generate' );

/**
 * Remove Plugin Notices
 */
if ( defined( 'LS_PLUGIN_BASE' ) ) {
	remove_action( 'after_plugin_row_' . LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice', 10 );
}

/**
 * General Body Class Filter
 */
function laborator_body_class( $classes ) {
	if ( kalium_get_theme_option( 'theme_borders' ) ) {
		$classes[] = 'has-page-borders';
	}

	if ( kalium_get_theme_option( 'footer_fixed' ) ) {
		$classes[] = 'has-fixed-footer';
	}

	// Header Border Bottom Color
	if ( kalium_get_theme_option( 'header_bottom_border' ) ) {
		$classes[] = 'has-header-bottom-border';
	}

	return $classes;
}

add_filter( 'body_class', 'laborator_body_class' );

/**
 * Portfolio Like Share Options
 */
function shortcode_lab_portfolio_like_share() {
	ob_start();
	include locate_template( 'tpls/portfolio-single-like-share.php' );

	return ob_get_clean();
}

add_shortcode( 'lab_portfolio_like_share', 'shortcode_lab_portfolio_like_share' );

/**
 * Like Button
 */
function kalium_shortcode_ajax_like_button( $atts ) {
	global $post;

	$id = $post->ID;

	// Custom ID
	if ( isset( $atts['id'] ) ) {
		$id = $atts['id'];
	}

	$likes = get_post_likes( $id );

	// Like Icon Class
	$like_icon_default = 'far fa-heart';
	$like_icon_liked   = 'fas fa-heart';

	ob_start();
	?>
    <a href="#" class="like-btn" data-id="<?php echo $id; ?>">
        <i class="icon <?php echo $likes['liked'] ? $like_icon_liked : $like_icon_default; ?>"></i>
        <span class="counter like-count"><?php echo esc_html( $likes['count'] ); ?></span>
    </a>
	<?php

	return ob_get_clean();
}

add_shortcode( 'kalium_ajax_like_button', 'kalium_shortcode_ajax_like_button' );

/**
 * Share buttons
 */
function kalium_shortcode_portfolio_share_buttons( $atts ) {
	global $post;

	$id = $post->ID;

	// Custom ID
	if ( isset( $atts['id'] ) ) {
		$id = $atts['id'];

	}

	// Portfolio share networks
	$share_networks = kalium_get_theme_option( 'portfolio_share_item_networks' );

	ob_start();
	?>
    <div class="social-links">

		<?php
		foreach ( $share_networks['visible'] as $network_id => $network ) {

			if ( 'placebo' == $network_id ) {
				continue;
			}

			kalium_social_network_share_post_link( $network_id, $id, [
				'icon_only' => true,
				'class'     => 'social-share-icon',
			] );

		}
		?>

    </div>
	<?php

	return ob_get_clean();
}

add_shortcode( 'kalium_portfolio_share_buttons', 'kalium_shortcode_portfolio_share_buttons' );

/**
 * Current Portfolio Menu Item Highlight (Bug fix)
 */
function portfolio_current_nav_class( $classes, $item ) {

	if ( ! isset( $item->url ) ) {
		return $item;
	}

	$path_info = pathinfo( $item->url );

	if ( $path_info['filename'] == kalium_get_theme_option( 'portfolio_prefix_url_slug', 'portfolio' ) ) {
		$classes[] = 'current-menu-item current_page_item';
	}

	return $classes;
}

if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
	$req_path_info = pathinfo( $_SERVER['REQUEST_URI'] );

	if ( ! empty( $req_path_info['filename'] ) && $req_path_info['filename'] == kalium_get_theme_option( 'portfolio_prefix_url_slug', 'portfolio' ) ) {
		add_filter( 'nav_menu_css_class', 'portfolio_current_nav_class', 10, 2 );
	}
}

/**
 * Portfolio Post Type Args
 */
function kalium_portfolio_post_type_args( $args ) {

	// URL Slug for Portfolio Works
	$portfolio_prefix_url_slug = sanitize_title( kalium_get_theme_option( 'portfolio_prefix_url_slug' ) );

	if ( $portfolio_prefix_url_slug ) {
		$args['rewrite']['slug'] = $portfolio_prefix_url_slug;
	}

	return $args;
}

add_filter( 'portfolioposttype_args', 'kalium_portfolio_post_type_args', 1000 );

/**
 * Portfolio Category Args
 */
function kalium_portfolio_category_tax_args( $args ) {

	// URL Slug for Portfolio Category
	$portfolio_category_prefix_url_slug = sanitize_title( kalium_get_theme_option( 'portfolio_category_prefix_url_slug' ) );

	if ( $portfolio_category_prefix_url_slug ) {
		$args['rewrite']['slug'] = $portfolio_category_prefix_url_slug;
	} else {
		$args['rewrite']['slug'] = 'portfolio-category';
	}

	return $args;
}

add_filter( 'portfolioposttype_category_args', 'kalium_portfolio_category_tax_args', 1000 );

/**
 * Proportional Image Height on Blog
 */
function kalium_blog_thumbnail_size_proportional( $size ) {
	return 'large';
}

/**
 * Ninja Forms Support
 */
function kalium_ninja_forms_display_field_class( $field_class, $field_id, $field_row ) {
	global $ninja_forms_fields;

	switch ( $field_row['type'] ) {

		case '_submit':
		case '_timed_submit':
			$field_class .= ' btn btn-default';
			break;

		// Break Rule
		case '_hr':
			break;

		// Text Description
		case '_desc':
			break;

		// Checkbox & Radio
		case '_checkbox':
		case '_radio':
			break;

		// Text inputs
		default:
			$field_class .= ' form-control';
	}

	return $field_class;
}

add_filter( 'ninja_forms_display_field_class', 'kalium_ninja_forms_display_field_class', 10, 3 );

/**
 * Ninja forms 3 display forms.
 */
function kalium_ninja_forms_3_display_fields( $fields ) {
	$field_types = [ 'submit' ];

	if ( is_array( $fields ) ) {
		foreach ( $fields as & $field ) {
			// Buttons
			if ( in_array( $field['type'], $field_types ) ) {
				$field['element_class'] .= ' button';
			}
		}
	}

	return $fields;
}

add_filter( 'ninja_forms_display_fields', 'kalium_ninja_forms_3_display_fields' );

/**
 * Footer Visibility
 */
add_filter( 'kalium_show_footer', kalium_get_theme_option( 'footer_visibility', true ) ? '__return_true' : '__return_false', 1 );

/**
 * LayerSlider hide Notice
 */
add_filter( 'option_layerslider-authorized-site', '__return_true', 1000 );

/**
 * Portfolio Loop Thumbnail Custom Sizes
 */
function kalium_portfolio_loop_custom_thumbnail_size( $size, $type ) {
	if ( 'type-1' == $type && ( $custom_size = kalium_get_theme_option( 'portfolio_thumbnail_size_1' ) ) ) {
		return $custom_size;
	} elseif ( 'type-2' == $type && ( $custom_size = kalium_get_theme_option( 'portfolio_thumbnail_size_2' ) ) ) {
		return $custom_size;
	}

	return $size;
}

/**
 * Portfolio Head Title Meta Tag
 */
function portfolioposttype_args_head_title( $args ) {
	$args['labels']['name'] = kalium_get_theme_option( 'portfolio_title' );

	return $args;
}

if ( kalium_get_theme_option( 'portfolio_title' ) ) {
	add_filter( 'portfolioposttype_args', 'portfolioposttype_args_head_title' );
}

/**
 * Disabled comments on blog posts
 */
if ( 'hide' === kalium_get_theme_option( 'blog_comments' ) ) {
	add_filter( 'kalium_blog_enable_comments', '__return_false' );
}

/**
 * Remove Dot from Social Networks.
 *
 * @deprecated 3.0
 */
function kalium_social_networks_name_remove_dot( $name ) {
	return preg_replace( '/\.$/', '', $name );
}

/**
 * Disable Kalium Open Graph data generation when Yoast is enabled
 */
if ( defined( 'WPSEO_VERSION' ) ) {
	$social = WPSEO_Options::get_option( 'wpseo_social' );

	if ( isset( $social['opengraph'] ) ) {
		add_filter( 'kalium_open_graph_meta', '__return_false' );
	}
}

/**
 * Fix image dimensions issue with SVG files
 */
function kalium_fix_svg_size_for_images( $image, $attachment_id = null ) {
	if ( is_array( $image ) && kalium()->is->svg( $image[0] ) && ! ( $image[1] && $image[2] ) ) {
		$svg_dimensions = kalium()->helpers->get_svg_dimensions( $attachment_id );
		$image[1]       = $svg_dimensions[0];
		$image[2]       = $svg_dimensions[1];
	}

	return $image;
}

add_filter( 'wp_get_attachment_image_src', 'kalium_fix_svg_size_for_images', 10, 2 );

/**
 * Jetpack remove YouTube and Vimeo embed.
 */
function kalium_jetpack_remove_youtube_vimeo_shortcodes( $shortcodes ) {
	$jetpack_shortcodes_dir = WP_CONTENT_DIR . '/plugins/jetpack/modules/shortcodes/';

	$shortcodes_to_unload = array( 'youtube.php', 'vimeo.php' );

	foreach ( $shortcodes_to_unload as $shortcode ) {
		if ( $key = array_search( $jetpack_shortcodes_dir . $shortcode, $shortcodes ) ) {
			unset( $shortcodes[ $key ] );
		}
	}

	return $shortcodes;
}

add_filter( 'jetpack_shortcodes_to_include', 'kalium_jetpack_remove_youtube_vimeo_shortcodes', 10 );

/**
 * Preselected portfolio item type.
 */
function kalium_portfolio_preselected_item_type_filter( $field_group ) {
	if ( 'group_5ba0c486f384b' === $field_group['key'] ) { // Portfolio Item Type
		$item_type = kalium_get_theme_option( 'portfolio_preselected_item_type' );

		if ( in_array( $item_type, array( 'type-1', 'type-2', 'type-3', 'type-4', 'type-5', 'type-6', 'type-7' ) ) ) {
			$field_group['fields'][0]['default_value'] = array(
				0 => $item_type,
			);
		}
	}

	return $field_group;
}

add_filter( 'acf/validate_field_group', 'kalium_portfolio_preselected_item_type_filter' );

/**
 * Custom header styling.
 */
function kalium_header_custom_styling_filter() {
	if ( is_singular() ) {
		$post_id             = get_queried_object_id();
		$page_header_styling = kalium_get_field( 'page_header_styling', $post_id );

		if ( 'yes' === $page_header_styling ) {
			$header_background_color = kalium_get_field( 'header_background_color', $post_id );
			$header_bottom_border    = kalium_get_field( 'header_bottom_border', $post_id );
			$header_bottom_spacing   = kalium_get_field( 'header_bottom_spacing', $post_id );

			if ( $header_background_color ) {
				add_filter( 'get_data_header_background_color', kalium_hook_return_value( $header_background_color ) );
			}

			if ( $header_bottom_border ) {
				add_filter( 'get_data_header_bottom_border', kalium_hook_return_value( $header_bottom_border ) );
			}

			if ( $header_bottom_spacing ) {
				add_filter( 'get_data_header_bottom_spacing', kalium_hook_return_value( $header_bottom_spacing ) );
			}
		} else if ( 'no' === $page_header_styling ) {
			add_filter( 'get_data_header_background_color', '__return_empty_string' );
			add_filter( 'get_data_header_bottom_border', '__return_empty_string' );
			add_filter( 'get_data_header_bottom_spacing', '__return_empty_string' );
		}
	}
}

add_action( 'wp', 'kalium_header_custom_styling_filter' );
