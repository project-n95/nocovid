<?php
/**
 * Kalium WordPress Theme
 *
 * Theme plugins class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Theme_Plugins {

	/**
	 * Theme plugins registered on TGMPA.
	 *
	 * @var array
	 */
	private $theme_plugins = [];

	/**
	 * TGMPA AJAX button labels.
	 *
	 * @var array
	 */
	private $tgmpa_ajax_button_strings = [

		// Install button
		'install'  => [
			'title'      => 'Install',
			'processing' => 'Installing&hellip;',
		],

		// Activate button
		'activate' => [
			'title'      => 'Activate',
			'processing' => 'Activating&hellip;',
		],

		// Update button
		'update'   => [
			'title'      => 'Update',
			'processing' => 'Updating&hellip;',
		],

		// Active state button
		'active'   => [
			'title'      => 'Active',
			'processing' => '',
		],
	];

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Register TGMPA Plugins
		add_action( 'tgmpa_register', [ $this, '_register_tgmpa_plugins' ] );

		// Install, update or activate plugins with TGMPA via AJAX
		add_action( 'wp_ajax_kalium-plugins-tgmpa-install', [ $this, '_plugins_install_ajax' ] );
	}

	/**
	 * Third party plugins for Kalium.
	 *
	 * @return void
	 */
	public function _register_tgmpa_plugins() {

		// Retrieve plugins data
		$this->init_theme_plugins_data();

		// Required plugins notice
		$this->show_required_plugins_notices();

		// Plugins list
		$plugins = $this->get_plugins_list();

		// TGMPA Config
		$config = [
			'id'           => 'kalium',
			'menu'         => 'kalium-install-plugins',
			'has_notices'  => false,
			'is_automatic' => false,
		];

		// Init TGMPA
		tgmpa( $plugins, $config );

		// Set plugin source for bundled plugins only
		add_filter( 'upgrader_pre_download', [ $this, '_set_source_for_bundled_plugins' ], 1000, 3 );
	}

	/**
	 * Install, update or activate plugins with TGMPA via AJAX.
	 *
	 * @return void
	 */
	public function _plugins_install_ajax() {
		global $tgmpa;

		// Only for users with "install_plugins" capability
		if ( current_user_can( 'install_plugins' ) ) {

			// Plugin vars
			$plugin_slug        = kalium()->request->query( 'plugin' );
			$is_install_plugin  = 'install-plugin' === kalium()->request->query( 'tgmpa-install' );
			$is_activate_plugin = 'activate-plugin' === kalium()->request->query( 'tgmpa-activate' );
			$is_update_plugin   = 'update-plugin' === kalium()->request->query( 'tgmpa-update' );

			// Initalize TGMPA plugins
			$this->_register_tgmpa_plugins();

			// Plugin Install hook
			if ( $is_install_plugin ) {

				// After process completes
				add_action( 'upgrader_process_complete', function () use ( $plugin_slug ) {

					// Avoid any redirect after plugin is activated
					add_filter( 'wp_redirect', kalium_hook_return_value( '' ), 1000 );

					// Activate plugin
					$this->activate_plugin( $plugin_slug );

					// Custom hooks after plugin is activated
					$this->plugins_after_install_ajax_hooks( 'install', $plugin_slug );

					// Button state
					$button_state    = 'active';
					$button_text     = $this->tgmpa_ajax_button_strings[ $button_state ]['title'];
					$button_url      = '';
					$processing_text = '';

					// Activate plugin state
					if ( false === kalium()->is->plugin_active( kalium()->helpers->get_plugin_basename( $plugin_slug ) ) ) {
						$button_state    = 'activate';
						$button_text     = $this->tgmpa_ajax_button_strings[ $button_state ]['title'];
						$processing_text = $this->tgmpa_ajax_button_strings[ $button_state ]['processing'];
						$button_url      = $this->get_install_plugin_ajax_link( $plugin_slug, $button_state );
					}

					// Response
					$response = [
						'success'         => true,
						'action'          => 'install-plugin',
						'button_state'    => $button_state,
						'button_text'     => $button_text,
						'button_url'      => $button_url,
						'processing_text' => $processing_text,
					];

					// Output response
					echo sprintf( '<script type="text/template" class="kalium-tgmpa-ajax-response">%s</script>', wp_json_encode( $response ) );
				} );
			} // Plugin Update hook
            elseif ( $is_update_plugin ) {

				// Was the plugin update before update
				$plugin_basename   = kalium()->helpers->get_plugin_basename( $plugin_slug );
				$was_plugin_active = kalium()->is->plugin_active( $plugin_basename );

				// After process completes
				add_action( 'upgrader_process_complete', function () use ( $plugin_slug, $was_plugin_active ) {

					// Avoid any redirect after plugin is activated
					add_filter( 'wp_redirect', kalium_hook_return_value( '' ), 1000 );

					// Activate plugin if it was previously active
					if ( $was_plugin_active ) {
						$this->activate_plugin( $plugin_slug );
					}

					// Custom hooks after plugin is activated
					$this->plugins_after_install_ajax_hooks( 'update', $plugin_slug );

					// Button state
					$button_state    = 'activate';
					$button_text     = $this->tgmpa_ajax_button_strings[ $button_state ]['title'];
					$processing_text = $this->tgmpa_ajax_button_strings[ $button_state ]['processing'];
					$button_url      = $this->get_install_plugin_ajax_link( $plugin_slug, $button_state );

					if ( $was_plugin_active ) {
						$button_state    = 'active';
						$button_text     = $this->tgmpa_ajax_button_strings[ $button_state ]['title'];
						$button_url      = '';
						$processing_text = '';
					}

					// Response
					$response = [
						'success'         => true,
						'action'          => 'update-plugin',
						'new_version'     => $this->get_plugin_version( $plugin_slug ),
						'plugin_updated'  => true,
						'button_state'    => $button_state,
						'button_text'     => $button_text,
						'button_url'      => $button_url,
						'processing_text' => $processing_text,
					];

					// Output response
					echo sprintf( '<script type="text/template" class="kalium-tgmpa-ajax-response">%s</script>', wp_json_encode( $response ) );
				} );
			} // Plugin Activate hook
            elseif ( $is_activate_plugin ) {

				// Avoid any redirect after plugin is activated
				add_filter( 'wp_redirect', kalium_hook_return_value( '' ), 1000 );

				// Activate plugin
				$this->activate_plugin( $plugin_slug );

				// Custom hooks after plugin is activated
				$this->plugins_after_install_ajax_hooks( 'activate', $plugin_slug );

				// Button state
				$button_state = 'active';
				$button_text  = $this->tgmpa_ajax_button_strings[ $button_state ]['title'];

				// Response
				$response = [
					'success'        => true,
					'action'         => 'activate-plugin',
					'plugin_updated' => true,
					'button_state'   => $button_state,
					'button_text'    => $button_text,
				];

				// Output response
				echo sprintf( '<script type="text/template" class="kalium-tgmpa-ajax-response">%s</script>', wp_json_encode( $response ) );
			}

			// Pass request to TGMPA handler
			$tgmpa->install_plugins_page();
		}

		die();
	}

	/**
	 * Set source for bundled plugins when installing or updating them.
	 *
	 * @param bool        $return
	 * @param string      $package
	 * @param WP_Upgrader $upgrader
	 *
	 * @return string|WP_Error
	 */
	public function _set_source_for_bundled_plugins( $return, $package, $upgrader ) {
		global $pagenow;

		$skin        = $upgrader->skin;
		$type        = isset( $skin->type ) ? $skin->type : '';
		$plugin_slug = isset( $skin, $skin->options, $skin->options['extra'], $skin->options['extra']['slug'] ) ? $skin->options['extra']['slug'] : '';

		$theme_register_message = sprintf( 'Download failed. Theme must be registered in order to install or update premium bundled plugins. <p>Go to <a href="%1$s" class="kalium-theme-registration-link">Laborator &raquo; Registration</a> to register your theme.</p>', esc_url( Kalium_About::get_tab_link( 'theme-registration' ) ) );

		// Make sure it is a plugin
		if ( $skin instanceof Plugin_Upgrader_Skin ) {
			$plugin_slug = dirname( $skin->plugin );

			if ( isset( $this->theme_plugins[ $plugin_slug ] ) ) {
				$plugin = $this->theme_plugins[ $plugin_slug ];

				if ( ! empty( $plugin['source'] ) ) {
					$source = $plugin['source'];
					$skin->feedback( 'downloading_package', $source );
					$download_file = download_url( $source );

					if ( is_wp_error( $download_file ) ) {

						// Check if theme is not activated
						if ( false === kalium()->theme_license->is_theme_registered() ) {
							return new WP_Error( 'download_failed_theme_not_registered', $theme_register_message );
						}

						return new WP_Error( 'download_failed', $skin->upgrader->strings['download_failed'], $download_file->get_error_message() );
					}

					return $download_file;
				}
			}
		} // Installing plugin
		else if ( 'web' === $type && ( $skin instanceof Plugin_Installer_Skin || $skin instanceof TGMPA_Bulk_Installer_Skin ) && $this->is_premium_plugin( $plugin_slug ) && false === kalium()->theme_license->is_theme_registered() ) {
			return new WP_Error( 'download_failed_theme_not_registered', $theme_register_message );
		} // Update page
		else if ( 'update.php' === $pagenow ) {

			if ( $skin instanceof Bulk_Plugin_Upgrader_Skin && ! empty( $skin->plugin_info ) ) {
				$plugin_info = $skin->plugin_info;

				// Current updating plugin meta
				$name = $plugin_info['Name'];

				// Check for matching bundled plugin
				foreach ( $this->get_plugins_list() as $plugin ) {

					// Matched bundled plugin
					if ( ! empty( $plugin['source'] ) && $name == $plugin['native_name'] ) {
						$version = $this->get_latest_plugin_version( $plugin['slug'] );

						// Only if its the same version or older
						if ( version_compare( $version, $plugin['version'], '<=' ) ) {
							$source = $plugin['source'];
							$skin->feedback( 'downloading_package', $source );

							$download_file = download_url( $source );

							return $download_file;
						}
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Get list of plugins that are required or recommended for the theme.
	 *
	 * @return array
	 */
	public function get_plugins_list() {
		$plugins = [];

		// Advanced Custom Fields Pro
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'advanced-custom-fields-pro',
			'native_name' => 'Advanced Custom Fields PRO',
			'data'        => [
				'tags'       => [ 'premium-plugin', 'required' ],
				'author'     => 'Elliot Condon',
				'author_uri' => 'https://advancedcustomfields.com/',
				'icon_wp'    => 'advanced-custom-fields',
			],
		] );

		// Portfolio Post Type
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'portfolio-post-type',
			'native_name' => 'Portfolio Post Type',
			'data'        => [
				'tags'       => [ 'recommended' ],
				'author'     => 'Devin Price',
				'author_uri' => 'https://www.wptheming.com/',
				'icon_wp'    => true,
			],
		] );

		// WPBakery Page Builder
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'js_composer',
			'native_name' => 'WPBakery Page Builder',
			'data'        => [
				'tags'       => [ 'premium-plugin', 'required' ],
				'author'     => 'WPBakery',
				'author_uri' => 'https://wpbakery.com/',
			],
		] );

		// WooCommerce
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'woocommerce',
			'native_name' => 'WooCommerce',
			'data'        => [
				'author'     => 'Automattic',
				'author_uri' => 'https://woocommerce.com/',
				'icon_wp'    => true,
			],
		] );

		// Revolution Slider
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'revslider',
			'native_name' => 'Slider Revolution',
			'data'        => [
				'tags'       => [ 'premium-plugin' ],
				'author'     => 'ThemePunch',
				'author_uri' => 'https://themepunch.com/',
			],
		] );

		// Layer Slider
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'LayerSlider',
			'native_name' => 'LayerSlider WP',
			'data'        => [
				'tags'       => [ 'premium-plugin' ],
				'author'     => 'Kreatura Media',
				'author_uri' => 'https://kreaturamedia.com/',
			],
		] );

		// WooCommerce Product Filter
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'prdctfltr',
			'native_name' => 'Product Filter for WooCommerce',
			'data'        => [
				'tags'       => [ 'premium-plugin' ],
				'author'     => '7VX LLC',
				'author_uri' => 'https://xforwoocommerce.com/',
			],
		] );

		// WooCommerce Product Size Guide
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'ct-size-guide',
			'native_name' => 'WooCommerce Product Size Guide',
			'data'        => [
				'tags'       => [ 'premium-plugin' ],
				'author'     => 'createIT',
				'author_uri' => 'https://createit.pl/',
			],
		] );

		// Classic Editor
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'classic-editor',
			'native_name' => 'Classic Editor',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'WordPress Contributors',
				'author_uri' => 'https://wordpress.org/plugins/classic-editor/',
				'icon_wp'    => true,
			],
		] );

		// Yoast SEO
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'wordpress-seo',
			'native_name' => 'Yoast SEO',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'Team Yoast',
				'author_uri' => 'https://yoa.st/1uk',
				'icon_wp'    => true,
			],
		] );

		// Loco Translate
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'loco-translate',
			'native_name' => 'Loco Translate',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'Tim Whitlock',
				'author_uri' => 'https://localise.biz/wordpress/plugin',
				'icon_wp'    => true,
			],
		] );

		// Ninja Forms
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'ninja-forms',
			'native_name' => 'Ninja Forms',
			'data'        => [
				'tags'       => [ 'optional' ],
				'icon_wp'    => true,
				'author'     => 'Saturday Drive',
				'author_uri' => 'http://ninjaforms.com/',
			],
		] );

		// WP Mail SMTP
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'wp-mail-smtp',
			'native_name' => 'WP Mail SMTP',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'WPForms',
				'author_uri' => 'https://wpforms.com/',
				'icon_wp'    => true,
			],
		] );

		// WP Mail SMTP
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'breadcrumb-navxt',
			'native_name' => 'Breadcrumb NavXT',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'John Havlik',
				'author_uri' => 'http://mtekk.us/',
				'icon_wp'    => true,
			],
		] );

		// HubSpot
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'leadin',
			'native_name' => 'HubSpot - CRM and more...',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'HubSpot',
				'author_uri' => 'https://www.hubspot.com/',
				'icon_wp'    => true,
			],
		] );

		// WPForms Lite
		$plugins[] = $this->prepare_plugin_entry( [
			'slug'        => 'wpforms-lite',
			'native_name' => 'Contact Form by WPForms',
			'data'        => [
				'tags'       => [ 'optional' ],
				'author'     => 'WPForms',
				'author_uri' => 'https://wpforms.com/',
				'icon_wp'    => true,
			],
		] );

		return $plugins;
	}

	/**
	 * List theme plugins to install.
	 *
	 * @return void
	 */
	public function list_theme_plugins() {

		?>
        <ul class="about-kalium__plugins">

			<?php
			foreach ( $this->get_plugins_list() as $plugin ) :
				$plugin_slug = $plugin['slug'];
				$plugin_name = $plugin['name'];
				$plugin_data = $plugin['data'];
				$plugin_tags = $plugin_data['tags'];

				// Plugin filters
				$is_required       = in_array( 'required', $plugin_tags );
				$is_recommended    = in_array( 'recommended', $plugin_tags );
				$is_optional       = in_array( 'optional', $plugin_tags );
				$is_premium_plugin = in_array( 'premium-plugin', $plugin_tags );

				// Plugin icon
				$plugin_icon = kalium()->assets_url( sprintf( 'admin/images/plugins/plugin-%1$s.png', $plugin_slug ) );

				if ( $icon_wp = kalium_get_array_key( $plugin_data, 'icon_wp', false ) ) {
					$icon_slug   = is_bool( $icon_wp ) ? $plugin_slug : $icon_wp;
					$plugin_icon = sprintf( 'https://ps.w.org/%1$s/assets/icon-256x256.png', esc_attr( $icon_slug ) );
				}

				// Plugin author
				$plugin_author = sprintf( 'By <a href="%2$s" target="_blank" rel="noreferrer noopener">%1$s</a>', esc_html( $plugin_data['author'] ), esc_url( $plugin_data['author_uri'] ) );

				// Filter data
				$filter_data = [
					'required'    => $is_required,
					'recommended' => $is_recommended,
					'optional'    => $is_optional,
					'premium'     => $is_premium_plugin,
				];

				// Classes
				$classes = [];

				// Hide optional plugins by default
				if ( $is_optional ) {
					$classes[] = 'filter-out';
				}

				?>
                <li <?php kalium_class_attr( $classes ); ?> data-filter="<?php echo esc_attr( wp_json_encode( $filter_data ) ); ?>">

                    <div class="about-kalium__plugin-item">

						<?php if ( $new_version = $this->get_latest_plugin_version( $plugin_slug ) ) : ?>
                            <div class="about-kalium__plugin-item-update-notice">
                                <i class="dashicons dashicons-update"></i> New version
                                available: <?php echo esc_html( $new_version ); ?>
                            </div>
						<?php endif; ?>

                        <div class="about-kalium__plugin-item-heading">

                            <div class="about-kalium__plugin-item-thumbnail">
                                <img src="<?php echo esc_url( $plugin_icon ); ?>" width="48" height="48"/>
                            </div>

                            <div class="about-kalium__plugin-item-badge">

								<?php if ( $is_premium_plugin ) : ?>
                                    <span class="about-kalium__plugin-item-badge-premium-plugin" title="Premium Plugin">Premium</span>
								<?php endif; ?>

								<?php if ( $is_required ) : ?>
                                    <span class="about-kalium__plugin-item-badge-required-plugin" title="Required Plugin">Required</span>
								<?php endif; ?>

                            </div>

                        </div>

                        <h3 class="about-kalium__plugin-item-name">
							<?php echo esc_html( $plugin_name ); ?>
                            <small>
                                <span class="version"><?php echo esc_html( $this->get_plugin_version( $plugin_slug ) ); ?></span>
                                |
                                <span class="author"><?php echo $plugin_author; ?></span>
                            </small>
                        </h3>

						<?php
						// Plugin install, activate or update link
						$this->install_plugin_link( $plugin_slug );
						?>
                    </div>
                </li>
			<?php
			endforeach;
			?>
        </ul>
		<?php
	}

	/**
	 * Custom hooks after plugin is installed, activated or updated via TGMPA on AJAX.
	 *
	 * @param string $action
	 * @param string $plugin_slug
	 *
	 * @return void
	 */
	private function plugins_after_install_ajax_hooks( $action, $plugin_slug ) {

		// Disable WPBakery Page Builder redirect to avoid unexpected AJAX results
		if ( 'js_composer' === $plugin_slug ) {
			delete_transient( '_vc_page_welcome_redirect' );
		} // Disable WooCommerce redirect to avoid unexpected AJAX results
		else if ( 'woocommerce' === $plugin_slug ) {
			delete_transient( '_wc_activation_redirect' );
		}
	}

	/**
	 * Plugin install link.
	 *
	 * @param string $plugin_slug
	 * @param bool   $echo
	 *
	 * @return void|string
	 */
	private function install_plugin_link( $plugin_slug, $echo = true ) {

		/** @global TGM_Plugin_Activation $tgmpa */
		global $tgmpa;

		$is_plugin_installed = $tgmpa->is_plugin_installed( $plugin_slug );
		$is_plugin_active    = $tgmpa->is_plugin_active( $plugin_slug );
		$plugin_has_update   = ! empty( $this->get_latest_plugin_version( $plugin_slug ) );

		// Button classes
		$button_classes = [
			'button',
			'button-tgmpa-ajax',
		];

		// Default button action
		$action_type = 'active';

		// Update plugin
		if ( $plugin_has_update ) {
			$action_type = 'update';
		} // Install plugin
        elseif ( ! $is_plugin_installed ) {
			$action_type = 'install';
		} // Activate plugin
        elseif ( ! $is_plugin_active ) {
			$action_type = 'activate';
		}

		// Button action link
		$action_link = $this->get_install_plugin_ajax_link( $plugin_slug, $action_type );

		// Action type button class
		$button_classes[] = "button--state-{$action_type}";

		// Install and activate button classes
		if ( in_array( $action_type, [ 'install', 'activate' ] ) ) {
			$button_classes[] = 'button-primary';
		}

		// Display button
		$button = sprintf(
			'<a href="%1$s" data-title-processing="%3$s" class="%4$s">%2$s</a>',
			$action_link,
			$this->tgmpa_ajax_button_strings[ $action_type ]['title'],
			$this->tgmpa_ajax_button_strings[ $action_type ]['processing'],
			kalium()->helpers->list_classes( $button_classes )
		);

		// Echo button
		if ( $echo ) {
			echo $button;
		} else {
			return $button;
		}
	}

	/**
	 * Get plugin install, update or activate link for TGMPA.
	 *
	 * @param string $plugin_slug
	 * @param string $action_type
	 *
	 * @return string|null
	 */
	private function get_install_plugin_ajax_link( $plugin_slug, $action_type ) {

		// If the action type is not registered
		if ( ! in_array( $action_type, [ 'install', 'update', 'activate' ] ) ) {
			return null;
		}

		return wp_nonce_url(
			sprintf(
				admin_url( 'admin-ajax.php?action=kalium-plugins-tgmpa-install&plugin=%1$s&tgmpa-%2$s=%2$s-plugin' ),
				$plugin_slug,
				$action_type
			),
			'tgmpa-' . $action_type,
			'tgmpa-nonce'
		);
	}

	/**
	 * Initialize theme plugins information.
	 *
	 * @return void
	 */
	private function init_theme_plugins_data() {

		// Load plugins from transient
		$plugins_data = get_site_transient( 'kalium_theme_plugins_data' );

		// Only for allowed users
		if ( current_user_can( 'update_plugins' ) ) {

			// Force fetch plugins from Laborator API
			if ( kalium()->request->has( 'force-check' ) ) {
				delete_site_transient( 'kalium_theme_plugins_data' );
			}

			// Fetch plugin data when user is on Laborator > Plugins page
			if ( 'plugins' === kalium()->request->query( 'tab' ) && 'kalium' === kalium()->request->query( 'page' ) && false === get_site_transient( 'kalium_theme_plugins_refreshed' ) ) {
				$plugins_data = false;
				set_site_transient( 'kalium_theme_plugins_refreshed', true, HOUR_IN_SECONDS * 3 );
			} // Bundled plugins page
            elseif ( 'kalium-install-plugins' === kalium()->request->query( 'page' ) ) {
				$plugins_data = false;
			}
		}

		// Fetch plugin data
		if ( false === $plugins_data ) {

			// Get latest theme version
			$response = wp_remote_post( kalium()->theme_license->get_api_server_url(), [
				'body' => [
					'plugin_data'     => 'kalium',
					'current_version' => kalium()->get_version(),
					'license_key'     => kalium()->theme_license->get_license_key(),
				],
			] );

			// Plugins data
			$plugins_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Check for plugin updates every day
			set_site_transient( 'kalium_theme_plugins_data', $plugins_data, DAY_IN_SECONDS );
		}

		// Initialize loaded plugins in plugins var
		if ( is_array( $plugins_data ) ) {
			foreach ( $plugins_data as $plugin ) {
				$this->theme_plugins[ $plugin->slug ] = (array) $plugin;
			}
		}
	}

	/**
	 * Show required plugins notices when they are not active.
	 *
	 * @return void
	 */
	private function show_required_plugins_notices() {
		$dismiss_var      = 'dismiss_required_plugins_notice';
		$required_plugins = [];

		// Skip on Kalium about and install plugins page
		if ( in_array( kalium()->request->query( 'page' ), [
				'kalium',
				'kalium-install-plugins'
			] ) || get_transient( $dismiss_var ) ) {
			return;
		}

		// Dismiss notice by user
		if ( kalium()->request->has( $dismiss_var ) && current_user_can( 'install_plugins' ) ) {
			set_transient( $dismiss_var, true, MONTH_IN_SECONDS );
			wp_redirect( remove_query_arg( $dismiss_var ) );
			die();
		}

		// Get required plugins
		foreach ( $this->get_plugins_list() as $plugin ) {
			$is_required = in_array( 'required', kalium_get_array_key( $plugin['data'], 'tags', [] ) );

			if ( $is_required && ! kalium()->is->plugin_active( kalium()->helpers->get_plugin_basename( $plugin['slug'] ) ) ) {
				$required_plugins[] = $plugin['name'];
			}
		}

		if ( ! empty( $required_plugins ) ) {
			$message = sprintf(
				'<p><strong>This theme requires the following %1$s: %2$s.</strong></p>
                 <p><strong><a href="%3$s">Begin installing %1$s</a> | <a href="%4$s">Dismiss this notice</a></strong></p>',
				kalium_conditional( 1 === count( $required_plugins ), 'plugin', 'plugins' ),
				implode( ', ', $required_plugins ),
				Kalium_About::get_tab_link( 'plugins' ),
				add_query_arg( [ $dismiss_var => 1 ] )
			);

			// Add admin notice
			kalium()->helpers->add_admin_notice( $message, 'warning' );
		}
	}

	/**
	 * Prepare TGMPA plugin entry.
	 *
	 * @param array $plugin_data {
	 *
	 * @type string $slug
	 * @type string $native_name
	 * @type string $name
	 * @type bool   $required
	 * @type array  $data
	 * }
	 *
	 * @return array
	 */
	private function prepare_plugin_entry( $plugin_data ) {

		// Plugin entry
		$plugin = wp_parse_args( $plugin_data, [
			'slug'        => '',
			'native_name' => '',
			'name'        => '',
			'required'    => false,
			'data'        => [],
		] );

		// Plugin data
		$plugin['data'] = wp_parse_args( $plugin['data'], [
			'tags'       => [],
			'author'     => '',
			'author_uri' => '',
		] );

		// Insert native name as plugin name
		if ( empty( $plugin['name'] ) ) {
			$plugin['name'] = $plugin['native_name'];
		}

		// Retrieve plugin data
		if ( isset( $this->theme_plugins[ $plugin['slug'] ] ) ) {
			$plugin = array_merge( $plugin, $this->theme_plugins[ $plugin['slug'] ] );
		}

		return $plugin;
	}

	/**
	 * Get current version of plugin.
	 *
	 * @param string $plugin_slug
	 *
	 * @return string
	 */
	private function get_plugin_version( $plugin_slug ) {

		// Installed plugins
		static $plugins = [];

		// Version
		$plugin_version = null;

		// Plugin basename
		$plugin_basename = kalium()->helpers->get_plugin_basename( $plugin_slug );

		// Initialize plugins
		if ( empty( $plugins ) ) {
			$plugins = get_plugins();
		}

		// Get version from TGMPA entries
		if ( isset( $this->theme_plugins[ $plugin_slug ] ) ) {
			$plugin_version = kalium_get_array_key( $this->theme_plugins[ $plugin_slug ], 'version', null );
		}

		// Get current installed version
		if ( $plugin_basename && isset( $plugins[ $plugin_basename ] ) ) {
			$plugin_version = kalium_get_array_key( $plugins[ $plugin_basename ], 'Version', null );
		}

		// Retrieve version from WordPress.org API
		if ( is_null( $plugin_version ) ) {

			// Retrieve version from WordPress.org API
			$remote_plugin_version = $this->get_plugin_version_remote( $plugin_slug );

			if ( $remote_plugin_version ) {
				$plugin_version = $remote_plugin_version;
			}
		}

		return $plugin_version;
	}

	/**
	 * Get plugin version from WordPress API.
	 *
	 * @param string $plugin_slug
	 *
	 * @return string|null
	 */
	private function get_plugin_version_remote( $plugin_slug ) {
		$plugin_version = null;

		// Plugin version transient var
		$plugin_version_var = "kalium_plugin_version_var_{$plugin_slug}";

		// Return transient value
		if ( $transient_value = get_transient( $plugin_version_var ) ) {
			$plugin_version = $transient_value;
		} else {

			// Import plugins_api function
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			// Request latest version data for the plugin
			$response = plugins_api( 'plugin_information', [
				'slug'   => $plugin_slug,
				'fields' => [
					'sections' => false,
				],
			] );

			// When the request is successful
			if ( ! is_wp_error( $response ) ) {

				// Assign latest version of requested plugin
				$plugin_version = $response->version;
				set_transient( $plugin_version_var, $plugin_version, DAY_IN_SECONDS );
			}
		}

		return $plugin_version;
	}

	/**
	 * Get latest version for a plugin (if there is any update of plugin).
	 *
	 * @param string $plugin_slug
	 *
	 * @return string|null
	 */
	private function get_latest_plugin_version( $plugin_slug ) {
		$plugin_updates = get_plugin_updates();
		$latest_version = null;

		// Get latest version from plugin updates array
		foreach ( $plugin_updates as $plugin_file => $plugin ) {
			$slug = dirname( $plugin_file );

			if ( $plugin_slug === $slug && isset( $plugin->update ) ) {
				$latest_version = $plugin->update->new_version;
			}
		}

		// Check if newer version is available in TGMPA
		if ( isset( $this->theme_plugins[ $plugin_slug ], $this->theme_plugins[ $plugin_slug ]['version'] ) ) {
			$current_plugin_version = $this->get_plugin_version( $plugin_slug );
			$tgmpa_plugin_version   = $this->theme_plugins[ $plugin_slug ]['version'];

			if ( version_compare( $tgmpa_plugin_version, $current_plugin_version, '>' ) && version_compare( $tgmpa_plugin_version, $latest_version, '>' ) ) {
				$latest_version = $tgmpa_plugin_version;
			}
		}

		return $latest_version;
	}

	/**
	 * Check if plugin is premium plugin.
	 *
	 * @param string $plugin_slug
	 *
	 * @return bool
	 */
	private function is_premium_plugin( $plugin_slug ) {
		foreach ( $this->get_plugins_list() as $plugin ) {
			if ( $plugin_slug === $plugin['slug'] ) {
				return in_array( 'premium-plugin', $plugin['data']['tags'] );
			}
		}

		return false;
	}

	/**
	 * Activate plugin.
	 *
	 * @param string $plugin_slug
	 *
	 * @return bool
	 */
	private function activate_plugin( $plugin_slug ) {

		// Plugin basename
		if ( $plugin_basename = kalium()->helpers->get_plugin_basename( $plugin_slug ) ) {

			// Activate plugin with core WordPress function
			activate_plugin( $plugin_basename );

			return true;
		}

		return false;
	}
}
