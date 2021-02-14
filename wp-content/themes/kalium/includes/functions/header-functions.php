<?php
/**
 * Kalium WordPress Theme
 *
 * Theme header functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Show or hide header.
 *
 * @return bool
 */
function kalium_show_header() {
	return apply_filters( 'kalium_show_header', true );
}

/**
 * Check if header is fullwidth.
 *
 * @return bool
 */
function kalium_is_fullwidth_header() {
	$return = boolval( kalium_get_theme_option( 'header_fullwidth' ) );

	if ( get_queried_object() instanceof WP_Post && ( $queried_object_id = kalium_get_queried_object_id() ) ) {
		$header_fullwidth = kalium_get_field( 'header_fullwidth', $queried_object_id );

		if ( in_array( $header_fullwidth, [ 'yes', 'no' ] ) ) {
			$return = 'yes' === $header_fullwidth;
		}
	}

	return $return;
}

/**
 * Check if header contains top or side menu.
 *
 * @param string{'top-menu'|'sidebar-menu'} $menu_type
 *
 * @return bool
 */
function kalium_header_has_content_element_type( $menu_type ) {
	static $header_content_elements;

	// Custom Header Content Entries
	if ( ! isset( $header_content_elements ) ) {
		$header_content_elements = array_map( 'kalium_parse_content_builder_field', [
			kalium_get_theme_option( 'custom_header_content_left' ),
			kalium_get_theme_option( 'custom_header_content_right' ),
			kalium_get_theme_option( 'custom_header_content' ),
		] );
	}

	foreach ( $header_content_elements as $menu_content_entry ) {
		if ( $entries = kalium_get_array_key( $menu_content_entry, 'entries' ) ) {
			foreach ( $entries as $entry ) {
				if ( 'element' === $entry['content']['type'] ) {

					// Check for "top menu"
					if ( 'top-menu' === $menu_type && 'open-top-menu' === $entry['content']['value'] ) {
						return true;
					} // Check for "sidebar menu"
					else if ( 'sidebar-menu' === $menu_type && 'open-sidebar-menu' === $entry['content']['value'] ) {
						return true;
					}
				}
			}
		}
	}

	return false;
}

/**
 * Check if header has top bar menu.
 *
 * @return bool
 */
function kalium_header_has_top_bar() {
	return boolval( kalium_get_theme_option( 'top_header_bar' ) );
}

/**
 * Parse content builder field.
 *
 * @param string $str
 *
 * @return array
 */
function kalium_parse_content_builder_field( $str ) {

	// Parsed JSON
	$parsed = json_decode( $str, true );

	// Empty JSON to array
	if ( ! is_array( $parsed ) ) {
		$parsed = [];
	}

	// Entries list
	$entries = kalium_get_array_key( $parsed, 'entries', [] );

	// Map to properly format entries
	$entries = array_map( 'kalium_parse_content_builder_entry', $entries );

	// Options array
	$options = [

		// Alignment
		'alignment' => kalium_get_array_key( $parsed, 'alignment' ),
	];

	return [
		'options' => $options,
		'entries' => $entries,
	];
}

/**
 * Parse menu content element type and return the modified array.
 *
 * @param array $entry
 *
 * @return array
 */
function kalium_parse_content_builder_entry( $entry ) {

	// When there is no content type defined
	if ( ! isset( $entry['contentType'] ) ) {
		return $entry;
	}

	// Nav menus
	$registered_nav_menus = get_registered_nav_menus();

	// Content type
	$content_type = $entry['contentType'];

	// Content category type and value
	$content = [
		'type'  => 'element',
		'value' => $content_type,
	];

	// Parse menu entries
	if ( preg_match( '/^menu-([0-9]+)$/', $content_type, $matches ) ) {
		$content['type']  = 'menu';
		$content['value'] = $matches[1];
	} // Parse registered nav menu location
	else if ( isset( $registered_nav_menus[ $content_type ] ) ) {
		$content['type']  = 'menu';
		$content['value'] = $content_type;
	}

	// Hide on devices
	if ( isset( $entry['options']['hide_on'] ) ) {
		$hide_on         = $entry['options']['hide_on'];
		$hide_on_desktop = in_array( 'desktop', $hide_on );
		$hide_on_tablet  = in_array( 'tablet', $hide_on );
		$hide_on_mobile  = in_array( 'mobile', $hide_on );

		$entry['options']['hide_on'] = [
			'desktop' => $hide_on_desktop,
			'tablet'  => $hide_on_tablet,
			'mobile'  => $hide_on_mobile,
		];
	}

	return wp_parse_args( $entry, [
		'contentType' => '',
		'content'     => [
			'type'  => $content['type'],
			'value' => $content['value'],
		],
	] );
}

/**
 * Render menu content entries.
 *
 * @param array $content
 * @param array $args
 *
 * @return void
 */
function kalium_header_render_menu_content_entries( $content, $args = [] ) {
	static $index = 1;

	$args = wp_parse_args( $args, [
		'default_skin' => kalium_get_theme_option( 'custom_header_default_skin' ),
		'menu_depth'   => 0,
	] );

	if ( is_array( $content ) && ! empty( $content['entries'] ) ) {
		$entries = kalium_header_standard_menu_toggle_prepend_append( $content['entries'] );

		foreach ( $entries as $entry ) {
			$type    = $entry['content']['type'];
			$value   = $entry['content']['value'];
			$options = isset( $entry['options'] ) ? $entry['options'] : [];

			// Options by variable
			$option_skin  = kalium_get_array_key( $options, 'skin' );
			$option_align = kalium_get_array_key( $options, 'align' );
			$option_text  = kalium_get_array_key( $options, 'text' );

			// Inherit skin
			if ( 'inherit' === $option_skin ) {
				$option_skin = $args['default_skin'];
			}

			// Item classes
			$item_classes = [
				'header-block__item',
				'header-block__item--type-' . kalium_conditional( 'menu' === $type, 'menu-' . $value, kalium()->helpers->camelcase_to_dashes( $value ) ),
			];

			// Custom classes for item
			if ( ! empty( $entry['class'] ) ) {
				$item_classes[] = kalium()->helpers->list_classes( $entry['class'] );
			}

			// Responsive Hide on Device
			if ( isset( $options['hide_on'] ) ) {
				foreach ( $options['hide_on'] as $device => $enabled ) {
					if ( $enabled ) {
						$item_classes[] = 'header-block__item--hide-on-' . $device;
					}
				}
			}

			// Content HTML
			ob_start();

			// Handle content type
			switch ( $type ) {

				// Menu
				case 'menu':
					$option_submenu_dropdown_caret = kalium_get_array_key( $options, 'submenu_dropdown_caret' );

					// Classes
					$classes = [
						'standard-menu-container',
					];

					// Dropdown caret class
					if ( 1 !== $args['menu_depth'] && 'yes' === $option_submenu_dropdown_caret ) {
						$classes[] = 'dropdown-caret';
					}

					// Menu skin
					$classes[] = $option_skin;

					// Menu args
					$menu_args = [
						'depth' => $args['menu_depth'],
					];

					// Menu location or menu id to load
					if ( is_numeric( $value ) ) {
						$menu_args['menu'] = $value;
					} else if ( is_string( $value ) ) {
						$menu_args['theme_location'] = $value;
					}

					// Menu container
					if ( empty( $options['_is_toggle_button'] ) ) {
						kalium()->helpers->build_dom_element( [
							'tag_name'   => 'div',
							'attributes' => [
								'class' => kalium()->helpers->list_classes( $classes ),
							],
							'content'    => kalium_nav_menu( $menu_args ),
							'echo'       => true,
						] );
					}

					// Mobile menu toggle
					if ( isset( $options['_is_toggle_button'] ) ) {
						kalium_header_menu_bars_button( 'mobile-menu', $option_skin );
					}
					break;

				// Elements
				case 'element':

					// Element types
					switch ( $value ) {

						// Raw text
						case 'raw-text':
							kalium()->helpers->build_dom_element( [
								'tag_name'   => 'div',
								'attributes' => [
									'class' => kalium()->helpers->list_classes( [
										'raw-text-widget',
										$args['default_skin'],
									] ),
								],
								'content'    => wp_kses_post( do_shortcode( $option_text ) ),
								'echo'       => true,
							] );
							break;

						// Date and time
						case 'date-time':
							$date_format = kalium_get_array_key( $options, 'date_format', get_option( 'date_format' ) );
							$date        = function_exists( 'wp_date' ) ? wp_date( $date_format ) : date_i18n( $date_format );

							$date_element_attrs = [
								'class' => kalium()->helpers->list_classes( [
									'date-time-widget',
									$option_skin,
								] ),
							];

							kalium()->helpers->build_dom_element( [
								'tag_name'   => 'div',
								'attributes' => $date_element_attrs,
								'content'    => $date,
								'echo'       => true,
							] );
							break;

						// Social networks
						case 'social-networks':
							$option_style             = kalium_get_array_key( $options, 'style' );
							$option_display_structure = kalium_get_array_key( $options, 'display_structure' );

							// Social networks args
							$social_networks_args = [
								'skin'            => $option_skin,
								'style'           => '',
								'include_icon'    => in_array( $option_display_structure, [
									'icon-title',
									'icon',
								] ),
								'include_title'   => in_array( $option_display_structure, [
									'icon-title',
									'title',
								] ),
								'rounded'         => false,
								'hover_underline' => false,
							];

							// Social networks style
							switch ( $option_style ) {

								// Rounded icons
								case 'rounded':
									$social_networks_args['rounded'] = true;
									break;

								// Rounded and colored icons
								case 'rounded-colored':
									$social_networks_args['rounded'] = true;
									$social_networks_args['style']   = 'color-background';
									break;

								// Rounded icons and colored on hover
								case 'rounded-colored-hover':
									$social_networks_args['rounded'] = true;
									$social_networks_args['style']   = 'color-background-hover';
									break;

								// Colored text
								case 'colored':
									$social_networks_args['style'] = 'color-text';
									break;

								// Colored text on hover
								case 'colored-hover':
									$social_networks_args['style'] = 'color-text-hover';
									break;

								// Colored background
								case 'colored-bg':
									$social_networks_args['style'] = 'color-background';
									break;

								// Colored background on hover
								case 'colored-bg-hover':
									$social_networks_args['style'] = 'color-background-hover';
									break;
							}

							// List social networks
							kalium_social_networks( $social_networks_args );
							break;

						// Toggle standard menu
						case 'open-standard-menu':
							$option_menu_id                = kalium_get_array_key( $options, 'menu_id' );
							$option_menu_position          = kalium_get_array_key( $options, 'menu_position' );
							$option_toggle_effect          = kalium_get_array_key( $options, 'toggle_effect' );
							$option_stagger_direction      = kalium_get_array_key( $options, 'stagger_direction' );
							$option_submenu_dropdown_caret = kalium_get_array_key( $options, 'submenu_dropdown_caret' );

							// Menu vars
							$toggle_id   = kalium_get_array_key( $options, '_toggle_id' );
							$toggle_menu = kalium_get_array_key( $options, '_toggle_menu' );

							if ( preg_match( '/^menu-([0-9]+)$/', $option_menu_id, $matches ) ) {
								$option_menu_id = $matches[1];
							}

							$classes = [
								'standard-toggle-menu',
								'standard-toggle-menu--position-' . $option_menu_position,
							];

							// Wrapper start
							echo sprintf( '<div %1$s data-toggle-effect="%2$s" data-stagger-direction="%3$s" data-toggle-id="%4$s">', kalium_class_attr( $classes, false ), $option_toggle_effect, $option_stagger_direction, $toggle_id );

							// Menu
							$menu_classes = [
								'standard-toggle-menu--col',
								'standard-toggle-menu--menu',
								'standard-menu-container',
							];

							// Submenu dropdown caret
							if ( 'yes' === $option_submenu_dropdown_caret ) {
								$menu_classes[] = 'dropdown-caret';
							}

							if ( ! empty( $option_skin ) && 'inherit' !== $option_skin ) {
								$menu_classes[] = $option_skin;
							}

							// Menu items
							if ( in_array( $option_menu_position, [ 'left', 'right' ] ) || $toggle_id ) {
								echo sprintf( '<div %s>', kalium_class_attr( $menu_classes, false ) );
								echo kalium_nav_menu( $option_menu_id );
								echo '</div>';
							}

							// Toggle menu button
							if ( ! $toggle_id ) {
								echo '<div class="standard-toggle-menu--col standard-toggle-menu--button">';
								kalium_header_menu_bars_button( 'standard-menu', $option_skin, [
									'attributes' => [
										'data-toggle-id' => $toggle_menu,
									],
								] );
								echo '</div>';
							}

							// Wrapper end
							echo '</div>';
							break;

						// Open top menu
						case 'open-top-menu':
							kalium_header_menu_bars_button( 'top-menu', $option_skin );
							break;

						// Open fullscreen menu
						case 'open-fullscreen-menu':
							$option_toggled_skin     = kalium_get_array_key( $options, 'toggled_skin' );
							$option_background_color = kalium_get_array_key( $options, 'background_color' );
							$option_search_field     = kalium_get_array_key( $options, 'search_field' );
							$option_show_footer      = kalium_get_array_key( $options, 'show_footer' );
							$option_translucent_bg   = kalium_get_array_key( $options, 'translucent_bg' );

							$fullscreen_menu_options = [];

							if ( ! empty( $option_toggled_skin ) && 'inherit' !== $option_toggled_skin ) {
								$fullscreen_menu_options = array_merge( $fullscreen_menu_options, array(
									'skin_default' => $option_skin,
									'skin_active'  => $option_toggled_skin,
								) );
							}

							kalium_header_menu_bars_button( 'fullscreen-menu', $option_skin, $fullscreen_menu_options );

							// Show fullscreen menu hook
							add_action( 'kalium_header_content', function () use ( $option_skin, $option_background_color, $option_search_field, $option_show_footer, $option_translucent_bg ) {
								$fullscreen_menu_args = [
									'skin'                   => $option_skin,
									'search_field'           => kalium_validate_boolean( $option_search_field ),
									'translucent_background' => kalium_validate_boolean( $option_translucent_bg ),
									'footer_block'           => kalium_validate_boolean( $option_show_footer ),
								];

								if ( 'inherit' !== $option_background_color ) {
									$fullscreen_menu_args['skin'] = $option_background_color;
								}

								// Fullcreen menu
								kalium_header_fullscreen_menu( $fullscreen_menu_args );
							}, 30 );
							break;

						// Open sidebar menu
						case 'open-sidebar-menu':
							kalium_header_menu_bars_button( 'sidebar-menu', $option_skin );
							break;

						// Search field
						case 'search-field':
							$option_input_visibility = kalium_get_array_key( $options, 'input_visibility' );

							kalium_header_search_field( [
								'skin'             => $option_skin,
								'align'            => $option_align,
								'input_visibility' => $option_input_visibility,
							] );
							break;

						// Breadcrumb
						case 'breadcrumb':

							// When Breadcrumb NavXT is activated
							if ( kalium()->is->breadcrumb_navxt_active() ) {
								$option_separator = kalium_get_array_key( $options, 'separator' );
								$breadcrumb_id    = 'breadcrumb-' . $index;

								if ( $option_separator ) {
									kalium_append_custom_css( '.top-header-bar .breadcrumb.' . $breadcrumb_id . ' li:after', [
										'content' => "'{$option_separator}'",
									] );
								}

								echo sprintf( '<ul class="breadcrumb %2$s">%1$s</ul>', bcn_display_list( true ), $breadcrumb_id );
							}
							break;

						// WPML language switcher
						case 'wpml-language-switcher':
							$option_text_format   = kalium_get_array_key( $options, 'display_text_format' );
							$option_flag_position = kalium_get_array_key( $options, 'flag_position' );
							$option_show_on       = kalium_get_array_key( $options, 'show_on' );
							$option_skip_missing  = kalium_get_array_key( $options, 'skip_missing' );

							$wpml_switcher_args = [
								'skin'          => $option_skin,
								'flag_position' => $option_flag_position,
								'display_text'  => $option_text_format,
								'show_on'       => $option_show_on,
								'skip_missing'  => is_array( $option_skip_missing ) && in_array( 'yes', $option_skip_missing ),
							];

							kalium_wpml_language_switcher( $wpml_switcher_args );
							break;

						// Mini cart icon
						case 'woocommerce-mini-cart':
							if ( function_exists( 'kalium_woocommerce_cart_menu_icon' ) ) {
								kalium_woocommerce_cart_menu_icon( $option_skin, $option_align );
							}
							break;

						// WooCommerce Cart Totals
						case 'woocommerce-cart-totals':
							$option_total_price = kalium_get_array_key( $options, 'total_price' );
							$option_text_before = kalium_get_array_key( $options, 'text_before' );
							$option_hide_empty  = 'yes' === kalium_get_array_key( $options, 'hide_empty' );

							kalium_woocommerce_cart_totals_widget( [
								'skin'        => $option_skin,
								'total_price' => $option_total_price,
								'text_before' => $option_text_before,
								'hide_empty'  => $option_hide_empty,
							] );
							break;

						// WooCommerce My Account Link
						case 'woocommerce-account-link':
							$option_login_text     = kalium_get_array_key( $options, 'login_text' );
							$option_logged_in_text = kalium_get_array_key( $options, 'logged_in_text' );
							$option_link_icon      = kalium_get_array_key( $options, 'link_icon' );

							if ( kalium()->is->woocommerce_active() ) {
								$content        = $option_login_text;
								$widget_classes = [
									'woocommerce-account-link',
									$option_skin,
								];

								// Logged in user
								if ( is_user_logged_in() ) {
									$content = $option_logged_in_text;
								}

								// With icon
								if ( kalium_validate_boolean( $option_link_icon ) ) {
									$icon_svg = kalium_get_svg_file( 'assets/images/icons/single-neutral-circle.svg' );

									if ( trim( $content ) ) {
										$content = sprintf( '<i class="woocommerce-account-link__icon">%1$s</i> <span class="woocommerce-account-link__label">%2$s</span>', $icon_svg, $content );
									} else {
										$content = sprintf( '<i class="woocommerce-account-link__icon">%1$s</i>', $icon_svg );
									}

									$widget_classes[] = 'woocommerce-my-account-link--has-icon';
								}

								// Display my account link
								kalium()->helpers->build_dom_element( [
									'tag_name'   => 'a',
									'attributes' => [
										'href'  => wc_get_page_permalink( 'myaccount' ),
										'class' => $widget_classes,
									],
									'content'    => $content,
									'echo'       => true,
								] );
							}
							break;
					}
					break;
			}

			// Element content
			if ( $element_content = ob_get_clean() ) {

				// Show DOM element
				kalium()->helpers->build_dom_element( [
					'tag_name'   => 'div',
					'attributes' => [
						'class' => kalium()->helpers->list_classes( $item_classes ),
					],
					'content'    => $element_content,
					'echo'       => true,
				] );
			}

			// Element index counter
			$index ++;
		}
	}
}

/**
 * Append or prepend standard menu toggle in the beginning or end of row.
 *
 * @param array $entries
 *
 * @return array
 */
function kalium_header_standard_menu_toggle_prepend_append( &$entries ) {
	if ( is_array( $entries ) ) {
		foreach ( $entries as $i => $entry ) {

			// Toggle Standard Menu Button
			if ( 'open-standard-menu' === kalium_get_array_key( $entry, 'contentType' ) ) {
				$menu_position = kalium_get_array_key( $entry['options'], 'menu_position' );

				if ( in_array( $menu_position, [ 'beginning', 'end' ] ) ) {
					$prepend_append = 'beginning' === $menu_position ? 'array_unshift' : 'array_push';
					$toggle_id      = 'toggle-' . $i;

					// Set toggle ID and menu
					$entry['options']['_toggle_id']           = $toggle_id;
					$entries[ $i ]['options']['_toggle_menu'] = $toggle_id;

					$prepend_append( $entries, $entry );
				}
			} // Menu element
			else if ( 'menu' === kalium_get_array_key( $entry['content'], 'type' ) ) {
				$mobile_menu_toggle_position = kalium_get_array_key( $entry['options'], 'mobile_menu_toggle_position' );
				$toggle_position             = $i + 1;

				if ( 'no-toggle' !== $mobile_menu_toggle_position ) {
					if ( 'beginning' === $mobile_menu_toggle_position ) {
						$toggle_position = 0;
					} elseif ( 'end' === $mobile_menu_toggle_position ) {
						$toggle_position = count( $entries );
					}

					// Item extra class
					$entries[ $i ]['class'] = 'header-block__item--standard-menu-container';

					// Toggle extra class
					$entry['class'] = 'header-block__item--mobile-menu-toggle';

					// Mark as toggle button entry
					$entry['options']['_is_toggle_button'] = true;

					// Insert at position
					array_splice( $entries, $toggle_position, 0, [ $entry ] );
				}
			}
		}
	}

	return $entries;
}

/**
 * Get header option.
 *
 * @param string $option
 *
 * @return mixed|null
 */
function kalium_header_get_option( $option ) {
	if ( ! ( get_queried_object() instanceof WP_Post ) ) {
		return null;
	}

	$return            = null;
	$queried_object_id = kalium_get_queried_object_id();

	switch ( $option ) {

		// Position
		case 'position':
			$theme_option_field = $post_meta_field = 'header_position';
			break;

		// Spacing
		case 'spacing':
			$theme_option_field = $post_meta_field = 'header_spacing';

			// Ignore post meta field when header position is not defined in post meta
			if ( 'inherit' === kalium_get_field( 'header_position', $queried_object_id ) ) {
				$post_meta_field = null;
			}
			break;

		// Default
		default:
			return null;
	}

	// Get value from theme options
	$return = kalium_get_theme_option( $theme_option_field );

	// Get value from page settings
	if ( $queried_object_id > 0 && $post_meta_field ) {
		$page_value = kalium_get_field( $post_meta_field, $queried_object_id );

		if ( 'inherit' !== $page_value ) {
			$return = $page_value;
		}
	}

	return $return;
}

/**
 * New implementation of Sticky Header.
 *
 * @return array
 */
function kalium_get_sticky_header_options() {

	// Options
	$options = [

		// Sticky menu type: standard, autohide
		'type'                      => 'standard',

		// Sticky container selector
		'containerElement'          => '.site-header',

		// Logo selector
		'logoElement'               => '.logo-image',

		// Trigger hook offset relative to screen
		'triggerOffset'             => 0,

		// Initial offset for all animation scenes
		'offset'                    => 0,

		// Initial offset for animation scene (sticky type: standard)
		'animationOffset'           => 10,

		// Spacer to cover up the header height
		'spacer'                    => true,

		// Animate duration with scroll
		'animateProgressWithScroll' => true,

		// Animate duration (if not null, overwrites animation duration on timeline)
		'animateDuration'           => null,

		// Tween changes for animation scene
		'tweenChanges'              => false,

		// Sticky header class name definitions
		'classes'                   => [

			// Element name
			'name'        => 'site-header',

			// Class prefix for sticky header options
			'prefix'      => 'sticky',

			// Initialized class
			'init'        => 'initialized',

			// Menu is set to fixed
			'fixed'       => 'fixed',

			// Menu is set to absolute
			'absolute'    => 'absolute',

			// Sticky header spacer class
			'spacer'      => 'spacer',

			// Sticky header is active
			'active'      => 'active',

			// Sticky header is fully active
			'fullyActive' => 'fully-active',
		],

		// Autohide options
		'autohide'                  => [

			// Animation type
			'animationType' => 'fade-slide-top',

			// Animate duration
			'duration'      => 0.3,

			// Threshold distance before menu is shown/hide
			'threshold'     => 100,
		],

		// Props to animate
		'animateScenes'             => [],

		// Alternate logos
		'alternateLogos'            => [

			// Sticky logo
			//	'sticky'   => [
			//		'name'  => 'sticky',
			//		'image' => wp_get_attachment_image( 1, 'original' ),
			//		'css'   => [
			//			'width'  => 30,
			//			'height' => 30,
			//		],
			//	],
		],

		// Supported on devices
		'supportedOn'               => [

			// Desktop
			'desktop' => true,

			// Tablet
			'tablet'  => false,

			// Mobile
			'mobile'  => true,
		],

		// Other options
		'other'                     => [

			// Menu skin on sticky mode
			'menuSkin' => null,
		],

		// Debug mode
		'debugMode'                 => defined( 'KALIUM_DEBUG' ) && KALIUM_DEBUG,
	];

	// Animate scenes
	$animate_scenes = [];

	// Padding animation
	//	$animate_scenes['padding'] = [
	//		'name'     => 'padding',
	//		'selector' => 'self',
	//		'props'    => [
	//			'paddingTop',
	//			'paddingBottom',
	//		],
	//		'css'      => [
	//			'default' => [
	//				'paddingTop'    => 10,
	//				'paddingBottom' => 10,
	//			],
	//		],
	//	];

	// Background animation
	//	$animate_scenes['background'] = [
	//		'name'     => 'background',
	//		'selector' => 'self',
	//		'props'    => [
	//			'backgroundColor',
	//		],
	//		'css'      => [
	//			'default' => [
	//				'backgroundColor' => '#c0c0c0',
	//			],
	//		],
	//	];

	// Resize logo animation
	//	$animate_scenes['resizeLogo'] = [
	//		'name'     => 'logo-resize',
	//		'selector' => '.logo-image',
	//		'props'    => [
	//			'width',
	//			'height',
	//		],
	//		'css'      => [
	//			'default' => [
	//				'width'  => 30,
	//				'height' => 30,
	//			],
	//		],
	//	];

	// Sticky logo animation
	//	$animate_scenes['stickyLogo'] = [
	//		'name'     => 'sticky-logo',
	//		'selector' => '.logo-image',
	//		'props'    => [
	//			'width',
	//			'height',
	//		],
	//		'data'     => [
	//			'type'          => 'alternate-logo',
	//			'alternateLogo' => 'sticky',
	//		],
	//		'duration' => 0.6,
	//		'css'      => [
	//			'width'  => 40,
	//			'height' => 30,
	//		],
	//	];

	// Add animate scene array
	//	$options['animateScenes'] = $animate_scenes;

	/**
	 * Hook: kalium_sticky_header_options.
	 *
	 * @hooked kalium_sticky_header_options_default - 10
	 * @hooked kalium_sticky_options_for_static_header_type - 20
	 */
	return apply_filters( 'kalium_sticky_header_options', $options );
}

/**
 * Get logo switch sections.
 *
 * @return array
 */
function kalium_get_logo_switch_sections() {
	$sections = [];

	if ( is_singular() && kalium_get_field( 'section_logo_switch' ) ) {
		$sections = kalium_get_field( 'logo_switch_sections' );
	}

	return apply_filters( 'kalium_sticky_logo_switch_sections', $sections );
}
