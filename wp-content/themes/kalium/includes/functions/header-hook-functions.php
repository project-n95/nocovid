<?php
/**
 * Kalium WordPress Theme
 *
 * Header hook functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Default sticky options.
 *
 * @param array $options
 *
 * @return array
 */
function _kalium_sticky_header_options_default( $options ) {

	// Sticky vars
	$vertical_padding    = kalium_get_theme_option( 'sticky_header_vertical_padding' );
	$background_color    = kalium_get_theme_option( 'sticky_header_background_color' );
	$header_skin         = kalium_get_theme_option( 'sticky_header_skin' );
	$has_border          = kalium_get_theme_option( 'sticky_header_border' );
	$border_color        = kalium_get_theme_option( 'sticky_header_border_color' );
	$border_width        = kalium_get_theme_option( 'sticky_header_border_width' );
	$shadow_color        = kalium_get_theme_option( 'sticky_header_shadow_color' );
	$shadow_width        = kalium_get_theme_option( 'sticky_header_shadow_width' );
	$shadow_blur         = kalium_get_theme_option( 'sticky_header_shadow_blur' );
	$is_autohide         = kalium_get_theme_option( 'sticky_header_autohide' );
	$autohide_type       = kalium_get_theme_option( 'sticky_header_autohide_animation_type' );
	$autohide_duration   = kalium_get_theme_option( 'sticky_header_autohide_duration' );
	$logo_width          = kalium_get_theme_option( 'sticky_header_logo_width' );
	$custom_logo         = kalium_get_theme_option( 'sticky_header_logo' );
	$animate_with_scroll = kalium_get_theme_option( 'sticky_header_animate_duration' );
	$tween_changes       = kalium_get_theme_option( 'sticky_header_tween_changes' );
	$animate_duration    = kalium_get_theme_option( 'header_sticky_duration' );
	$animate_offset      = kalium_get_theme_option( 'header_sticky_initial_offset' );
	$animate_chaining    = kalium_get_theme_option( 'sticky_header_animation_chaining' );
	$supported_on        = [
		'desktop' => kalium_get_theme_option( 'sticky_header_support_desktop' ),
		'tablet'  => kalium_get_theme_option( 'sticky_header_support_tablet' ),
		'mobile'  => kalium_get_theme_option( 'sticky_header_support_mobile' ),
	];

	/**
	 * Initial offset for scenes to exclude top header bar.
	 */
	$options['offset'] = '.top-header-bar';

	/**
	 * Animation scene offset.
	 */
	if ( is_numeric( $animate_offset ) ) {
		$options['animationOffset'] = $animate_offset;
	}

	/**
	 * Animate animation progress with scroll.
	 */
	$options['animateProgressWithScroll'] = ! boolval( $animate_with_scroll );

	/**
	 * Tween changes with scroll progress.
	 */
	$options['tweenChanges'] = boolval( $tween_changes );

	/**
	 * Animate duration in seconds.
	 */
	if ( is_numeric( $animate_duration ) ) {
		$options['animateDuration'] = $animate_duration;
	}

	/**
	 * Generate spacer or not.
	 */
	$options['spacer'] = 'absolute' !== kalium_header_get_option( 'position' );

	/**
	 * Autohide sticky type.
	 */
	if ( $is_autohide ) {

		// Sticky type
		$options['type'] = 'autohide';

		// Animation type
		$options['autohide']['animationType'] = $autohide_type;

		// Duration
		if ( is_numeric( $autohide_duration ) ) {
			$options['autohide']['duration'] = $autohide_duration;
		}
	}

	/**
	 * Supported on device types.
	 */
	$options['supportedOn'] = $supported_on;

	/**
	 * Menu skin on sticky mode.
	 */
	if ( $header_skin ) {
		$options['other']['menuSkin'] = $header_skin;
	}

	/**
	 * Animation scenes.
	 */
	$scene_name_padding     = 'padding';
	$scene_name_styling     = 'styling';
	$scene_name_sticky_logo = 'sticky-logo';
	$scene_name_resize_logo = 'resize-logo';

	$animation_scenes = [];

	// Padding animation scene
	if ( is_numeric( $vertical_padding ) ) {

		$animation_scene_padding = [
			'name'     => 'padding',
			'selector' => '.header-block',
			'props'    => [
				'paddingTop',
				'paddingBottom',
			],
			'css'      => [
				'default' => [
					'paddingTop'    => $vertical_padding,
					'paddingBottom' => $vertical_padding,
				],
			],
		];

		$animation_scenes[ $scene_name_padding ] = $animation_scene_padding;
	}

	// Background, shadow and border animation scene
	$animation_scene_style = [
		'name'     => 'style',
		'selector' => '.header-block',
		'props'    => [],
		'css'      => [
			'default' => [],
		],
		'data'     => [
			'tags' => [
				'transparent-header', // required!
			],
		],
	];

	if ( $background_color ) {
		$animation_scene_style['props'][]                           = 'backgroundColor';
		$animation_scene_style['css']['default']['backgroundColor'] = $background_color;
	}

	// Bottom border
	if ( $has_border ) {

		// Bottom border color
		if ( $border_color ) {
			$animation_scene_style['props'][]                        = 'borderBottom';
			$animation_scene_style['css']['default']['borderBottom'] = sprintf( '%s solid %s', $border_width, $border_color );
			kalium_append_custom_css( '.site-header', 'border-bottom: 1px solid transparent' );
		}

		// Shadow color
		if ( $shadow_color ) {
			$animation_scene_style['props'][]                     = 'boxShadow';
			$animation_scene_style['css']['default']['boxShadow'] = sprintf( '%s 0px %s %s', $shadow_color, $shadow_width, $shadow_blur );
			kalium_append_custom_css( '.site-header', 'box-shadow: 0px 0px 0px transparent' );
		}
	}

	// Register styling scene if there are props to animate
	if ( ! empty( $animation_scene_style['css']['default'] ) ) {
		$animation_scenes[ $scene_name_styling ] = $animation_scene_style;
	}

	// Custom logo animation scene
	if ( $custom_logo ) {

		// Attachment image
		$sticky_logo = wp_get_attachment_image( $custom_logo, 'original' );

		if ( $sticky_logo ) {

			// Register alternate logo
			$sticky_logo_src    = wp_get_attachment_image_src( $custom_logo, 'original' );
			$sticky_logo_width  = $sticky_logo_src[1];
			$sticky_logo_height = $sticky_logo_src[2];

			$options['alternateLogos']['sticky'] = [
				'name'  => 'sticky',
				'image' => $sticky_logo,
			];

			// Custom logo width
			if ( $logo_width ) {
				$new_dimensions = kalium()->helpers->resize_by_width( $sticky_logo_width, $sticky_logo_height, $logo_width );

				$sticky_logo_width  = $new_dimensions[0];
				$sticky_logo_height = $new_dimensions[1];
			} // Determine logo width set in theme options
			else if ( $custom_logo_max_width = kalium_get_theme_option( 'custom_logo_max_width' ) ) {
				$new_dimensions = kalium()->helpers->resize_by_width( $sticky_logo_width, $sticky_logo_height, $custom_logo_max_width );

				$sticky_logo_width  = $new_dimensions[0];
				$sticky_logo_height = $new_dimensions[1];
			}

			// Sticky logo animation scene
			$animation_scene_sticky_logo = [
				'name'     => 'sticky-logo',
				'selector' => 'logo',
				'props'    => [
					'width',
					'height',
				],
				'css'      => [
					'width'  => $sticky_logo_width,
					'height' => $sticky_logo_height,
				],
				'data'     => [
					'type'          => 'alternate-logo',
					'alternateLogo' => 'sticky',
					'tags'          => [
						'logo-switch', // required!
					],
				],
			];

			$animation_scenes[ $scene_name_sticky_logo ] = $animation_scene_sticky_logo;
		}
	} // Logo resize animation scene
	else if ( $logo_width && ( $custom_logo = kalium_get_theme_option( 'custom_logo_image' ) ) ) {
		$custom_logo_src = wp_get_attachment_image_src( $custom_logo, 'original' );

		if ( $custom_logo_src ) {
			$custom_logo_width  = $custom_logo_src[1];
			$custom_logo_height = $custom_logo_src[2];
			$new_dimensions     = kalium()->helpers->resize_by_width( $custom_logo_width, $custom_logo_height, $logo_width );

			$custom_logo_width  = $new_dimensions[0];
			$custom_logo_height = $new_dimensions[1];

			// Resize logo animation scene
			$animation_scenes[ $scene_name_resize_logo ] = [
				'name'     => 'logo-resize',
				'selector' => '.logo-image',
				'props'    => [
					'width',
					'height',
				],
				'css'      => [
					'default' => [
						'width'  => $custom_logo_width,
						'height' => $custom_logo_height,
					],
				],
				'data'     => [
					'tags' => [
						'logo-resize', // required!
					],
				],
			];
		}
	}

	/**
	 * Animation chaining.
	 */
	$scene_duration_base = 0.3;

	switch ( $animate_chaining ) {

		// All at once
		case 'all':
			foreach ( $animation_scenes as & $animation_scene ) {
				$animation_scene['position'] = 0;
			}
			break;

		// Padding -> Background, Logo
		case 'padding-bg_logo':
			$animation_chain = [
				$scene_name_padding,
				[ $scene_name_styling, $scene_name_sticky_logo, $scene_name_resize_logo ],
			];
			break;

		// Background, Logo -> Padding
		case 'bg_logo-padding':
			$animation_chain = [
				[ $scene_name_styling, $scene_name_sticky_logo, $scene_name_resize_logo ],
				$scene_name_padding,
			];
			break;

		// Logo, Padding -> Background
		case 'logo_padding-bg':
			$animation_chain = [
				[ $scene_name_padding, $scene_name_sticky_logo, $scene_name_resize_logo ],
				$scene_name_styling,
			];
			break;

		// Background -> Logo, Padding
		case 'bg-logo_padding':
			$animation_chain = [
				$scene_name_styling,
				[ $scene_name_padding, $scene_name_sticky_logo, $scene_name_resize_logo ],
			];
			break;

		// Padding -> Background -> Logo
		case 'padding-bg-logo':
			$animation_chain = [
				$scene_name_padding,
				$scene_name_styling,
				[ $scene_name_sticky_logo, $scene_name_resize_logo ],
			];
			break;

		// Background -> Logo -> Padding
		case 'bg-logo-padding':
			$animation_chain = [
				$scene_name_styling,
				[ $scene_name_sticky_logo, $scene_name_resize_logo ],
				$scene_name_padding,
			];
			break;

		// Logo -> Background -> Padding
		case 'logo-bg-padding':
			$animation_chain = [
				[ $scene_name_sticky_logo, $scene_name_resize_logo ],
				$scene_name_styling,
				$scene_name_padding,
			];
			break;

		// Background -> Padding -> Logo
		case 'bg-padding-logo':
			$animation_chain = [
				$scene_name_styling,
				$scene_name_padding,
				[ $scene_name_sticky_logo, $scene_name_resize_logo ],
			];
			break;
	}

	// Set animation chaining
	if ( isset( $animation_chain ) ) {

		foreach ( $animation_chain as $i => $scenes ) {

			$scenes = ! is_array( $scenes ) ? [ $scenes ] : $scenes;

			foreach ( $scenes as $scene_name ) {

				if ( isset( $animation_scenes[ $scene_name ] ) ) {
					$position = $i * $scene_duration_base;

					// Duration on timeline
					if ( ! isset( $animation_scenes[ $scene_name ]['duration'] ) ) {
						$animation_scenes[ $scene_name ]['duration'] = $scene_duration_base;
					}

					// Position on timeline
					$animation_scenes[ $scene_name ]['position'] = $position;
				}
			}
		}
	}

	// Sort animation scenes by their position
	uasort( $animation_scenes, function ( $a, $b ) {
		if ( isset( $a['position'] ) && isset( $b['position'] ) ) {
			return $a['position'] < $b['position'] ? - 1 : 1;
		}
	} );

	// Update animation scenes property
	$options['animateScenes'] = $animation_scenes;

	return $options;
}

/**
 * Header position and spacing.
 *
 * @return void
 */
function _kalium_header_position_spacing_action() {
	$queried_object_id = kalium_get_queried_object_id();

	$header_position = kalium_header_get_option( 'position' );
	$header_spacing  = kalium_header_get_option( 'spacing' );

	// Header position
	if ( 'absolute' === $header_position && ! post_password_required( $queried_object_id ) ) {
		kalium_append_custom_css( '.site-header', 'position: absolute; left: 0; right: 0;' );

		// Header spacing
		if ( $header_spacing ) {
			$header_spacing = str_replace( 'px', '', $header_spacing );
			kalium_append_custom_css( '.wrapper', "padding-top: {$header_spacing}px", '', true );
		}
	}
}

/**
 * Absolute header body class.
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_absolute_header_body_class( $classes ) {
	$queried_object_id = kalium_get_queried_object_id();
	$header_position   = kalium_header_get_option( 'position' );

	if ( 'absolute' === $header_position && ! post_password_required( $queried_object_id ) ) {
		$classes[] = 'header-absolute';
	}

	return $classes;
}

/**
 * Custom logo and menu options for current page.
 *
 * @return void
 */
function _kalium_header_custom_logo_and_menu_on_page() {
	if ( get_queried_object() instanceof WP_Post && ( $queried_object_id = kalium_get_queried_object_id() ) ) {

		// Vars
		$custom_logo         = kalium_get_field( 'custom_logo', $queried_object_id );
		$custom_menu_skin    = kalium_get_field( 'custom_menu_skin', $queried_object_id );
		$sticky_menu_on_page = kalium_get_field( 'sticky_menu_on_page', $queried_object_id );
		$custom_sticky_logo  = kalium_get_field( 'custom_sticky_logo', $queried_object_id );
		$sticky_menu_skin    = kalium_get_field( 'sticky_menu_skin', $queried_object_id );

		$menu_skins = [
			'menu-skin-main',
			'menu-skin-dark',
			'menu-skin-light',
		];

		// Custom logo
		if ( $custom_logo && is_numeric( $custom_logo ) ) {
			$custom_logo_width = kalium_get_field( 'custom_logo_width' );

			add_filter( 'get_data_use_uploaded_logo', '__return_true' );
			add_filter( 'get_data_custom_logo_image', kalium_hook_return_value( $custom_logo ) );

			if ( is_numeric( $custom_logo_width ) && $custom_logo_width > 0 ) {
				add_filter( 'get_data_custom_logo_max_width', kalium_hook_return_value( $custom_logo_width ) );
			}
		}

		// Custom sticky logo
		if ( $custom_sticky_logo ) {
			add_filter( 'get_data_sticky_header_logo', kalium_hook_return_value( $custom_sticky_logo ) );
		}

		// Menu skin
		if ( $custom_menu_skin && in_array( $custom_menu_skin, $menu_skins ) ) {
			add_filter( 'get_data_menu_full_bg_skin', kalium_hook_return_value( $custom_menu_skin ) );
			add_filter( 'get_data_menu_standard_skin', kalium_hook_return_value( $custom_menu_skin ) );
			add_filter( 'get_data_menu_top_skin', kalium_hook_return_value( $custom_menu_skin ) );
			add_filter( 'get_data_menu_sidebar_skin', kalium_hook_return_value( $custom_menu_skin ) );
			add_filter( 'get_data_custom_header_default_skin', kalium_hook_return_value( $custom_menu_skin ) );
		}

		// Overwrite sticky header option for current page
		if ( in_array( $sticky_menu_on_page, [ 'enable', 'disable' ] ) ) {
			add_filter( 'get_data_sticky_header', ( $sticky_menu_on_page == 'enable' ? '__return_true' : '__return_false' ) );
		}

		// Custom sticky header skin
		if ( in_array( $sticky_menu_skin, $menu_skins ) ) {
			add_filter( 'get_data_sticky_header_skin', kalium_hook_return_value( $sticky_menu_skin ) );
		}
	}
}

/**
 * Process legacy header types in custom header builder.
 */
function _kalium_header_process_legacy_header_types( $menu_content ) {
	$header_type      = kalium_get_theme_option( 'main_menu_type' );
	$submenu_caret    = kalium_conditional( kalium_get_theme_option( 'submenu_dropdown_indicator' ), 'yes', 'no' );
	$menu_content_new = [
		'entries'   => [],
		'alignment' => 'right',
	];

	// Toggle skin color
	$skin_class = 'menu-skin-main';

	switch ( $header_type ) {
		case 'full-bg-menu':
			$skin_class = kalium_get_theme_option( 'menu_full_bg_skin' );
			break;

		case 'standard-menu':
			$skin_class = kalium_get_theme_option( 'menu_standard_skin' );
			break;

		case 'top-menu':
			$skin_class = kalium_get_theme_option( 'menu_top_skin' );
			break;

		case 'sidebar-menu':
			$skin_class = kalium_get_theme_option( 'menu_sidebar_skin' );
			break;
	}

	// Language switcher
	if ( kalium_get_theme_option( 'header_wpml_language_switcher' ) ) {
		$show_on             = kalium_get_theme_option( 'header_wpml_language_trigger' );
		$flag_position       = kalium_get_theme_option( 'header_wpml_language_flag_position' );
		$display_text_format = kalium_get_theme_option( 'header_wpml_language_switcher_text_display_type' );

		$language_switcher = [
			'contentType' => 'wpml-language-switcher',
			'options'     => [
				'display_text_format' => $display_text_format,
				'flag_position'       => $flag_position,
				'show_on'             => $show_on,
				'skin'                => $skin_class,
			],
		];

		$menu_content_new['entries'][] = $language_switcher;
	}

	// Search field
	if ( kalium_get_theme_option( 'header_search_field' ) ) {
		$search_field_input = [
			'contentType' => 'search-field',
			'options'     => [
				'align'   => 'left',
				'skin'    => $skin_class,
				'hide_on' => [ 'mobile' ],
			],
		];

		$menu_content_new['entries'][] = $search_field_input;
	}

	// Mini cart
	if ( kalium_get_theme_option( 'shop_cart_icon_menu' ) && kalium()->is->woocommerce_active() ) {
		$mini_cart_toggle = [
			'contentType' => 'woocommerce-mini-cart',
			'options'     => [
				'align' => 'left',
				'skin'  => $skin_class,
			],
		];

		$menu_content_new['entries'][] = $mini_cart_toggle;
	}

	// Fullscreen menu toggle
	if ( 'full-bg-menu' === $header_type ) {
		$menu_full_toggled_skin   = 'menu-skin-light';
		$menu_full_search_field   = kalium_conditional( kalium_get_theme_option( 'menu_full_bg_search_field' ), [ 'yes' ], [] );
		$menu_full_show_footer    = kalium_conditional( kalium_get_theme_option( 'menu_full_bg_footer_block' ), [ 'yes' ], [] );
		$menu_full_translucent_bg = kalium_conditional( kalium_get_theme_option( 'menu_full_bg_opacity' ), [ 'yes' ], [] );

		if ( 'menu-skin-light' === $skin_class ) {
			$menu_full_toggled_skin = 'menu-skin-dark';
		}

		// Fullscreen toggle
		$menu_toggle = [
			'contentType' => 'open-fullscreen-menu',
			'options'     => [
				'skin'             => $skin_class,
				'toggled_skin'     => $menu_full_toggled_skin,
				'background_color' => 'inherit',
				'search_field'     => $menu_full_search_field,
				'show_footer'      => $menu_full_show_footer,
				'translucent_bg'   => $menu_full_translucent_bg,
				'hide_on'          => [],
			],
		];

		$menu_content_new['entries'][] = $menu_toggle;
	} // Standard menu toggle
	else if ( 'standard-menu' === $header_type ) {
		$menu_toggle = [
			'contentType' => 'main-menu',
			'options'     => [
				'skin'                   => $skin_class,
				'submenu_dropdown_caret' => $submenu_caret,
			],
		];

		// Standard menu toggle
		if ( kalium_validate_boolean( kalium_get_theme_option( 'menu_standard_menu_bar_visible' ) ) ) {
			$menu_toggle['contentType']              = 'open-standard-menu';
			$menu_toggle['options']['menu_id']       = 'main-menu';
			$menu_toggle['options']['menu_position'] = 'beginning';

			// Reveal effect
			$reveal_effect = kalium_get_theme_option( 'menu_standard_menu_bar_effect' );

			$reveal_effect_mapping = [
				'reveal-from-top'    => 'slide-top',
				'reveal-from-right'  => 'slide-right',
				'reveal-from-left'   => 'slide-left',
				'reveal-from-bottom' => 'slide-bottom',
				'reveal-fade'        => 'fade',
			];

			// Toggle effect
			if ( isset( $reveal_effect_mapping[ $reveal_effect ] ) ) {
				$menu_toggle['options']['toggle_effect'] = $reveal_effect_mapping[ $reveal_effect ];
			}

			// Stagger direction
			$menu_toggle['options']['stagger_direction'] = 'right';

			// Add to end of row
			$menu_content_new['entries'][] = $menu_toggle;
		} else {
			$menu_toggle['options']['mobile_menu_toggle_position'] = 'end';

			// Add to beginning of row
			array_unshift( $menu_content_new['entries'], $menu_toggle );
		}
	} // Top menu toggle
	else if ( 'top-menu' === $header_type ) {
		$menu_toggle = [
			'contentType' => 'open-top-menu',
			'options'     => [
				'skin' => $skin_class,
			],
		];

		$menu_content_new['entries'][] = $menu_toggle;
	} // Sidebar menu toggle
	else if ( 'sidebar-menu' === $header_type ) {
		$menu_toggle = [
			'contentType' => 'open-sidebar-menu',
			'options'     => [
				'skin' => $skin_class,
			],
		];

		$menu_content_new['entries'][] = $menu_toggle;
	}

	return json_encode( $menu_content_new );
}
