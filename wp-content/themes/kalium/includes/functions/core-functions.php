<?php
/**
 * Kalium WordPress Theme
 *
 * Core theme functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Get template from Kalium theme.
 *
 * @param string $file
 * @param array  $args
 *
 * @return void
 */
function kalium_get_template( $file, $args = [] ) {

	// Templates prefix
	$file = sprintf( 'templates/%s', $file );

	// Locate template file
	$located = locate_template( $file, false );

	// Apply filters to current template file
	$template_file = apply_filters( 'kalium_get_template', $located, $file, $args );

	// File does not exists
	if ( ! file_exists( $template_file ) ) {
		kalium_doing_it_wrong( __FUNCTION__, sprintf( '%s does not exist.', '<code>' . $file . '</code>' ), '2.1' );

		return;
	}

	// Filter arguments by "kalium_get_template-filename.php"
	$args = apply_filters( "kalium_get_template-{$file}", $args );

	// Extract arguments (to use in template file)
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	// Actions before parsing template
	do_action( 'kalium_get_template_before', $located, $file, $args );

	include( $template_file );

	// Actions after parsing template
	do_action( 'kalium_get_template_after', $located, $file, $args );
}

/**
 * Doing it wrong, the Kalium way.
 *
 * @param string $function
 * @param string $message
 * @param string $version
 *
 * @return void
 */
function kalium_doing_it_wrong( $function, $message, $version ) {
	$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

	if ( defined( 'DOING_AJAX' ) ) {
		do_action( 'doing_it_wrong_run', $function, $message, $version );
		error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
	} else {
		_doing_it_wrong( $function, $message, $version );
	}
}

/**
 * Enqueue script or style (or groups of them) from theme registered assets.
 *
 * @param string       $handle
 * @param string|array $src
 *
 * @return void
 */
function kalium_enqueue( $handle, $src = '' ) {
	kalium()->enqueue->enqueue( $handle, $src );
}

/**
 * Get prefixed enqueue handle.
 *
 * @param string $handle
 * @param bool   $return_all
 *
 * @return string|array
 */
function kalium_enqueue_handle( $handle, $return_all = false ) {
	return kalium()->enqueue->get_prefixed_handle( $handle, $return_all );
}

/**
 * Dequeue script or style (or groups of them) from theme registered assets.
 *
 * @param string $handle
 *
 * @return void
 */
function kalium_dequeue( $handle ) {
	kalium()->enqueue->dequeue( $handle );
}

/**
 * Add inline script before or after the script.
 *
 * @param string $handle
 * @param string $data
 * @param string $position
 *
 * @return bool
 */
function kalium_add_inline_script( $handle, $data, $position = 'after' ) {
	return kalium()->enqueue->add_inline_script( $handle, $data, $position );
}

/**
 * Get theme option.
 *
 * @param string|null $option_name
 * @param string      $default_value
 *
 * @return mixed
 */
function kalium_get_theme_option( $option_name = null, $default_value = '' ) {

	// Return all theme options
	if ( ! $option_name ) {
		return apply_filters( 'get_theme_options', get_theme_mods() );
	}

	return apply_filters( "get_data_{$option_name}", get_theme_mod( $option_name, $default_value ) );
}

/**
 * Get ACF field value with fallback functionality.
 *
 * @param string   $field_key
 * @param int|bool $post_id
 * @param bool     $format_value
 *
 * @return mixed
 */
function kalium_get_field( $field_key, $post_id = false, $format_value = true ) {
	return kalium()->acf->get_field( $field_key, $post_id, $format_value );
}

/**
 * Get element from array by key (fail safe).
 *
 * @param array  $arr
 * @param string $key
 * @param mixed  $default_value
 *
 * @return mixed|null
 */
function kalium_get_array_key( $arr, $key, $default_value = null ) {
	if ( ! is_array( $arr ) ) {
		return $default_value;
	}

	return ! empty( $arr[ $key ] ) ? $arr[ $key ] : $default_value;
}

/**
 * Get SVG file contents from theme directory.
 *
 * @param string     $file_path
 * @param string     $id
 * @param int[]|null $size
 *
 * @return string|null
 */
function kalium_get_svg_file( $file_path, $id = null, $size = null ) {
	$file_path = kalium()->locate_file( $file_path );

	if ( ! file_exists( $file_path ) ) {
		return null;
	}

	// File contents
	$file_contents = file_get_contents( $file_path );

	// Set or replace ID
	if ( is_string( $id ) && ! empty( $id ) ) {
		$id_attribute  = sprintf( 'id="%s"', esc_attr( $id ) );
		$file_contents = preg_replace( '/(\s)id=".*?"/i', '${1}' . $id_attribute, $file_contents );

		// Assign ID if not exists
		if ( false === strpos( $file_contents, $id_attribute ) ) {
			$file_contents = str_replace( '<svg', "<svg {$id_attribute}", $file_contents );
		}
	}

	// Apply new size
	if ( is_array( $size ) && 2 === count( $size ) ) {
		$width  = $size[0];
		$height = $size[1];

		// Set new dimensions replace
		$sizes = [
			'find'    => [
				'/width="\d+"/',
				'/height="\d+"/',
			],
			'replace' => [
				sprintf( 'width="%d"', $width ),
				sprintf( 'height="%d"', $height ),
			],
		];

		$file_contents = preg_replace( $sizes['find'], $sizes['replace'], $file_contents );

		// Assign width if not exists
		if ( false === strpos( $file_contents, $sizes['replace'][0] ) ) {
			$file_contents = str_replace( '<svg', "<svg {$sizes['replace'][0]}", $file_contents );
		}

		// Assign height if not exists
		if ( false === strpos( $file_contents, $sizes['replace'][1] ) ) {
			$file_contents = str_replace( '<svg', "<svg {$sizes['replace'][1]}", $file_contents );
		}
	}

	return $file_contents;
}

/**
 * Get queried object based on Kalium logic.
 *
 * @return int|0
 */
function kalium_get_queried_object_id() {
	static $queried_object_id;

	// Return cached queried object id
	if ( isset( $queried_object_id ) ) {
		return $queried_object_id;
	}

	// Queried object ID
	$queried_object_id = get_queried_object_id();

	// On shop archive pages inherit ID from WooCommerce shop page
	if ( kalium()->is->woocommerce_active() && ( is_shop() || is_product_taxonomy() ) && ( $post = get_post( wc_get_page_id( 'shop' ) ) ) ) {
		$queried_object_id = $post->ID;
	}

	return $queried_object_id;
}

/**
 * Get attachment image, the Kalium way.
 *
 * @param int        $attachment_id
 * @param string     $size
 * @param null|array $atts
 * @param null|array $placeholder_atts
 *
 * @return string
 */
function kalium_get_attachment_image( $attachment_id, $size = 'thumbnail', $atts = null, $placeholder_atts = null ) {
	return kalium()->images->get_image( $attachment_id, $size, $atts, $placeholder_atts );
}

/**
 * Validate boolean value for given var.
 * Returns true for values 'y' or 'yes'
 * Returns for string value 'false'
 *
 * @param mixed $var
 *
 * @return bool
 * @uses wp_validate_boolean()
 */
function kalium_validate_boolean( $var ) {
	return kalium()->helpers->validate_boolean( $var );
}

/**
 * Show classes attribute array.
 *
 * @param array $classes
 * @param bool  $echo
 *
 * @return string
 */
function kalium_class_attr( $classes, $echo = true ) {
	$class = sprintf( 'class="%s"', kalium()->helpers->list_classes( $classes ) );

	if ( $echo ) {
		echo $class;

		return '';
	}

	return $class;
}

/**
 * Get nav menu.
 *
 * @param array $args
 *
 * @return string|false
 */
function kalium_nav_menu( $args = [] ) {

	// Load menu by id
	if ( is_numeric( $args ) ) {
		$args = [
			'menu' => $args,
		];
	} // Load menu from theme location
	else if ( is_string( $args ) ) {
		$args = [
			'theme_location' => $args,
		];
	}

	$container_class = kalium_get_array_key( $args, kalium_conditional( isset( $args['theme_location'] ), 'theme_location', 'menu' ), 'none' );

	// Menu args
	$args = wp_parse_args( $args, [
		'container'       => 'nav',
		'container_class' => 'nav-container-' . $container_class,
		'echo'            => false,
		'link_before'     => '<span>',
		'link_after'      => '</span>',
		'fallback_cb'     => 'kalium_page_menu',
	] );

	return apply_filters( 'kalium_nav_menu', wp_nav_menu( $args ), $args );
}

/**
 * Page menu items fallback function used in kalium_nav_menu.
 *
 * @param array $args
 *
 * @return string|false
 */
function kalium_page_menu( $args = [] ) {
	$page_menu_args = [
		'container'  => kalium_get_array_key( $args, 'container', 'nav' ),
		'menu_class' => kalium_get_array_key( $args, 'container_class', 'none' ),
		'before'     => '<ul class="menu">',
		'echo'       => false,
	];

	// Custom page elements container classes
	$page_css_class_function = function ( $classes ) {
		$classes[] = 'menu-item';

		return $classes;
	};

	// Add filter to "page_css_class" hook
	add_filter( 'page_css_class', $page_css_class_function );

	$menu = wp_page_menu( $page_menu_args );

	// Remove filter from "page_css_class" hook
	remove_filter( 'page_css_class', $page_css_class_function );

	return $menu;
}

/**
 * JavaScript assets enqueue mapping.
 *
 * @return void
 */
function kalium_js_assets_enqueue_mapping() {
	$js = $css = [];

	// Light gallery
	$light_gallery = kalium()->enqueue->get_enqueue_items( 'light-gallery' );

	foreach ( $light_gallery as $enqueue_item ) {
		if ( $enqueue_item->is_style() ) {
			$css['light-gallery'][] = [
				'src' => $enqueue_item->get_src(),
			];
		} else {
			$js['light-gallery'][] = [
				'src' => $enqueue_item->get_src(),
			];
		}
	}

	$enqueue_mapping = [
		'js'  => $js,
		'css' => $css,
	];

	// Dependency loader
	kalium_define_js_variable( [
		'enqueueAssets' => $enqueue_mapping,
		'require'       => '~function(e){var t=e instanceof Array?e:[e];return new Promise(function(e,r){var a=function(t){if(t&&t.length){var r=t.shift(),n=r.match( /\.js(\?.*)?$/)?"script":"text";jQuery.ajax({dataType:n,url:r}).success(function(){!function(e){var t;e.match( /\.js(\?.*)?$/)?(t=document.createElement("script")).src=e:((t=document.createElement("link")).rel="stylesheet",t.href=e);var r=!1,a=jQuery("[data-deploader]").each(function(t,a){e!=jQuery(a).attr("src")&&e!=jQuery(a).attr("href")||(r=!0)}).length;r||(t.setAttribute("data-deploader",a),jQuery("head").append(t))}(r)}).always(function(){r.length&&a(t)})}else e()};a(t)})}'
	] );
}

/**
 * Define Kalium JavaScript variable attached to global "_k" variable.
 *
 * @param string|array $var_name
 * @param mixed        $value
 * @param string       $array_key
 *
 * @return void
 */
function kalium_define_js_variable( $var_name, $value = '', $array_key = '' ) {
	if ( is_array( $var_name ) ) {
		foreach ( $var_name as $single_var_name => $single_value ) {
			if ( is_string( $single_var_name ) ) {
				kalium_define_js_variable( $single_var_name, $single_value );
			}
		}
	} else {
		$var_name = esc_js( $var_name );
		$value    = is_string( $value ) && '~' === substr( $value, 0, 1 ) ? substr( $value, 1 ) : wp_json_encode( $value, JSON_NUMERIC_CHECK );

		if ( $array_key ) {
			$script = sprintf( 'var _k = _k || {}; _k.%1$s = _k.%1$s || {}; _k.%1$s["%3$s"] = %2$s;', $var_name, $value, $array_key );
		} else {
			$script = sprintf( 'var _k = _k || {}; _k.%1$s = %2$s;', $var_name, $value );
		}

		$script = apply_filters( 'kalium_define_js_variable', $script, $var_name, $value );

		// Frontend
		if ( ! is_admin() ) {
			kalium_add_inline_script( 'main-js', $script, 'before' );
		} else {
			echo "<script>{$script}</script>";
		}
	}
}

/**
 * Return single value in WP Hook.
 *
 * @param mixed $value
 *
 * @return array
 */
function kalium_hook_return_value( $value ) {
	$returnable = new Kalium_WP_Hook_Value( $value );

	return [ $returnable, 'return_value' ];
}

/**
 * Return single value in WP Hook.
 *
 * @param mixed $value
 *
 * @return array
 */
function kalium_hook_echo_value( $value ) {
	$returnable = new Kalium_WP_Hook_Value( $value );

	return [ $returnable, 'echo_value' ];
}

/**
 * Concat a string value in WP Hook.
 *
 * @param string $value
 *
 * @return array
 */
function kalium_hook_concat_string_value( $value ) {
	$returnable = new Kalium_WP_Hook_Value( $value );

	return [ $returnable, 'concat_string_value' ];
}

/**
 * Merge array value in WP Hook.
 *
 * @param mixed  $value
 * @param string $key
 *
 * @return array
 */
function kalium_hook_merge_array_value( $value, $key = '' ) {
	$returnable              = new Kalium_WP_Hook_Value();
	$returnable->array_value = $value;
	$returnable->array_key   = $key;

	return [ $returnable, 'merge_array_value' ];
}

/**
 * Merge two arrays in WP Hook.
 *
 * @param array $value
 *
 * @return array
 */
function kalium_hook_merge_arrays( $value ) {
	$returnable        = new Kalium_WP_Hook_Value();
	$returnable->array = $value;

	return [ $returnable, 'merge_arrays' ];
}

/**
 * Call user function in WP Hook.
 *
 * @param string $function_name
 * @param mixed  ...$args
 *
 * @return array
 */
function kalium_hook_call_user_function( $function_name ) {

	// Function arguments
	$function_args = func_get_args();

	// Remove the function name argument
	array_shift( $function_args );

	$returnable                = new Kalium_WP_Hook_Value();
	$returnable->function_name = $function_name;
	$returnable->function_args = $function_args;

	/** @var TYPE_NAME $returnable */
	return array( $returnable, 'call_user_function' );
}

/**
 * Custom style generator.
 *
 * @param string       $selector
 * @param string|array $props
 * @param string       $media
 * @param bool         $footer
 *
 * @return void
 */
function kalium_append_custom_css( $selector, $props = '', $media = '', $footer = false ) {
	global $kalium_append_custom_css;

	if ( ! isset( $kalium_append_custom_css ) ) {
		$kalium_append_custom_css = [];
	}

	$css = '';

	// Selector Start
	$css .= $selector . ' {';

	// Selector Properties
	if ( is_array( $props ) ) {
		$css .= kalium()->helpers->build_css_props( $props );
	} else {
		$css .= $props;
	}

	$css .= '}';
	// Selector End

	// Media Wrap
	if ( trim( $media ) ) {
		if ( strpos( $media, '@' ) == false ) {
			$css = "@media {$media} { {$css} }";
		} else {
			$css = "{$media} { {$css} }";
		}
	}

	if ( ! $footer || defined( 'DOING_AJAX' ) ) {
		echo sprintf( '<style data-appended-custom-css="true">%s</style>', $css );

		return;
	}

	$kalium_append_custom_css[] = $css;
}

/**
 * Mobile menu breakpoint.
 *
 * @return int
 */
function kalium_get_mobile_menu_breakpoint() {
	$breakpoint = kalium_get_theme_option( 'menu_mobile_breakpoint' );

	if ( ! $breakpoint || ! is_numeric( $breakpoint ) ) {
		$breakpoint = 768;
	}

	return $breakpoint;
}

/**
 * Get social networks from theme options.
 *
 * @return array
 */
function kalium_get_social_networks_list() {

	// Social networks list
	$social_networks_list = [];

	// Old social network ids aliases
	$social_network_id_aliases = [
		'fb'     => 'facebook',
		'ig'     => 'instagram',
		'tw'     => 'twitter',
		'be'     => 'behance',
		'yt'     => 'youtube',
		'lin'    => 'linkedin',
		'drb'    => 'dribbble',
		'pi'     => 'pinterest',
		'vk'     => 'vkontakte',
		'vm'     => 'vimeo',
		'da'     => 'deviantart',
		'wa'     => 'whatsapp',
		'tu'     => 'tumblr',
		'sk'     => 'skype',
		'gh'     => 'github',
		'sc'     => 'soundcloud',
		'hz'     => 'houzz',
		'px'     => '500px',
		'xi'     => 'xing',
		'sp'     => 'spotify',
		'sn'     => 'snapchat',
		'em'     => 'email',
		'yp'     => 'yelp',
		'ta'     => 'tripadvisor',
		'tc'     => 'twitch',
		'fl'     => 'flickr',
		'fs'     => 'foursquare',
		'custom' => 'custom',
	];

	// Ordered social networks
	$social_networks_ordered = kalium_get_enabled_options( kalium_get_theme_option( 'social_order' ) );

	foreach ( $social_networks_ordered as $old_social_network_id => $title ) {
		$social_network = [
			'link' => kalium_get_theme_option( "social_network_link_{$old_social_network_id}" ),
			'data' => [
				'old_id' => $old_social_network_id,
			],
		];

		// Social network id
		$social_network_id = $social_network_id_aliases[ $old_social_network_id ];

		// Email type
		if ( 'email' === $social_network_id ) {
			$social_network['data']['is_email']      = true;
			$social_network['data']['email_subject'] = kalium_get_theme_option( 'social_network_link_em_subject' );
		}

		// Custom social network icon
		if ( 'custom' === $old_social_network_id ) {
			$social_network['link']          = kalium_get_theme_option( 'social_network_custom_link_link' );
			$social_network['data']['title'] = kalium_get_theme_option( 'social_network_custom_link_title' );
			$social_network['data']['icon']  = kalium_get_theme_option( 'social_network_custom_link_icon' );
		}

		$social_networks_list[ $social_network_id ] = $social_network;
	}

	return $social_networks_list;
}

/**
 * Get Google API Key.
 *
 * @return string
 */
function kalium_get_google_api() {
	return apply_filters( 'kalium_google_api_key', kalium_get_theme_option( 'google_maps_api' ) );
}

/**
 * Generate infinite scroll pagination object for JavaScript.
 *
 * @param string $id
 * @param array  $args
 *
 * @return void
 */
function kalium_infinite_scroll_pagination_js_object( $id, $args = [] ) {
	if ( ! empty( $id ) ) {

		// Defaults
		$args = wp_parse_args( $args, [

			// Total items
			'total_items'       => 1,

			// Posts per page
			'posts_per_page'    => 10,

			// Fetched ID's,
			'fetched_items'     => [],

			// Base query
			'base_query'        => [],

			// WP Ajax Action
			'action'            => 'kalium_endless_pagination_get_paged_items',

			// Loop handler
			'loop_handler'      => '',

			// Posts loop template function (PHP)
			'loop_template'     => '',

			// JS Callback Function
			'callback'          => '',

			// Selectors
			'trigger_element'   => sprintf( '.pagination--infinite-scroll-show-more[data-endless-pagination-id="%s"]', esc_attr( $id ) ),

			// Container Element
			'container_element' => sprintf( '#%s', esc_attr( $id ) ),

			// Auto-reveal
			'auto_reveal'       => false,

			// Extra arguments
			'args'              => [],

		] );

		// Remove unnecessary keys from query
		foreach ( [ 'pagename', 'page_id', 'name', 'portfolio', 'preview' ] as $query_arg ) {
			if ( isset( $base_query[ $query_arg ] ) ) {
				unset( $base_query[ $query_arg ] );
			}
		}

		// Instance object
		$infinite_scroll_obj_data = [

			// Query to use
			'baseQuery'    => $args['base_query'],

			// Extra Query Filter Args
			'queryFilter'  => null,

			// Pagination info
			'pagination'   => [
				'totalItems'   => $args['total_items'],
				'perPage'      => $args['posts_per_page'],
				'fetchedItems' => $args['fetched_items'],
			],

			// WP AJAX Action
			'action'       => $args['action'],

			// Loop handler
			'loopHandler'  => $args['loop_handler'],

			// Loop template
			'loopTemplate' => $args['loop_template'],

			// JavaScript Callback
			'callback'     => $args['callback'],

			// Triggers
			'triggers'     => [

				// CSS Selector
				'selector'   => $args['trigger_element'],

				// Items container (where to append results)
				'container'  => $args['container_element'],

				// Auto Reveal
				'autoReveal' => $args['auto_reveal'],

				// Classes added on events
				'classes'    => [

					// Ready
					'isReady'       => 'pagination--infinite-scroll-has-items',

					// Loading
					'isLoading'     => 'pagination--infinite-scroll-is-loading',

					// Pagination reached the end
					'allItemsShown' => 'pagination--infinite-scroll-all-items-shown'
				],
			],

			// Extra arguments
			'args'         => $args['args'],
		];

		// Infinite scroll pagination instance
		kalium_define_js_variable( 'infinite_scroll_instances', apply_filters( 'kalium_infinite_scroll_object', $infinite_scroll_obj_data, $id ), $id );
	}
}

/**
 * Get footer classes.
 *
 * @param array|string $class
 *
 * @return array
 */
function kalium_get_footer_classes( $class = [] ) {

	// Classes
	$classes = [ 'site-footer', 'main-footer' ];

	// Split string
	if ( is_string( $class ) ) {
		$class = explode( ' ', $class );
	}

	// Extra classes
	if ( ! empty( $class ) && is_array( $class ) ) {
		$classes = array_merge( $classes, $class );
	}

	return apply_filters( 'kalium_footer_class', $classes );
}

/**
 * Format content just like default the_content().
 *
 * @param string $str
 *
 * @return string
 */
function kalium_format_content( $str ) {
	$format_functions = [
		'do_blocks',
		'wptexturize',
		'wpautop',
		'prepend_attachment',
		'convert_smilies',
		'do_shortcode',
	];

	foreach ( $format_functions as $function ) {
		$str = $function( $str );
	}

	return $str;
}
