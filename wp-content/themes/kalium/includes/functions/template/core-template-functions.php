<?php
/**
 * Kalium WordPress Theme
 *
 * Core template functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Logo element.
 */
if ( ! function_exists( 'kalium_logo_element' ) ) {

	/**
	 * @param int $attachment_id
	 * @param int $max_width
	 */
	function kalium_logo_element( $attachment_id = null, $max_width = null ) {

		// Vars
		$args = [
			'logo_image' => [],
			'logo_name'  => kalium_get_theme_option( 'logo_text' ),
			'link'       => apply_filters( 'kalium_logo_url', home_url() ),
		];

		// Classes
		$classes = [
			'header-logo',
		];

		// Logo vars
		$use_image_logo        = kalium_get_theme_option( 'use_uploaded_logo' );
		$logo_attachment_id    = kalium_get_theme_option( 'custom_logo_image' );
		$logo_max_width        = kalium_get_theme_option( 'custom_logo_max_width' );
		$logo_max_width_mobile = kalium_get_theme_option( 'custom_logo_mobile_max_width' );

		// Get logo from arguments
		if ( $attachment_id && wp_get_attachment_image_src( $attachment_id ) ) {
			$use_image_logo     = true;
			$logo_attachment_id = $attachment_id;
			$logo_max_width     = $max_width;
		}

		// Logo image
		if ( $use_image_logo && ( $logo_image = wp_get_attachment_image_src( $logo_attachment_id, 'full' ) ) ) {

			// Image details
			$image_url    = $logo_image[0];
			$image_width  = $logo_image[1];
			$image_height = $logo_image[2];

			// Logo max width
			if ( is_numeric( $logo_max_width ) && $logo_max_width > 0 ) {
				$resized      = kalium()->helpers->resize_by_width( $image_width, $image_height, $logo_max_width );
				$image_width  = $resized[0];
				$image_height = $resized[1];

				// Resize logo CSS
				kalium_append_custom_css( '.logo-image', sprintf( 'width:%dpx;height:%dpx;', $image_width, $image_height ) );
			}

			// Logo max width on mobile
			if ( is_numeric( $logo_max_width_mobile ) && $logo_max_width_mobile > 0 ) {
				$resized = kalium()->helpers->resize_by_width( $image_width, $image_height, $logo_max_width_mobile );

				// Resize logo CSS
				kalium_append_custom_css( '.logo-image', sprintf( 'width:%dpx;height:%dpx;', $resized[0], $resized[1] ), sprintf( 'screen and (max-width: %dpx)', kalium_get_mobile_menu_breakpoint() ) );
			}

			// Define logo image
			$args['logo_image'] = [
				'src'    => $image_url,
				'width'  => $image_width,
				'height' => $image_height,
			];

			// Add logo image class
			$classes[] = 'logo-image';
		} else {

			// Add logo text class
			$classes[] = 'logo-text';

			// Logo skin
            $classes[] = kalium_get_theme_option( 'custom_header_default_skin' );
		}

		// Pass classes as template argument
		$args['classes'] = $classes;

		// Logo element
		kalium_get_template( 'elements/logo.php', $args );
	}
}

/**
 * Dynamic sidebar implementation for Kalium.
 */
if ( ! function_exists( 'kalium_dynamic_sidebar' ) ) {

	/**
	 * @param string       $sidebar_id
	 * @param array|string $class
	 *
	 * @return void
	 */
	function kalium_dynamic_sidebar( $sidebar_id, $class = '' ) {
		$classes = [ 'widget-area' ];

		if ( is_array( $class ) ) {
			$classes = array_merge( $classes, $class );
		} else if ( ! empty( $class ) ) {
			$classes[] = $class;
		}

		?>
        <div <?php kalium_class_attr( apply_filters( 'kalium_widget_area_classes', $classes, $sidebar_id ) ); ?> role="complementary">
			<?php
			// Show sidebar widgets
			dynamic_sidebar( $sidebar_id );
			?>
        </div>
		<?php
	}
}

/**
 * Kalium social network link.
 */
if ( ! function_exists( 'kalium_social_network_link' ) ) {

	/**
	 * @param string $social_network
	 * @param array  $args
	 *
	 * @return void
	 */
	function kalium_social_network_link( $social_network, $args = [] ) {

		// Social networks list
		static $social_networks;

		if ( empty( $social_networks ) ) {
			$social_networks = apply_filters( 'kalium_social_network_link_list', [
				'facebook'    => [
					'title' => 'Facebook',
					'icon'  => 'fab fa-facebook',
				],
				'instagram'   => [
					'title' => 'Instagram',
					'icon'  => 'fab fa-instagram',
				],
				'twitter'     => [
					'title' => 'Twitter',
					'icon'  => 'fab fa-twitter',
				],
				'behance'     => [
					'title' => 'Behance',
					'icon'  => 'fab fa-behance',
				],
				'youtube'     => [
					'title' => 'YouTube',
					'icon'  => 'fab fa-youtube',
				],
				'github'      => [
					'title' => 'GitHub',
					'icon'  => 'fab fa-github',
				],
				'linkedin'    => [
					'title' => 'LinkedIn',
					'icon'  => 'fab fa-linkedin',
				],
				'vimeo'       => [
					'title' => 'Vimeo',
					'icon'  => 'fab fa-vimeo',
				],
				'whatsapp'    => [
					'title' => 'WhatsApp',
					'icon'  => 'fab fa-whatsapp',
				],
				'snapchat'    => [
					'title' => 'Snapchat',
					'icon'  => 'fab fa-snapchat-ghost',
				],
				'dribbble'    => [
					'title' => 'Dribbble',
					'icon'  => 'fab fa-dribbble'
				],
				'pinterest'   => [
					'title' => 'Pinterest',
					'icon'  => 'fab fa-pinterest',
				],
				'spotify'     => [
					'title' => 'Spotify',
					'icon'  => 'fab fa-spotify',
				],
				'skype'       => [
					'title' => 'Skype',
					'icon'  => 'fab fa-skype',
				],
				'tumblr'      => [
					'title' => 'Tumblr',
					'icon'  => 'fab fa-tumblr',
				],
				'soundcloud'  => [
					'title' => 'SoundCloud',
					'icon'  => 'fab fa-soundcloud',
				],
				'500px'       => [
					'title' => '500px',
					'icon'  => 'fab fa-500px',
				],
				'xing'        => [
					'title' => 'Xing',
					'icon'  => 'fab fa-xing',
				],
				'email'       => [
					'title' => __( 'Email', 'kalium' ),
					'icon'  => 'far fa-envelope',
				],
				'yelp'        => [
					'title' => 'Yelp',
					'icon'  => 'fab fa-yelp',
				],
				'tripadvisor' => [
					'title' => 'TripAdvisor',
					'icon'  => 'fab fa-tripadvisor',
				],
				'twitch'      => [
					'title' => 'Twitch',
					'icon'  => 'fab fa-twitch',
				],
				'houzz'       => [
					'title' => 'Houzz',
					'icon'  => 'fab fa-houzz',
				],
				'deviantart'  => [
					'title' => 'DeviantArt',
					'icon'  => 'fab fa-deviantart',
				],
				'vkontakte'   => [
					'title' => 'VKontakte',
					'icon'  => 'fab fa-vk',
				],
				'flickr'      => [
					'title' => 'Flickr',
					'icon'  => 'fab fa-flickr',
				],
				'foursquare'  => [
					'title' => 'Foursquare',
					'icon'  => 'fab fa-foursquare',
				],
			] );
		}

		// Social network link args
		$args = wp_parse_args( $args, [

			// Link
			'link'                   => '',
			'link_target'            => '_blank',

			// Elements to include
			'include_icon'           => true,
			'include_title'          => false,

			// Rounded style
			'rounded'                => false,

			// Style
			'color_text'             => false,
			'color_text_hover'       => false,
			'color_background'       => false,
			'color_background_hover' => false,

			// Other args
			'skin'                   => 'default', // default, light, dark
			'hover_underline'        => false,
		] );

		// Social network entry
		if ( is_array( $social_network ) ) {

			if ( isset( $social_network['id'] ) && isset( $social_network['title'] ) && isset( $social_network['icon'] ) ) {
				$social_network_args = [
					'title' => $social_network['title'],
					'icon'  => $social_network['icon'],
				];
				$social_network      = $social_network['id'];
			} else {
				return;
			}
		} else if ( isset( $social_networks[ $social_network ] ) ) {
			$social_network_args = $social_networks[ $social_network ];
		} else {
			return;
		}

		// Link
		$link = $args['link'];

		// Empty link
		if ( empty( $link ) ) {
			$link = '#';
		}

		// Link classes
		$classes = [
			'social-network-link',
			'sn-' . $social_network,
		];

		// Valid skins
		$skins = [
			// Skins
			'default'         => 'default',
			'dark'            => 'dark',
			'light'           => 'light',

			// Fallback for header skin
			'menu-skin-main'  => 'default',
			'menu-skin-dark'  => 'dark',
			'menu-skin-light' => 'light',
		];

		// Add skin if exists
		if ( isset( $skins[ $args['skin'] ] ) ) {
			$classes[] = 'sn-skin-' . $skins[ $args['skin'] ];
		}

		/**
		 * Config
		 */
		// Icon and title cannot be removed together
		if ( ! $args['include_icon'] && ! $args['include_title'] ) {
			$args['include_icon'] = true;
		}

		// Rounded icon
		if ( $args['rounded'] ) {
			$classes[] = 'sn-rounded';

			// Disable title, include only icon
			$args['include_icon']  = true;
			$args['include_title'] = false;
		}

		// Disable hover underline for icon only
		if ( $args['include_icon'] && ! $args['include_title'] ) {
			$args['hover_underline'] = false;
		}

		// When has color background
		if ( $args['color_background'] || $args['color_background_hover'] ) {
			$classes[] = 'sn-has-color-background';
		}

		// When both icon and title are showing
		if ( $args['include_icon'] && $args['include_title'] ) {
			$classes[] = 'sn-icon-and-title';
		}

		// Hover underline
		if ( $args['hover_underline'] ) {
			$classes[] = 'sn-hover-underline';
		}

		/**
		 * Style
		 */
		// Color text
		if ( $args['color_text'] ) {
			$classes[] = 'sn-style-color-text';
		}

		// Color text on hover
		if ( $args['color_text_hover'] ) {
			$classes[] = 'sn-style-color-text-hover';
		}

		// Color background
		if ( $args['color_background'] ) {
			$classes[] = 'sn-style-color-background';
		}

		// Color background on hover
		if ( $args['color_background_hover'] ) {
			$classes[] = 'sn-style-color-background-hover';
		}

		// Icon classes
		$icon_classes = [
			'sn-column',
			'sn-icon',
			'sn-text',
		];

		// Title classes
		$title_classes = [
			'sn-column',
			'sn-title',
			'sn-text',
		];

		// DOM Element
		?>
        <a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $args['link_target'] ); ?>" rel="noopener noreferrer" <?php kalium_class_attr( $classes ); ?>>
			<?php if ( $args['include_icon'] ) : ?>
                <span <?php kalium_class_attr( $icon_classes ); ?>>
                <i class="<?php echo esc_attr( apply_filters( 'kalium_social_network_link_icon', $social_network_args['icon'], $social_network_args ) ); ?>"></i>
                </span>
			<?php endif; ?>

			<?php if ( $args['include_title'] ) : ?>
                <span <?php kalium_class_attr( $title_classes ); ?>>
					<?php echo esc_html( apply_filters( 'kalium_social_network_link_title', $social_network_args['title'], $social_network_args ) ); ?>
                </span>
			<?php endif; ?>
        </a>
		<?php
	}
}

/**
 * Show social networks list from theme options.
 */
if ( ! function_exists( 'kalium_social_networks' ) ) {

	/**
	 * @param array $args {
	 *
	 * @type string skin
	 * @type string style
	 * @type string target
	 * @type bool include_icon
	 * @type bool include_title
	 * @type bool rounded
	 * @type bool hover_underline
	 * }
	 */
	function kalium_social_networks( $args = [] ) {

		// Social network args
		$args = wp_parse_args( $args, [
			'skin'            => '',
			'style'           => '',
			'target'          => '_blank',
			'include_icon'    => true,
			'include_title'   => false,
			'rounded'         => false,
			'hover_underline' => false,
		] );

		// Ordered social networks
		$social_networks_list = kalium_get_social_networks_list();

		// List social network links
		if ( ! empty( $social_networks_list ) ) {

			// Classes
			$classes = [
				'social-networks-links',
			];

			echo sprintf( '<ul %s>', kalium_class_attr( $classes, false ) );

			foreach ( $social_networks_list as $social_network => $social_network_args ) {

				// Link URL
				$link_url = $social_network_args['link'];

				// Email link
				if ( ! empty( $social_network_args['data']['is_email'] ) ) {
					$subject  = $social_network_args['data']['email_subject'];
					$link_url = "mailto:{$link_url}";

					// Email subject
					if ( ! empty( $subject ) ) {
						$link_url .= "?subject={$subject}";
					}
				}

				// Style configuration
				$style_args = [

					// Style
					'color_text'             => false,
					'color_text_hover'       => false,
					'color_background'       => false,
					'color_background_hover' => false,
				];

				switch ( $args['style'] ) {

					// Colored text
					case 'color-text':
						$style_args['color_text'] = true;
						$args['hover_underline']  = true;
						break;

					// Colored text on hover
					case 'color-text-hover':
						$style_args['color_text_hover'] = true;
						$args['hover_underline']        = true;
						break;

					// Colored background
					case 'color-background':
						$style_args['color_background'] = true;
						break;

					// Colored background on hover
					case 'color-background-hover':
						$style_args['color_background_hover'] = true;
						break;

					// Default
					default:
						if ( $args['include_title'] ) {
							$args['hover_underline'] = true;
						}
				}

				// Social network link args
				$social_network_link_args = [

					// Link
					'link'                   => $link_url,
					'target'                 => $args['target'],

					// Title and icon
					'include_icon'           => $args['include_icon'],
					'include_title'          => $args['include_title'],

					// Rounded icons
					'rounded'                => $args['rounded'],

					// Hover underline
					'hover_underline'        => $args['hover_underline'],

					// Skin
					'skin'                   => $args['skin'],

					// Style
					'color_text'             => $style_args['color_text'],
					'color_text_hover'       => $style_args['color_text_hover'],
					'color_background'       => $style_args['color_background'],
					'color_background_hover' => $style_args['color_background_hover'],
				];

				// Custom social network
				if ( 'custom' === $social_network ) {
					$social_network = [
						'id'    => 'custom',
						'title' => $social_network_args['data']['title'],
						'icon'  => $social_network_args['data']['icon'],
					];
				}

				// Entry wrapper start
				echo sprintf( '<li %s>', kalium_class_attr( [ 'social-networks-links--entry' ], false ) );

				// Social network link
				kalium_social_network_link( $social_network, $social_network_link_args );

				// Entry wrapper end
				echo '</li>';
			}

			echo '</ul>';
		}
	}
}

/**
 * Fire the wp_body_open action, backward compatibility to support pre-5.2.0 WordPress versions.
 *
 * @since 3.0
 */
if ( ! function_exists( 'wp_body_open' ) ) {

	function wp_body_open() {
		do_action( 'wp_body_open' );
	}
}

/**
 * Theme borders.
 */
if ( ! function_exists( 'kalium_display_page_borders' ) ) {

	function kalium_display_page_borders() {

		// Theme borders
		if ( kalium_get_theme_option( 'theme_borders' ) ) {
			get_template_part( 'tpls/borders' );
		}
	}
}

/**
 * Display theme footer.
 */
if ( ! function_exists( 'kalium_display_footer' ) ) {

	function kalium_display_footer() {

		// Footer template
		if ( apply_filters( 'kalium_show_footer', true ) ) {
			get_template_part( 'tpls/footer-main' );
		}
	}
}

/**
 * Page heading title display.
 */
if ( ! function_exists( 'kalium_page_heading_title_display' ) ) {

	function kalium_page_heading_title_display() {

		// Queried object
		$queried_object_id = kalium_get_queried_object_id();

		// Do not show on archive pages
		if ( ! is_singular() || ! $queried_object_id ) {
			return;
		}

		// Show heading title if allowed
		if ( kalium_get_field( 'heading_title', $queried_object_id ) ) {

			// Template args
			$args = [
				'heading_tag' => 'h1',
			];

			// Vars
			$current_post       = get_post( $queried_object_id );
			$title_type         = kalium_get_field( 'page_heading_title_type', $queried_object_id );
			$description_type   = kalium_get_field( 'page_heading_description_type', $queried_object_id );
			$custom_title       = kalium_get_field( 'page_heading_custom_title', $queried_object_id );
			$custom_description = kalium_get_field( 'page_heading_custom_description', $queried_object_id );

			// Sanitize title and description
			$custom_title       = wp_kses_post( $custom_title );
			$custom_description = kalium_format_content( wp_kses_post( $custom_description ) );

			// Set current post
			setup_postdata( $current_post );

			// Inherit from post title
			if ( 'post_title' === $title_type ) {
				$custom_title = apply_filters( 'the_title', get_the_title() );
			}

			// Inherit from post content
			if ( 'post_content' === $description_type ) {
				$custom_description = apply_filters( 'the_content', get_the_content() );
			}

			// Pass as template args
			$args['title']       = $custom_title;
			$args['description'] = $custom_description;

			/* @deprecated 3.1 */
			define( 'HEADING_TITLE_DISPLAYED', true );

			// Reset post data
			wp_reset_postdata();

			// Load page heading template
			kalium_get_template( 'global/page-heading.php', $args );
		}
	}
}

/**
 * Theme breadcrumb.
 */
if ( ! function_exists( 'kalium_breadcrumb_display' ) ) {

	/**
	 * Breadcrumb display
	 *
	 * @return void
	 * @since 3.1
	 */
	function kalium_breadcrumb_display() {
		if ( ! kalium()->is->breadcrumb_navxt_active() ) {
			return;
		}

		// Current Object ID
		$object_id = kalium_get_queried_object_id();

		// Breadcrumb display states
		$breadcrumb_show       = kalium_validate_boolean( kalium_get_theme_option( 'breadcrumb' ) );
		$breadcrumb_force_show = apply_filters( 'kalium_breadcrumb_force_show', null );

		// In-page breadcrumb display options
		if ( is_null( $breadcrumb_force_show ) && is_singular() && in_array( kalium_get_field( 'breadcrumb', $object_id ), [
				'enable',
				'disable'
			] ) ) {
			$breadcrumb_force_show = 'enable' === kalium_get_field( 'breadcrumb', $object_id );
		}

		// Breadcrumb can display
		if ( $breadcrumb_show || $breadcrumb_force_show ) {

			// Breadcrumb classes
			$classes = [
				'breadcrumb',
			];

			// Container classes
			$container_classes = [
				'breadcrumb__container',
			];

			// Style
			$style_props = [];

			$background_color = kalium_get_theme_option( 'breadcrumb_background_color' );
			$text_color       = kalium_get_theme_option( 'breadcrumb_text_color' );
			$border_color     = kalium_get_theme_option( 'breadcrumb_border_color' );
			$border_type      = kalium_get_theme_option( 'breadcrumb_border_type' );
			$text_align       = kalium_get_theme_option( 'breadcrumb_alignment' );

			$visibility = array_map( 'kalium_validate_boolean', [
				'home'            => kalium_get_theme_option( 'breadcrumb_visibility_homepage' ),
				'portfolio'       => kalium_get_theme_option( 'breadcrumb_visibility_portfolio' ),
				'blog'            => kalium_get_theme_option( 'breadcrumb_visibility_blog' ),
				'search'          => kalium_get_theme_option( 'breadcrumb_visibility_search' ),
				'not_found'       => kalium_get_theme_option( 'breadcrumb_visibility_404' ),
				'header_absolute' => kalium_get_theme_option( 'breadcrumb_visibility_absolute_header' ),
			] );

			$responsive = array_map( 'kalium_validate_boolean', [
				'desktop' => kalium_get_theme_option( 'breadcrumb_support_desktop', true ),
				'tablet'  => kalium_get_theme_option( 'breadcrumb_support_tablet', true ),
				'mobile'  => kalium_get_theme_option( 'breadcrumb_support_mobile', true ),
			] );

			// Custom breadcrumb parameters for current single page/post item
			if ( is_singular() ) {
				$_breadcrumb = kalium_get_field( 'breadcrumb', $object_id );

				// Hide breadcrumb
				if ( 'disable' === $_breadcrumb ) {
					return;
				} // Show
				else if ( 'enable' === $_breadcrumb ) {

					// Custom background color
					if ( $_background_color = kalium_get_field( 'breadcrumb_background_color', $object_id ) ) {
						$background_color = $_background_color;
					}

					// Custom text color
					if ( $_text_color = kalium_get_field( 'breadcrumb_text_color', $object_id ) ) {
						$text_color = $_text_color;
					}

					// Custom border color
					if ( $_border_color = kalium_get_field( 'breadcrumb_border_color', $object_id ) ) {
						$border_color = $_border_color;
					}

					// Custom border type
					if ( ( $_border_type = kalium_get_field( 'breadcrumb_border_type', $object_id ) ) && in_array( $_border_type, [
							'border',
							'border-horizontal'
						] ) ) {
						$border_type = $_border_type;
					}

					// Text alignment
					if ( ( $_text_align = kalium_get_field( 'breadcrumb_text_alignment', $object_id ) ) && in_array( $_text_align, [
							'left',
							'center',
							'right'
						] ) ) {
						$text_align = $_text_align;
					}
				}
			}

			// Style: Background
			if ( $background_color ) {
				$container_classes[] = 'breadcrumb__container--has-background';
				$container_classes[] = 'breadcrumb__container--has-padding';

				$style_props['background-color'] = $background_color;
			}

			// Style: Border
			if ( 'border-horizontal' === $border_type ) {
				$container_classes[] = 'breadcrumb__container--border-horizontal';
				$container_classes[] = 'breadcrumb__container--has-padding-horizontal';

				$style_props['border-color'] = $border_color;
			} else if ( 'border' === $border_type ) {
				$container_classes[] = 'breadcrumb__container--border';
				$container_classes[] = 'breadcrumb__container--has-padding';

				$style_props['border-color'] = $border_color;
			}

			// Style: Text
			if ( $text_color ) {
				$container_classes[] = 'breadcrumb__container--has-text-color';

				$style_props['color'] = $text_color;
			}

			// Style: Text alignment
			if ( in_array( $text_align, [ 'left', 'center', 'right' ] ) ) {
				$container_classes[] = 'breadcrumb__container--align-' . $text_align;
			}

			// Visibility check
			if ( ! $breadcrumb_force_show ) {
				if ( ! $visibility['home'] && is_front_page() ) {
					return;
				} else if ( ! $visibility['portfolio'] && ( is_post_type_archive( 'portfolio' ) || is_singular( 'portfolio' ) ) ) {
					return;
				} else if ( ! $visibility['blog'] && ( is_post_type_archive( 'post' ) || is_singular( 'post' ) ) ) {
					return;
				} else if ( ! $visibility['search'] && is_search() ) {
					return;
				} else if ( ! $visibility['not_found'] && is_404() ) {
					return;
				} else if ( ! $visibility['header_absolute'] && 'absolute' === kalium_header_get_option( 'position' ) ) {
					return;
				}
			}

			// Responsive settings
			if ( ! $responsive['desktop'] ) {
				$classes[] = 'breadcrumb--hide-on-desktop';
			}

			if ( ! $responsive['tablet'] ) {
				$classes[] = 'breadcrumb--hide-on-tablet';
			}

			if ( ! $responsive['mobile'] ) {
				$classes[] = 'breadcrumb--hide-on-mobile';
			}

			// Object ID (used as selector for styling)
			$selector_id = $object_id;

			if ( is_search() ) {
				$selector_id = 'search';
			} else if ( is_post_type_archive() ) {
				$selector_id = $GLOBALS['wp_query']->get( 'post_type' );
			} else if ( is_archive() ) {
				$object = get_queried_object();

				if ( $object instanceof WP_Term ) {
					$selector_id = $object->taxonomy;
				}
			}

			// Selector
			$selector = 'breadcrumb-' . $selector_id;

			// Breadcrumb ID
			$classes[] = $selector;

			// Breadcrumb trail
			$breadcrumb_html = bcn_display( true );

			if ( ! $breadcrumb_html ) {
				return;
			}

			// Template args
			$args = [
				'classes'           => $classes,
				'container_classes' => array_unique( $container_classes ),
				'breadcrumb_html'   => $breadcrumb_html,
			];

			// Load template
			kalium_get_template( 'global/breadcrumb.php', $args );

			// Style
			if ( ! empty( $style_props ) ) {
				kalium_append_custom_css( ".{$selector} .breadcrumb__container", $style_props );
			}
		}
	}
}
