<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium main class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Kalium Base class
require_once __DIR__ . '/core/kalium-base.php';

/**
 * Main Kalium class that setups the theme.
 *
 * @property Kalium_Request         request
 * @property Kalium_Helpers         helpers
 * @property Kalium_Is              is
 * @property Kalium_Filesystem      filesystem
 * @property Kalium_Enqueue         enqueue
 * @property Kalium_Theme_License   theme_license
 * @property Kalium_Theme_Plugins   theme_plugins
 * @property Kalium_Images          images
 * @property Kalium_Media           media
 * @property Kalium_Structured_Data structured_data
 * @property Kalium_Elementor       elementor
 * @property Kalium_WooCommerce     woocommerce
 * @property Kalium_ACF             acf
 */
final class Kalium extends Kalium_Base {

	/**
	 * Kalium instance as singleton class.
	 *
	 * @var Kalium $instance
	 */
	private static $instance;

	/**
	 * Loaded class instances.
	 *
	 * @var array $class_instances
	 */
	private $class_instances;

	/**
	 * Theme execution start time.
	 *
	 * @var int $start_time
	 */
	private $start_time;

	/**
	 * Create Instance of this class.
	 *
	 * @return Kalium
	 */
	public static function instance() {
		if ( ! self::$instance ) {

			// Initialize Kalium
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Theme Constructor executed only once per request.
	 *
	 * @return void
	 */
	public function __construct() {

		// This is singleton class
		if ( self::$instance ) {
			kalium_doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '2.0' );

			return;
		}

		// Save reference to the current instance
		self::$instance = $this;

		// Start time of Kalium execution
		$this->start_time = microtime( true );

		// Autoload classes
		$this->autoload_classes();

		// Load functions, template functions and theme hooks
		$this->load_theme_functions_templates_and_hooks();

		// Load legacy core files (soon to be removed)
		$this->load_legacy_core_files();

		// Load theme options
		require_once $this->locate_file( 'includes/libraries/smof/smof.php' );

		// After setup theme
		add_action( 'after_setup_theme', [ $this, '_after_setup_theme' ] );

		// After switch theme
		add_action( 'after_switch_theme', [ $this, '_after_switch_theme' ] );

		// Laborator menu
		add_action( 'admin_menu', [ $this, '_admin_menu' ], 1 );

		// Admin init
		add_action( 'admin_init', [ $this, '_admin_init' ] );

		// Admin enqueue scripts
		add_action( 'admin_enqueue_scripts', [ $this, '_admin_enqueue_scripts' ] );

		// Kalium init hook
		do_action( 'kalium_init' );
	}

	/**
	 * You cannot clone this class.
	 *
	 * @return void
	 */
	public function __clone() {
		kalium_doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '2.0' );
	}

	/**
	 * You cannot unserialize instance of this class.
	 *
	 * @return void
	 */
	public function __wakeup() {
		kalium_doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', '2.0' );
	}

	/**
	 * Getter used to load class instance.
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {

		// When class instance doesn't exists
		if ( false === isset( $this->class_instances[ $name ] ) ) {
			kalium_doing_it_wrong( "kalium()->{$name}", "Sub class instance <strong>{$name}</strong> doesn't exists.", '3.0' );

			return null;
		}

		return $this->class_instances[ $name ];
	}

	/**
	 * After theme setup.
	 *
	 * @return void
	 */
	public function _after_setup_theme() {

		// Theme supports
		add_theme_support( 'html5', [
			'comment-list',
			'comment-form',
			'search-form',
			'gallery',
			'caption',
			'style',
		] );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'featured-image' );
		add_theme_support( 'post-formats', [
			'video',
			'quote',
			'image',
			'link',
			'gallery',
			'audio',
		] );
		add_theme_support( 'title-tag' );

		// Theme text domain
		load_theme_textdomain( 'kalium', kalium()->locate_file( 'languages' ) );

		// Register menus
		register_nav_menus( [
			'main-menu'   => 'Main Menu',
			'mobile-menu' => 'Mobile Menu',
		] );

		// Content width
		$GLOBALS['content_width'] = apply_filters( 'kalium_content_width', 945 );
	}

	/**
	 * After activating Kalium.
	 *
	 * @return void
	 */
	public function _after_switch_theme() {
		wp_redirect( admin_url( 'admin.php?page=kalium&welcome' ) );
	}

	/**
	 * Setup admin menu.
	 *
	 * @return void
	 */
	public function _admin_menu() {

		// Laborator menu item
		add_menu_page( 'Laborator', 'Laborator', 'edit_theme_options', 'laborator_options', [
			$this,
			'_admin_page_theme_options',
		], '', 2 );
	}

	/**
	 * Code to execute on admin side.
	 *
	 * @return void
	 */
	public function _admin_init() {
		global $pagenow;

		// Theme options redirect
		if ( 'themes.php' == $pagenow && 'theme-options' == $this->request->query( 'page' ) ) {
			wp_redirect( admin_url( "admin.php?page=laborator_options" ) );
			die();
		}
	}

	/**
	 * Enqueue scripts for back-end.
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {

		// Old admin styles and scripts
		kalium_enqueue( 'admin-old-css' );

		// Admin styles and scripts
		kalium_enqueue( 'theme-admin' );

		// Theme options
		if ( 'laborator_options' === kalium()->request->query( 'page' ) ) {
			kalium_enqueue( 'theme-options-js' );
		}

		// Assets directory URL
		kalium_define_js_variable( 'kaliumAssetsDir', $this->assets_url() );
	}

	/**
	 * Theme options page.
	 *
	 * @return void
	 */
	public function _admin_page_theme_options() {
		$this->require_file( 'includes/libraries/smof/front-end/options.php' );
	}

	/**
	 * Get start time of execution.
	 *
	 * @return int
	 */
	public function get_start_time() {
		return $this->start_time;
	}

	/**
	 * Check if given plugins array are all active.
	 *
	 * @param array $plugins
	 *
	 * @return bool
	 */
	private function plugins_are_active( $plugins = [] ) {

		// When no plugins are specified, load plugin files
		if ( empty( $plugins ) ) {
			return true;
		}

		return $this->is->plugins_are_active( $plugins );
	}

	/**
	 * Autoload Kalium classes.
	 *
	 * @return void
	 */
	private function autoload_classes() {

		// Autoload classes list
		$autoload = require_once $this->locate_file( 'includes/classes/load-classes.php' );

		// Class instances
		$this->class_instances = [];

		// Autoload classes
		foreach ( $autoload as $class_name => $args ) {

			// Class args
			$args = wp_parse_args( $args, [
				'path'          => '',
				'instance_name' => '',
				'instantiate'   => true,
			] );

			// If class already exists do not include
			if ( ! class_exists( $class_name ) ) {
				$this->require_file( sprintf( 'includes/classes/%s.php', $args['path'] ) );
			}

			// Instance name
			$instance_name = $args['instance_name'];

			// Instantiate class
			if ( $args['instantiate'] ) {

				// Instantiate with Reflection class
				if ( class_exists( 'ReflectionClass' ) ) {

					try {
						// Class reflection
						$reflection = new \ReflectionClass( $class_name );

						// Load class
						if ( $this->plugins_are_active( $reflection->hasProperty( 'plugins' ) ? $reflection->getProperty( 'plugins' )->getValue() : [] ) ) {

							// Instantiate class
							if ( $reflection->getConstructor() ) {
								$instance = $reflection->newInstanceArgs( [ $this ] );
							} else {
								$instance = $reflection->newInstanceWithoutConstructor();
							}

							// Current admin page
							$instance->admin_page = is_admin() && isset( $_GET['page'] ) ? $_GET['page'] : null;

							// Register to class instances
							if ( $instance_name ) {
								$this->class_instances[ $instance_name ] = $instance;
							}
						}
					} catch ( Exception $e ) {
						wp_die( $e->getMessage() );
					}
				} else {

					// Plugin specific compatibility
					$compatible_plugins = [];

					if ( property_exists( $class_name, 'plugins' ) ) {
						$vars               = get_class_vars( $class_name );
						$compatible_plugins = is_array( $vars['plugins'] ) ? $vars['plugins'] : [ $vars['plugins'] ];
					}

					// Register instances (if set)
					if ( $this->plugins_are_active( $compatible_plugins ) ) {

						// Instantiate class
						$instance = new $class_name( $this );

						// Current admin page
						$instance->admin_page = is_admin() && isset( $_GET['page'] ) ? $_GET['page'] : null;

						// Register to class instances
						if ( $instance_name ) {
							$this->class_instances[ $instance_name ] = $instance;
						}
					}
				}
			}
		}
	}

	/**
	 * Load theme functions, template functions and hooks.
	 *
	 * @return void
	 */
	private function load_theme_functions_templates_and_hooks() {

		// WooCommerce plugin
		$woocommerce_plugin = kalium()->helpers->get_plugin_basename( 'woocommerce' );

		// Function files
		$functions = [
			'core'             => [],
			'header'           => [],
			'blog'             => [],
			'woocommerce'      => [ $woocommerce_plugin ],
			'util'             => [],
			'alias'            => [],
			'admin'            => [],
			'other'            => [],
			'core-hook'        => [],
			'header-hook'      => [],
			'blog-hook'        => [],
			'woocommerce-hook' => [ $woocommerce_plugin ],
			'other-hook'       => [],
		];

		foreach ( $functions as $file_name => $required_plugins ) {
			$file_path = $this->locate_file( sprintf( 'includes/functions/%s-functions.php', $file_name ) );

			if ( empty( $required_plugins ) || $this->plugins_are_active( $required_plugins ) ) {
				require_once $file_path;
			}
		}

		// Template functions files
		$template_functions = [
			'core'        => [],
			'header'      => [],
			'blog'        => [],
			'other'       => [],
			'woocommerce' => [ $woocommerce_plugin ],
		];

		foreach ( $template_functions as $file_name => $required_plugins ) {
			$file_path = $this->locate_file( sprintf( 'includes/functions/template/%s-template-functions.php', $file_name ) );

			if ( empty( $required_plugins ) || $this->plugins_are_active( $required_plugins ) ) {
				require_once $file_path;
			}
		}

		// Hooks files
		$hooks = [
			'core'        => [],
			'header'      => [],
			'blog'        => [],
			'other'       => [],
			'woocommerce' => [ $woocommerce_plugin ],
		];

		foreach ( $hooks as $file_name => $required_plugins ) {
			$file_path = $this->locate_file( sprintf( 'includes/hooks/%s-template-hooks.php', $file_name ) );

			if ( empty( $required_plugins ) || $this->plugins_are_active( $required_plugins ) ) {
				require_once $file_path;
			}
		}
	}

	/**
	 * Load legacy core files. Soon to be removed.
	 *
	 *
	 * @return void
	 */
	private function load_legacy_core_files() {

		// Legacy core files
		$legacy_core_files = [
			'includes/laborator_functions.php',
			'includes/laborator_actions.php',
			'includes/laborator_filters.php',
			'includes/laborator_portfolio.php',
			'includes/laborator_thumbnails.php',
			'includes/laborator_vc.php',
		];

		foreach ( $legacy_core_files as $file ) {
			$this->require_file( $file );
		}
	}
}

/**
 * Kalium instance reference.
 *
 * @return Kalium
 */
function kalium() {
	return Kalium::instance();
}

// Instantiate Kalium
Kalium::instance();
