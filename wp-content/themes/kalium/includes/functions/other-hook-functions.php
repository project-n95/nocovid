<?php
/**
 * Kalium WordPress Theme
 *
 * Hooks functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Warn users to install ACF5 Pro.
 *
 * @return void
 */
function _kalium_acf5_warning_init() {
	$is_using_acf4      = function_exists( 'acf' ) ? version_compare( acf()->version, '4.4.12', '<=' ) : false;
	$is_using_acf5_free = false === $is_using_acf4 && function_exists( 'acf' ) && false === defined( 'ACF_PRO' );

	if ( ( $is_using_acf4 || $is_using_acf5_free ) && 'kalium-install-plugins' !== kalium()->request->query( 'page' ) ) {
		$install_button = '<button type="button" class="button" id="kalium-acf5-pro-install-button"><i class="loading"></i> Deactivate ACF4 &amp; Install ACF5 Pro</button>';
		$acf_warning    = sprintf( 'You are currently using <strong>Advanced Custom Fields &ndash; %s</strong> which will not be supported in the upcoming updates of Kalium!<br><br>Please install and activate <strong>Advanced Custom Fields PRO</strong> plugin which is bundled with the theme <em>(free of charge)</em> either by installing from <a href="%s">Appearance &gt; Install Plugins</a> or clicking the button below which will deactivate previous version and install/activate ACF5 PRO automatically: <br><br>%s<br><br><em>Note: ACF4 and its addons will not be deleted (<a href="https://d.pr/i/RbEchZ" target="_blank" rel="noopener">see here</a>), however we recommend you to delete them after installing ACF5 PRO.</em>', acf()->version, esc_url( admin_url( 'themes.php?page=kalium-install-plugins' ) ), $install_button );

		if ( $is_using_acf5_free ) {
			$install_button = '<button type="button" class="button" id="kalium-acf5-pro-install-button"><i class="loading"></i> Deactivate ACF5 (free) &amp; Install ACF5 Pro</button>';
			$acf_warning    = sprintf( 'You are currently using <strong>Advanced Custom Fields &ndash; %s (free version)</strong> which does not fully support Kalium!<br><br>Please install and activate <strong>Advanced Custom Fields PRO</strong> plugin which is bundled with the theme <em>(free of charge)</em> either by installing from <a href="%s">Appearance &gt; Install Plugins</a> or clicking the button below which will current ACF and install/activate ACF5 PRO automatically: <br><br>%s', acf()->version, esc_url( admin_url( 'themes.php?page=kalium-install-plugins' ) ), $install_button );
		}

		kalium()->helpers->add_admin_notice( $acf_warning, 'warning' );

		// Plugin disable and enable
		if ( kalium()->request->input( 'kalium_acf4_deactivate' ) && current_user_can( 'manage_options' ) ) {
			$acf4_plugin = 'advanced-custom-fields/acf.php';

			deactivate_plugins( [
				$acf4_plugin,
				'acf-flexible-content/acf-flexible-content.php',
				'acf-gallery/acf-gallery.php',
				'acf-repeater/acf-repeater.php',
			] );
			die( did_action( "deactivate_{$acf4_plugin}" ) ? '1' : '-1' );
		}
	}

	// Activate ACF5 Pro
	if ( kalium()->request->input( 'kalium_acf5_activate' ) && current_user_can( 'manage_options' ) ) {
		$acf5_plugin = 'advanced-custom-fields-pro/acf.php';
		$all_plugins = apply_filters( 'all_plugins', get_plugins() );
		$success     = - 1;

		// Install and activate the plugin
		if ( ! isset( $all_plugins[ $acf5_plugin ] ) ) {

			if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}

			// Plugin file
			$download_url = TGM_Plugin_Activation::get_instance()->get_download_url( 'advanced-custom-fields-pro' );

			$skin_args = [
				'type'   => 'upload',
				'title'  => "ACF Pro",
				'url'    => '',
				'nonce'  => 'install-plugin_advanced-custom-fields-pro',
				'plugin' => '',
				'api'    => '',
				'extra'  => [],
			];

			$skin = new Plugin_Installer_Skin( $skin_args );

			// Create a new instance of Plugin_Upgrader.
			$upgrader = new Plugin_Upgrader( $skin );
			$upgrader->install( $download_url );
			$success = 1;

			// Update list of activated plugins
			$all_plugins = apply_filters( 'all_plugins', get_plugins() );
		}

		// Plugin exists, simply activate it
		if ( isset( $all_plugins[ $acf5_plugin ] ) ) {
			activate_plugins( $acf5_plugin );
			if ( did_action( 'activated_plugin' ) ) {
				$success = 1;
			}
		}

		die( (string) $success );
	}
}

/**
 * Define debug mode in body class.
 *
 * @param array $classes
 *
 * @return array
 */
function _kalium_check_debug_bode_body_class( $classes ) {
	if ( defined( 'KALIUM_DEBUG' ) ) {
		$classes[] = 'kalium-debug';
	}

	return $classes;
}

/**
 * Post link plus mapper for WPML plugin.
 *
 * @param array $results
 *
 * @return array
 */
function _kalium_post_link_plus_result_mapper( $results ) {
	if ( kalium()->is->wpml_active() ) {
		$results_new = [];

		foreach ( $results as $result ) {
			$results_new[] = get_post( apply_filters( 'wpml_object_id', $result->ID, $result->post_type, true ) );
		}

		return $results_new;
	}

	return $results;
}

/**
 * Homepage hashtags links fix.
 *
 * @param array   $classes
 * @param WP_Post $item
 *
 * @return array
 */
function _kalium_unique_hashtag_url_base_menu_item( $classes, $item ) {
	$url = $item->url;

	// Only hashtag links
	if ( false !== strpos( $url, '#' ) ) {
		$url_md5 = ( preg_replace( '/#.*/', '', $url ) );

		// Skip first item only
		if ( ! isset( $GLOBALS['kalium_hashtag_links'][ $url_md5 ] ) ) {
			$GLOBALS['kalium_hashtag_links'][ $url_md5 ] = true;

			return $classes;
		}

		$remove_classes = [
			'current_page_item',
			'current-menu-item',
			'current-menu-ancestor',
			'current_page_ancestor',
		];

		foreach ( $remove_classes as $class_to_remove ) {
			$current_item_index = array_search( $class_to_remove, $classes );

			if ( false !== $current_item_index ) {
				unset( $classes[ $current_item_index ] );
			}
		}
	}

	return $classes;
}

/**
 * Homepage hashtags reset skipped item.
 *
 * @param array $args Array of wp_nav_menu() arguments.
 *
 * @return array
 */
function _kalium_unique_hashtag_url_base_reset( $args ) {
	$GLOBALS['kalium_hashtag_links'] = [];

	return $args;
}

/**
 * Set WooCommerce Product Filter to use as theme. Temporary fix to
 * set the plugin as theme bundled plugin.
 *
 * @param array $args
 *
 * @return array
 */
function _kalium_prdctfltr_set_as_theme( $args ) {
	$args['product_filter']['key'] = 'true';

	return $args;
}

/**
 * Map WPBakery Page Builder shortcodes for infinite scroll pagination.
 *
 * @return void
 */
function _kalium_endless_pagination_ajax_map_wpb_shortcodes() {
	if ( kalium()->is->wpb_page_builder_active() ) {
		WPBMap::addAllMappedShortcodes();
	}
}

/**
 * Custom sidebars params.
 *
 * @param array $sidebar
 *
 * @return array
 */
function _kalium_custom_sidebars_params( $sidebar ) {

	// Widget wrappers
	$before_widget = '<div id="%1$s" class="widget %2$s">';
	$after_widget  = '</div>';
	$before_title  = '<h3 class="widget-title">';
	$after_title   = '</h3>';

	$sidebar['before_widget'] = $before_widget;
	$sidebar['after_widget']  = $after_widget;
	$sidebar['before_title']  = $before_title;
	$sidebar['after_title']   = $after_title;

	return $sidebar;
}

/**
 * Password protected post form.
 *
 * @param string $output
 *
 * @return string
 */
function _kalium_the_password_form( $output ) {
	$output = str_replace( 'type="submit"', sprintf( 'type="submit" %s', 'class="button button-small"' ), $output );

	return $output;
}

/**
 * Product Filter plugin AJAX fix with WPBakery.
 *
 * @return void
 */
function _kalium_prdctfltr_map_wpb_shortcodes_fix() {
	if ( class_exists( 'WPBMap' ) ) {
		add_action( 'admin_init', 'WPBMap::addAllMappedShortcodes' );
	}
}

/**
 * Maintenance mode.
 *
 * @return void
 */
function _kalium_page_maintenance_mode() {
	$maintenance_mode = kalium_get_theme_option( 'maintenance_mode' );

	// Do not show for administrators
	if ( current_user_can( 'manage_options' ) ) {
		$maintenance_mode = kalium()->request->has( 'view-maintenance' );
	}

	// Show maintenance mode
	if ( $maintenance_mode ) {

		// Page title and description
		$page_title       = trim( kalium_get_theme_option( 'maintenance_mode_title' ) );
		$page_description = trim( kalium_get_theme_option( 'maintenance_mode_description' ) );

		if ( $page_title ) {
			add_filter( 'pre_get_document_title', kalium_hook_return_value( $page_title ) );
			add_filter( 'wpseo_title', kalium_hook_return_value( $page_title ) );
		}

		// Custom background
		$custom_bg        = kalium_get_theme_option( 'maintenance_mode_custom_bg' );
		$custom_bg_id     = kalium_get_theme_option( 'maintenance_mode_custom_bg_id' );
		$custom_bg_size   = kalium_get_theme_option( 'maintenance_mode_custom_bg_size' );
		$custom_bg_color  = kalium_get_theme_option( 'maintenance_mode_custom_bg_color' );
		$custom_txt_color = kalium_get_theme_option( 'maintenance_mode_custom_txt_color' );

		if ( $custom_bg ) {
			$image = wp_get_attachment_image_src( $custom_bg_id, 'original' );
			kalium_append_custom_css( '.maintenance-mode .wrapper', 'background: transparent !important;', '', true );
			kalium_append_custom_css( '.maintenance-mode', 'background: ' . ( $custom_bg_color ? $custom_bg_color : '' ) . ( is_array( $image ) ? ( ' url(' . $image[0] . ') ' ) : '' ) . ' no-repeat center center scroll !important; background-size: ' . $custom_bg_size . ' !important;', '', true );
		}

		if ( $custom_txt_color ) {
			kalium_append_custom_css( '.coming-soon-container p, .coming-soon-container a, .coming-soon-container .message-container', 'color: ' . $custom_txt_color . ' !important;', '', true );
			kalium_append_custom_css( '.coming-soon-container a:after', 'background-color: ' . $custom_txt_color . ' !important;', '', true );
		}

		// Hide header and footer
		add_filter( 'kalium_show_header', '__return_false' );
		add_filter( 'kalium_show_footer', '__return_false' );

		// Remove demo store notice
		remove_action( 'wp_footer', 'woocommerce_demo_store' );

		// Body classes
		kalium()->helpers->add_body_class( [ 'maintenance-mode', 'bg-main-color' ] );

		// Load template
		kalium_get_template( 'pages/maintenance.php', [
			'page_description' => $page_description,
		] );

		die();
	}
}

/**
 * Coming soon mode.
 *
 * @return void
 */
function _kalium_coming_soon_mode() {
	$coming_soon_mode = kalium_get_theme_option( 'coming_soon_mode' );

	// Do not show for administrators
	if ( current_user_can( 'manage_options' ) ) {
		$coming_soon_mode = kalium()->request->has( 'view-coming-soon' );
	}

	// Show coming soon mode
	if ( $coming_soon_mode ) {

		// Custom logo
		$logo_id = $logo_max_width = null;

		if ( kalium_get_theme_option( 'coming_soon_mode_use_uploaded_logo' ) ) {
			$logo_id        = kalium_get_theme_option( 'coming_soon_mode_custom_logo_image' );
			$logo_max_width = kalium_get_theme_option( 'coming_soon_mode_custom_logo_max_width' );
		}

		// Page title
		if ( $page_title = kalium_get_theme_option( 'coming_soon_mode_title' ) ) {
			add_filter( 'pre_get_document_title', kalium_hook_return_value( $page_title ) );
			add_filter( 'wpseo_title', kalium_hook_return_value( $page_title ) );
		}

		// Page description
		$page_description = kalium_get_theme_option( 'coming_soon_mode_description' );

		// Hide header and footer
		add_filter( 'kalium_show_header', '__return_false' );
		add_filter( 'kalium_show_footer', '__return_false' );

		// Remove demo store notice
		remove_action( 'wp_footer', 'woocommerce_demo_store' );

		// Body classes
		kalium()->helpers->add_body_class( [ 'coming-soon-mode', 'bg-main-color' ] );

		// Countdown
		$set_countdown  = kalium_get_theme_option( 'coming_soon_mode_countdown' );
		$countdown_date = strtolower( date( 'd F Y H:i:s', strtotime( kalium_get_theme_option( 'coming_soon_mode_date' ) ) ) );

		// Social networks
		$social_networks = kalium_get_theme_option( 'coming_soon_mode_social_networks' );

		// Custom background
		$custom_bg        = kalium_get_theme_option( 'coming_soon_mode_custom_bg' );
		$custom_bg_id     = kalium_get_theme_option( 'coming_soon_mode_custom_bg_id' );
		$custom_bg_size   = kalium_get_theme_option( 'coming_soon_mode_custom_bg_size' );
		$custom_bg_color  = kalium_get_theme_option( 'coming_soon_mode_custom_bg_color' );
		$custom_txt_color = kalium_get_theme_option( 'coming_soon_mode_custom_txt_color' );

		if ( $custom_bg ) {
			$image = wp_get_attachment_image_src( $custom_bg_id, 'original' );
			kalium_append_custom_css( '.coming-soon-mode .wrapper', 'background: transparent !important;', '', true );
			kalium_append_custom_css( '.coming-soon-mode', 'background: ' . ( $custom_bg_color ? $custom_bg_color : '' ) . ( is_array( $image ) ? ( ' url(' . $image[0] . ') ' ) : '' ) . ' no-repeat center center fixed !important; background-size: ' . $custom_bg_size . ' !important;', '', true );
		}

		if ( $custom_txt_color ) {
			kalium_append_custom_css( '.coming-soon-container .message-container, .coming-soon-container .message-container .logo.logo-text, .coming-soon-container .countdown-holder, .coming-soon-container p, .message-container a', 'color: ' . $custom_txt_color . ' !important;', '', true );
			kalium_append_custom_css( '.message-container a:after', 'background-color: ' . $custom_txt_color . ' !important;', '', true );
			kalium_append_custom_css( '.coming-soon-container .social-networks-env a', 'background-color: ' . $custom_txt_color . ' !important;', '', true );
		}

		if ( $custom_bg_color ) {
			kalium_append_custom_css( '.coming-soon-container .social-networks-env a i', 'color: ' . $custom_bg_color . ' !important;', '', true );
		}

		// Load page template
		kalium_get_template( 'pages/coming-soon.php', [
			'logo_id'          => $logo_id,
			'logo_max_width'   => $logo_max_width,
			'page_description' => $page_description,
			'set_countdown'    => $set_countdown,
			'countdown_date'   => $countdown_date,
			'social_networks'  => $social_networks,
		] );

		die();
	}
}

/**
 * Google Meta Theme Color (Phone).
 *
 * @return void
 */
function _kalium_google_theme_color() {
	if ( $google_theme_color = kalium_get_theme_option( 'google_theme_color' ) ) {
		echo sprintf( '<meta name="theme-color" content="%s">', esc_attr( $google_theme_color ) );
	}
}

/**
 * Holiday season text display.
 *
 * @return void
 */
function _kalium_holiday_season_display() {
	global $pagenow;

	// Hide on theme options
	if ( 'admin.php' === $pagenow && 'laborator_options' === kalium()->request->query( 'page' ) ) {
		return;
	}

	// Holiday season wishes
	if ( kalium_is_holiday_season() && ! kalium()->request->has( 'license_key' ) ) {
		echo sprintf( '<style type="text/css">#laborator-holidays { float: %1$s; padding-%1$s: 15px; padding-top: 8px; margin: 0; font-size: 11px; } #laborator-holidays ~ #of_container { clear: both; margin-top: 35px; }</style>', is_rtl() ? 'left' : 'right' );

		add_action( 'admin_notices', function () {
			kalium_enqueue( 'fontawesome-css' );
			echo '<p id="laborator-holidays">Happy Holiday Season from <strong>Laborator</strong> team <i class="fa fa-tree"></i></p>';
		} );
	}
}

/**
 * Favicon from theme options.
 *
 * @return void
 */
function _kalium_theme_options_favicon() {
	$favicon_image    = kalium_get_theme_option( 'favicon_image' );
	$apple_touch_icon = kalium_get_theme_option( 'apple_touch_icon' );

	if ( has_site_icon() ) {
		return;
	}

	if ( $favicon_image || $apple_touch_icon ) {
		if ( is_numeric( $favicon_image ) ) {
			$favicon_image = wp_get_attachment_image_src( $favicon_image, 'full' );

			if ( $favicon_image ) {
				$favicon_image = $favicon_image[0];
			}
		}

		if ( is_numeric( $apple_touch_icon ) ) {
			$apple_touch_icon = wp_get_attachment_image_src( $apple_touch_icon, 'full' );

			if ( $apple_touch_icon ) {
				$apple_touch_icon = $apple_touch_icon[0];
			}
		}
		?>
		<?php if ( $favicon_image ) : ?>
            <link rel="shortcut icon" href="<?php echo esc_attr( $favicon_image ); ?>">
		<?php endif; ?>
		<?php if ( $apple_touch_icon ) : ?>
            <link rel="apple-touch-icon" href="<?php echo esc_attr( $apple_touch_icon ); ?>">
            <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_attr( $apple_touch_icon ); ?>">
		<?php endif; ?>
		<?php
	}
}

/**
 * Text line below user name on single post page.
 *
 * @param WP_User $profileuser
 */
function _kalium_user_custom_text( $profileuser ) {
	$user_custom_text = isset( $profileuser->_user_custom_text ) ? $profileuser->_user_custom_text : '';
	?>
    <tr>
        <th scope="row">
            User custom text
        </th>
        <td>
            <input type="text" name="user_custom_text" id="user_custom_text" value="<?php echo esc_attr( $user_custom_text ); ?>" class="regular-text"/>
            <span class="description">Enter text to display below user name in single post. Default: user role.</span>
        </td>
    </tr>
	<?php
}

/**
 * Display user custom text on single post page.
 *
 * @param string $text
 *
 * @return string
 */
function _kalium_user_custom_text_display( $text ) {
	global $post;

	if ( $user_custom_text = get_user_meta( $post->post_author, '_user_custom_text', true ) ) {
		return $user_custom_text;
	}

	return $text;
}

/**
 * Save custom user text.
 *
 * @param int $user_id
 */
function _kalium_user_custom_text_save( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) ) {
		$user_custom_text = kalium()->request->input( 'user_custom_text' );
		update_user_meta( $user_id, '_user_custom_text', $user_custom_text );
	}
}

