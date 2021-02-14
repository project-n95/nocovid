<?php
/**
 * Kalium WordPress Theme
 *
 * Header template functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Display header template.
 */
if ( ! function_exists( 'kalium_header_display' ) ) {

	function kalium_header_display() {
		if ( ! kalium_show_header() ) {
			return;
		}

		// Load header template
		get_template_part( 'tpls/header-main' );
	}
}


/**
 * Display header content.
 */
if ( ! function_exists( 'kalium_header_content_display' ) ) {

	function kalium_header_content_display() {

		// Legacy header types
		if ( 'custom-header' !== kalium_get_theme_option( 'main_menu_type' ) ) {
			remove_action( 'kalium_header_content_main', 'kalium_header_content_left', 10 );
			remove_action( 'kalium_header_content_after', 'kalium_header_content_below', 10 );

			add_filter( 'get_data_custom_header_content_right', '_kalium_header_process_legacy_header_types' );
		}

		// Include custom header template
		kalium_get_template( 'custom-header.php' );
	}
}

/**
 * Top header bar.
 */
if ( ! function_exists( 'kalium_header_top_bar_display' ) ) {

	function kalium_header_top_bar_display() {

		// Check if enabled
		if ( ! kalium_header_has_top_bar() ) {
			return;
		}

		// Content entries
		$content_left  = kalium_parse_content_builder_field( kalium_get_theme_option( 'top_header_bar_content_left' ) );
		$content_right = kalium_parse_content_builder_field( kalium_get_theme_option( 'top_header_bar_content_right' ) );

		// Do not display if no content is available
		if ( empty( $content_left['entries'] ) && empty( $content_right['entries'] ) ) {
			return;
		}

		// Alignment
		$content_left_alignment  = kalium_get_array_key( $content_left['options'], 'alignment' );
		$content_right_alignment = kalium_get_array_key( $content_right['options'], 'alignment' );

		// Skin classes
		$container_skin = kalium_get_theme_option( 'top_header_bar_skin' );
		$entries_skin   = kalium_get_theme_option( 'top_header_bar_default_skin' );

		// Responsive visibility
		$show_on = [
			'desktop' => boolval( kalium_get_theme_option( 'top_header_bar_support_desktop', true ) ),
			'tablet'  => boolval( kalium_get_theme_option( 'top_header_bar_support_tablet', false ) ),
			'mobile'  => boolval( kalium_get_theme_option( 'top_header_bar_support_mobile', false ) ),
		];

		// Content args
		$content_args = [
			'default_skin' => $entries_skin,
			'menu_depth'   => 1,
		];

		// Container classes
		$classes = [
			'top-header-bar',
		];

		// Container skin
		if ( in_array( $container_skin, [ 'light', 'dark' ] ) ) {
			$classes[] = 'top-header-bar--skin-' . $container_skin;
		}

		// Responsive
		foreach ( $show_on as $device_type => $visible ) {
			if ( ! $visible ) {
				$classes[] = 'top-header-bar--hide-on-' . $device_type;
			}
		}

		// Row container classes
		$row_container_classes = [
			'top-header-bar__row-container',
			'top-header-bar--row-container', // @deprecated
		];

		// Assign container when fullwidth header is not present
		if ( false === kalium_is_fullwidth_header() ) {
			$row_container_classes[] = 'container';
		}

		// Left column class
		$left_column_classes = [
			'top-header-bar__column',
			'top-header-bar__column--content-left',
			'top-header-bar--column', // @deprecated
			'top-header-bar--column-content-left', // @deprecated
		];

		if ( $content_left_alignment ) {
			$left_column_classes[] = 'top-header-bar--column-alignment-' . $content_left_alignment;
		}

		// Left column class
		$right_column_classes = [
			'top-header-bar__column',
			'top-header-bar__column--content-right',
			'top-header-bar--column', // @deprecated
			'top-header-bar--column-content-right', // @deprecated
		];

		if ( $content_right_alignment ) {
			$right_column_classes[] = 'top-header-bar__column--alignment-' . $content_right_alignment;
			$right_column_classes[] = 'top-header-bar--column-alignment-' . $content_right_alignment;
		}

		// Custom background and border color
		$custom_background_color = kalium_get_theme_option( 'top_header_bar_background_color' );
		$custom_border_color     = kalium_get_theme_option( 'top_header_bar_border_color' );
		$custom_separator_color  = kalium_get_theme_option( 'top_header_bar_separator_color' );

		if ( $custom_background_color || $custom_border_color ) {
			kalium_append_custom_css( '.top-header-bar', [
				'background-color' => $custom_background_color,
				'border-bottom'    => $custom_border_color ? sprintf( '1px solid %s', $custom_border_color ) : '0px',
			] );
		}

		// Separator
		if ( $custom_separator_color ) {
			$classes[] = 'top-header-bar--with-separators';

			kalium_append_custom_css( '.top-header-bar--with-separators .header-block__item:before', [
				'background-color' => $custom_separator_color,
			] );
		}
		?>
        <div <?php kalium_class_attr( $classes ); ?>>

            <div <?php kalium_class_attr( $row_container_classes ); ?>>

                <div class="top-header-bar__row top-header-bar--row">

					<?php if ( ! empty( $content_left['entries'] ) ): ?>
                        <div <?php kalium_class_attr( $left_column_classes ); ?>>
							<?php
							// Left content entries
							kalium_header_render_menu_content_entries( $content_left, $content_args );
							?>
                        </div>
					<?php endif; ?>

					<?php if ( ! empty( $content_right['entries'] ) ): ?>
                        <div <?php kalium_class_attr( $right_column_classes ); ?>>
							<?php
							// Right content entries
							kalium_header_render_menu_content_entries( $content_right, $content_args );
							?>
                        </div>
					<?php endif; ?>

                </div>

            </div>

        </div>
		<?php
	}
}

/**
 * Header left menu content (inline with the logo).
 */
if ( ! function_exists( 'kalium_header_content_left' ) ) {

	function kalium_header_content_left() {
		// Do not show if centered logo header is not selected
		if ( 'logo-centered' !== kalium_get_theme_option( 'custom_header_type' ) ) {
			return;
		}

		$menu_content = kalium_get_theme_option( 'custom_header_content_left' );
		$content      = kalium_parse_content_builder_field( $menu_content );

		$classes = [
			'header-block__column',
			'header-block--content-left',
		];

		// Alignment
		$classes[] = 'header-block--align-' . kalium_get_array_key( $content['options'], 'alignment', 'left' );

		?>
        <div <?php kalium_class_attr( $classes ); ?>>

            <div class="header-block__items-row">
				<?php
				// Render Content Entries
				kalium_header_render_menu_content_entries( $content );
				?>
            </div>

        </div>
		<?php

	}
}

/**
 * Centered header right menu content (inline with the logo).
 */
if ( ! function_exists( 'kalium_header_content_right' ) ) {

	function kalium_header_content_right() {
		$menu_content = kalium_get_theme_option( 'custom_header_content_right' );
		$content      = kalium_parse_content_builder_field( $menu_content );

		$classes = [
			'header-block__column',
			'header-block--content-right',
		];

		// Alignment
		$classes[] = 'header-block--align-' . kalium_get_array_key( $content['options'], 'alignment', 'right' );
		?>
        <div <?php kalium_class_attr( $classes ); ?>>

            <div class="header-block__items-row">
				<?php
				// Render Content Entries
				kalium_header_render_menu_content_entries( $content );
				?>
            </div>

        </div>
		<?php

	}
}

/**
 * Header content below the logo.
 */
if ( ! function_exists( 'kalium_header_content_below' ) ) {

	function kalium_header_content_below() {
		$menu_content = kalium_get_theme_option( 'custom_header_content' );
		$content      = kalium_parse_content_builder_field( $menu_content );

		// No entries found
		if ( empty( $content['entries'] ) ) {
			return;
		}

		$classes = [
			'header-block__column',
			'header-block--content-below',
		];

		// Alignment
		$classes[] = 'header-block--align-' . kalium_get_array_key( $content['options'], 'alignment', 'left' );

		?>
        <div class="header-block__row-container container">

            <div class="header-block__row header-block__row--secondary">

                <div <?php kalium_class_attr( $classes ); ?>>

                    <div class="header-block__items-row">
						<?php
						// Render Content Entries
						kalium_header_render_menu_content_entries( $content );
						?>
                    </div>

                </div>

            </div>

        </div>
		<?php

	}
}

/**
 * Centered header logo.
 */
if ( ! function_exists( 'kalium_header_content_logo' ) ) {

	function kalium_header_content_logo() {

		?>
        <div class="header-block__column header-block__logo header-block--auto-grow">
			<?php
			// Logo element
			kalium_logo_element();
			?>
        </div>
		<?php
	}
}

/**
 * Menu bars link.
 */
if ( ! function_exists( 'kalium_header_menu_bars_button' ) ) {

	function kalium_header_menu_bars_button( $action, $skin = '', $opts = [] ) {
		$opts = wp_parse_args( $opts, [
			'skin_default' => '',
			'skin_active'  => '',
			'attributes'   => [],
		] );

		$classes = [
			'toggle-bars',
		];

		if ( ! empty( $skin ) ) {
			$classes[] = $skin;
		}

		// Other attributes
		$atts = [
			'data-action="' . esc_attr( $action ) . '"',
		];

		if ( $opts['skin_default'] && $opts['skin_active'] ) {
			$atts[] = 'data-default-skin="' . esc_attr( $opts['skin_default'] ) . '"';
			$atts[] = 'data-active-skin="' . esc_attr( $opts['skin_active'] ) . '"';
		}

		foreach ( $opts['attributes'] as $attr_name => $attr_value ) {
			$atts[] = sprintf( '%1$s="%2$s"', $attr_name, esc_attr( $attr_value ) );
		}

		// Trigger action link
		echo sprintf( '<a href="#" class="%s" %s>', kalium()->helpers->list_classes( $classes ), implode( ' ', $atts ) );

		// Icon or label
		kalium_menu_icon_or_label();

		echo '</a>';
	}
}

/**
 * Fullscreen Menu container.
 */
if ( ! function_exists( 'kalium_header_fullscreen_menu' ) ) {

	/**
	 * @param array $args {
	 *
	 * @type string $skin
	 * @type string $align
	 * @type bool   $footer_block
	 * @type bool   $translucent_background
	 * @type bool   $search_field
	 * @type bool   $submenu_dropdown_indicator
	 * @type array  $menu_args
	 * }
	 */
	function kalium_header_fullscreen_menu( $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = [];
		}

		$args = wp_parse_args( $args, [
			'skin'                       => kalium_get_theme_option( 'menu_full_bg_skin' ),
			'align'                      => kalium_get_theme_option( 'menu_full_bg_alignment' ),
			'footer_block'               => kalium_get_theme_option( 'menu_full_bg_footer_block' ),
			'translucent_background'     => kalium_get_theme_option( 'menu_full_bg_opacity' ),
			'search_field'               => kalium_get_theme_option( 'menu_full_bg_search_field' ),
			'submenu_dropdown_indicator' => kalium_get_theme_option( 'submenu_dropdown_indicator' ),
			'menu_args'                  => [],
		] );

		// Parse menu args
		$args['menu_args'] = wp_parse_args( $args['menu_args'], [
			'container'      => '',
			'echo'           => true,
			'theme_location' => 'main-menu',
		] );

		// Full bg menu classes
		$classes = [
			'fullscreen-menu',
			'full-screen-menu',
			'menu-open-effect-fade',
		];

		if ( ! empty( $args['skin'] ) ) {
			$classes[] = $args['skin'];
		}

		if ( $args['submenu_dropdown_indicator'] ) {
			$classes[] = 'submenu-indicator';
		}

		if ( 'centered-horizontal' === $args['align'] ) {
			$classes[] = 'menu-horizontally-center';
		}

		if ( in_array( $args['align'], [ 'centered', 'centered-horizontal' ] ) ) {
			$classes[] = 'menu-aligned-center';
		}

		if ( $args['footer_block'] ) {
			$classes[] = 'has-fullmenu-footer';
		}

		if ( $args['translucent_background'] ) {
			$classes[] = 'translucent-background';
		}
		?>
        <div <?php kalium_class_attr( $classes ); ?>>
            <div class="fullscreen-menu-navigation">
                <div class="container">
                    <nav>
						<?php
						// Navigation
						kalium_nav_menu( $args['menu_args'] );

						// Search field
						if ( $args['search_field'] ) : ?>
                            <form class="search-form" method="get" action="<?php echo esc_url( kalium_search_url() ); ?>" enctype="application/x-www-form-urlencoded">
                                <input id="full-bg-search-inp" type="search" class="search-field" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off"/>
                                <label for="full-bg-search-inp">
									<?php
									// Search placeholder
									printf( '%s %s', esc_html__( 'Search', 'kalium' ), '<span><i></i><i></i><i></i></span>' );
									?>
                                </label>
                            </form>
						<?php endif; ?>
                    </nav>
                </div>
            </div>

			<?php if ( $args['footer_block'] ) : ?>
                <div class="fullscreen-menu-footer">
                    <div class="container">
                        <div class="right-part">
							<?php echo do_shortcode( '[lab_social_networks rounded]' ); ?>
                        </div>
                        <div class="left-part">
							<?php echo do_shortcode( kalium_get_theme_option( 'footer_text' ) ); ?>
                        </div>
                    </div>
                </div>
			<?php endif; ?>

        </div>
		<?php
	}
}

/**
 * Header search field.
 */
if ( ! function_exists( 'kalium_header_search_field' ) ) {

	//function kalium_header_search_field( $skin = '', $align_right = false, $force_show = false, $input_visibility = '' ) {
	function kalium_header_search_field( $args = [] ) {
		$args = wp_parse_args( $args, [
			'skin'             => '',
			'align'            => '',
			'input_visibility' => '',
		] );

		// Split text plugin is required
		kalium_enqueue( 'gsap-splittext-js' );

		// Classes
		$classes = [
			'header-search-input',
		];

		// Skin class
		if ( ! empty( $args['skin'] ) ) {
			$classes[] = $args['skin'];
		}

		// Right aligned
		if ( 'right' === $args['align'] ) {
			$classes[] = 'header-search-input--align-right';
		}

		// Search input visibility
        if ( 'visible' === $args['input_visibility'] ) {
            $classes[] = 'header-search-input--input-visible';
        }

		$animation = kalium_get_theme_option( 'header_search_field_icon_animation' );
		?>
        <div <?php kalium_class_attr( $classes ); ?>>
            <form role="search" method="get" action="<?php echo esc_url( kalium_search_url() ); ?>">

                <div class="search-field">
                    <span><?php esc_html_e( 'Search site...', 'kalium' ); ?></span>
                    <input type="search" value="" autocomplete="off" name="s"/>
                </div>

                <div class="search-icon">
                    <a href="#" data-animation="<?php echo $animation; ?>">
						<?php echo kalium_get_svg_file( 'assets/images/icons/search.svg', null, [ 24, 24 ] ); ?>
                    </a>
                </div>
            </form>

        </div>
		<?php
	}
}

/**
 * Show Menu Bar (hambuger icon).
 */
if ( ! function_exists( 'kalium_menu_icon_or_label' ) ) {

	function kalium_menu_icon_or_label() {
		$menu_hamburger_custom_label = kalium_get_theme_option( 'menu_hamburger_custom_label' );

		if ( $menu_hamburger_custom_label ) {

			$label_show_text  = kalium_get_theme_option( 'menu_hamburger_custom_label_text' );
			$label_close_text = kalium_get_theme_option( 'menu_hamburger_custom_label_close_text' );
			$icon_position    = kalium_get_theme_option( 'menu_hamburger_custom_icon_position', 'left' );
			$text_only        = 'hide' === $icon_position;

			$classes = [
				'toggle-bars__column',
				'toggle-bars__column--' . kalium_conditional( 'right' === $icon_position, 'left', 'right' ),
			];

			if ( ! $text_only ) {
				$classes[] = 'toggle-bars__column--padding-' . kalium_conditional( 'left' === $icon_position, 'left', 'right' );
			}
			?>
            <span <?php kalium_class_attr( $classes ); ?>>
                <span class="toggle-bars__text toggle-bars__text--show"><?php echo esc_html( $label_show_text ); ?></span>
                <span class="toggle-bars__text toggle-bars__text--hide"><?php echo esc_html( $label_close_text ); ?></span>
            </span>
			<?php

			if ( $text_only ) {
				return;
			}
		}

		// Bars
		?>
        <span class="toggle-bars__column">
            <span class="toggle-bars__bar-lines">
                <span class="toggle-bars__bar-line toggle-bars__bar-line--top"></span>
                <span class="toggle-bars__bar-line toggle-bars__bar-line--middle"></span>
                <span class="toggle-bars__bar-line toggle-bars__bar-line--bottom"></span>
            </span>
        </span>
		<?php
	}
}

/**
 * Display mobile menu.
 */
if ( ! function_exists( 'kalium_header_display_mobile_menu' ) ) {

	function kalium_header_display_mobile_menu() {
		if ( ! kalium_show_header() ) {
			return;
		}

		// Mobile menu
		get_template_part( 'tpls/menu-mobile' );
	}
}

/**
 * Display header top menu.
 */
if ( ! function_exists( 'kalium_header_display_top_menu' ) ) {

	function kalium_header_display_top_menu() {
		if ( ! kalium_show_header() ) {
			return;
		}

		// Menu type to use
		$main_menu_type = kalium_get_theme_option( 'main_menu_type' );

		// Top menu
		if ( 'top-menu' === $main_menu_type || kalium_get_theme_option( 'menu_top_force_include' ) || kalium_header_has_content_element_type( 'top-menu' ) ) {
			get_template_part( 'tpls/menu-top' );
		}
	}
}

/**
 * Display header side menu.
 */
if ( ! function_exists( 'kalium_header_display_side_menu' ) ) {

	function kalium_header_display_side_menu() {
		if ( ! kalium_show_header() ) {
			return;
		}

		// Menu type to use
		$main_menu_type = kalium_get_theme_option( 'main_menu_type' );

		// Sidebar menu
		if ( 'sidebar-menu' === $main_menu_type || kalium_get_theme_option( 'menu_sidebar_force_include' ) || kalium_header_has_content_element_type( 'sidebar-menu' ) ) {
			get_template_part( 'tpls/menu-sidebar' );
		}
	}
}
