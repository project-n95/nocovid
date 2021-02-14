<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium enqueue class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Enqueue {

	/**
	 * Frontend scripts and styles.
	 *
	 * @var array
	 */
	public $frontend = [

		// Theme styles
		'bootstrap-css'                 => 'css/bootstrap.min.css',
		'main-css'                      => 'css/main.min.css',
		'style-css'                     => '/style.css',

		// Theme style broken into parts
		'theme-base-css'                => 'css/base.min.css',
		'theme-portfolio-css'           => 'css/portfolio.min.css',
		'theme-woocommerce-css'         => 'css/woocommerce.min.css',
		'theme-other-css'               => 'css/other.min.css',

		// New theme styles broken into parts
		'theme-woocommerce-new-css'     => 'css/new/woocommerce.min.css',

		// Icon sets
		'fontawesome-css'               => 'vendors/font-awesome/css/all.min.css',

		// Theme scripts
		'main-js'                       => 'js/main.min.js',
		'sticky-header-js'              => 'js/sticky-header.min.js',

		// GSAP
		'gsap-js'                       => 'vendors/gsap/gsap.min.js',
		'gsap-scrollto-js'              => 'vendors/gsap/ScrollToPlugin.min.js',
		'gsap-splittext-js'             => 'vendors/gsap/SplitText.min.js',

		// ScrollMagic
		'scrollmagic-js'                => 'vendors/scrollmagic/ScrollMagic.min.js',
		'scrollmagic-gsap-js'           => 'vendors/scrollmagic/plugins/animation.gsap.min.js',
		'scrollmagic-debug-js'          => 'vendors/scrollmagic/plugins/debug.addIndicators.min.js',

		// VideoJS
		'videojs-css'                   => 'vendors/video-js/video-js.min.css',
		'videojs-js'                    => 'vendors/video-js/video.min.js',

		// VideoJS YouTube
		'videojs-youtube-js'            => 'vendors/video-js-youtube/video-js-youtube.js',

		// VideoJS Share
		'videojs-share-css'             => 'vendors/video-js-share/videojs-share.css',
		'videojs-share-js'              => 'vendors/video-js-share/videojs-share.min.js',

		// Vimeo
		'vimeo-player-js'               => 'vendors/vimeo/player.min.js',

		// Isotope and Packery mode
		'metafizzy-isotope-js'          => 'vendors/metafizzy/isotope.pkgd.min.js',
		'metafizzy-packery-js'          => 'vendors/metafizzy/packery-mode.pkgd.min.js',

		// Nivo lightbox
		'nivo-lightbox-css'             => 'vendors/nivo-lightbox/nivo-lightbox.min.css',
		'nivo-lightbox-default-css'     => 'vendors/nivo-lightbox/themes/default/default.css',
		'nivo-lightbox-js'              => 'vendors/nivo-lightbox/nivo-lightbox.min.js',

		// Slick carousel
		'slick-css'                     => 'vendors/slick/slick.css',
		'slick-js'                      => 'vendors/slick/slick.min.js',

		// Flickity carousel
		'flickity-css'                  => 'vendors/flickity/flickity.min.css',
		'flickity-js'                   => 'vendors/flickity/flickity.pkgd.min.js',

		// Flickity fade option
		'flickity-fade-css'             => 'vendors/flickity-fade/flickity-fade.css',
		'flickity-fade-js'              => 'vendors/flickity-fade/flickity-fade.js',

		// Fluid box
		'fluidbox-css'                  => 'vendors/fluidbox/css/fluidbox.min.css',
		'fluidbox-js'                   => 'vendors/fluidbox/jquery.fluidbox.min.js',

		// Light gallery
		'light-gallery-css'             => 'vendors/light-gallery/css/lightgallery.min.css',
		'light-gallery-transitions-css' => 'vendors/light-gallery/css/lg-transitions.min.css',
		'light-gallery-js'              => 'vendors/light-gallery/lightgallery-all.min.js',

		// jQuery throttle-debounce lib
		'jquery-throttle-debounce-js'   => 'vendors/jquery-libs/jquery.ba-throttle-debounce.js',
	];

	/**
	 * Admin scripts and styles.
	 *
	 * @var array
	 */
	public $admin = [

		// Admin scripts and styles
		'admin-css'             => 'admin/css/main.min.css',
		'admin-js'              => 'admin/js/main.min.js',

		// Admin CSS (deprecated)
		'admin-old-css'         => 'admin/css/main-old.min.css',

		// About
		'about-css'             => 'admin/css/about.min.css',
		'about-js'              => 'admin/js/about.min.js',

		// Theme registration
		'theme-registration-js' => 'admin/js/theme-registration.min.js',

		// Icons used in admin
		'font-flaticons-css'    => 'css/fonts/flaticons-custom/flaticon-admin.css',
		'font-lineaicons-css'   => 'css/fonts/linea-iconfont/linea-iconfont-admin.css',

		// Theme options
		'theme-options-js'      => 'admin/js/theme-options.min.js',

		// Loaders
		'loaders-css'           => 'admin/css/css-loaders.css',

		// Tooltipster
		'tooltipster-css'       => 'https://cdnjs.cloudflare.com/ajax/libs/tooltipster/3.3.0/css/tooltipster.min.css@3.3.0',
		'tooltipster-js'        => 'https://cdnjs.cloudflare.com/ajax/libs/tooltipster/3.3.0/js/jquery.tooltipster.min.js@3.3.0',
	];

	/**
	 * Styles and scripts will be added here after processed.
	 *
	 * @var Kalium_Enqueue_Item[]
	 */
	public $registered = [];

	/**
	 * Grouped enqueues.
	 *
	 * @var array
	 */
	public $grouped_enqueues = [];

	/**
	 * Waiting list for enqueue handles.
	 *
	 * @var array
	 */
	public $waiting_list = [];

	/**
	 * Build id used for cache bust.
	 */
	private $build_num = 1;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Enqueue item class
		require_once 'kalium-enqueue-item.php';

		// Hooks
		add_action( 'init', [ $this, '_register_frontend_enqueues' ] );
		add_action( 'admin_enqueue_scripts', [ $this, '_register_admin_enqueues' ] );

		// Enqueue from waiting list
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_waiting_list_items' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_waiting_list_items' ] );

		// Register default enqueue groups
		$this->register_default_enqueue_groups();

		/**
		 * Hook: kalium_enqueue_loaded.
		 */
		do_action( 'kalium_enqueue_loaded' );
	}

	/**
	 * Get enqueue item instances by also checking enqueue groups.
	 *
	 * @param string $handle
	 *
	 * @return Kalium_Enqueue_Item[]
	 */
	public function get_enqueue_items( $handle ) {
		$raw_handles = $handles = [];

		// If $handle is enqueue group handle
		if ( isset( $this->grouped_enqueues[ $handle ] ) ) {
			foreach ( $this->grouped_enqueues[ $handle ] as $single_handle ) {
				$raw_handles[] = $single_handle;
			}
		} else {
			$raw_handles[] = $handle;
		}

		// Register valid handles
		foreach ( $raw_handles as $single_handle ) {
			if ( isset( $this->registered[ $single_handle ] ) ) {
				$handles[] = $this->registered[ $single_handle ];
			}
		}

		return $handles;
	}

	/**
	 * Enqueue item by handle (script or style).
	 *
	 * @param string       $handle
	 * @param string|array $src
	 *
	 * @return void
	 */
	public function enqueue( $handle, $src = '' ) {

		// Register asset if $src is provided
		if ( ! empty( $src ) && ! isset( $this->registered[ $handle ] ) ) {
			$this->registered[ $handle ] = new Kalium_Enqueue_Item( $handle, $src );
			$this->wp_register_scripts_and_styles();
		}

		// Enqueue
		if ( $this->doing_it_right() ) {
			$enqueue_items = $this->get_enqueue_items( $handle );

			foreach ( $enqueue_items as $enqueue_item ) {
				if ( $enqueue_item->is_style() ) {
					wp_enqueue_style( $enqueue_item->handle );
				} else {
					wp_enqueue_script( $enqueue_item->handle );
				}
			}
		} else {

			// Add enqueue handle to waiting list
			$this->waiting_list[] = [
				'type'   => 'enqueue',
				'handle' => $handle,
			];
		}
	}

	/**
	 * Dequeue item by handle (script or style).
	 *
	 * @param string $handle
	 *
	 * @return void
	 */
	public function dequeue( $handle ) {
		if ( $this->doing_it_right() ) {
			$enqueue_items = $this->get_enqueue_items( $handle );

			foreach ( $enqueue_items as $enqueue_item ) {
				if ( $enqueue_item->is_style() ) {
					wp_dequeue_style( $enqueue_item->handle );
				} else {
					wp_dequeue_script( $enqueue_item->handle );
				}
			}
		} else {

			// Add dequeue handle to waiting list
			$this->waiting_list[] = [
				'type'   => 'dequeue',
				'handle' => $handle,
			];
		}
	}

	/**
	 * Add inline script. Same implemenataion except the handle is translated to theme processed handle.
	 *
	 * @param string $handle
	 * @param string $data
	 * @param string $position
	 *
	 * @return void
	 */
	public function add_inline_script( $handle, $data, $position = 'after' ) {
		if ( $this->doing_it_right() ) {
			$enqueue_items = $this->get_enqueue_items( $handle );

			if ( ! empty( $enqueue_items ) ) {
				$enqueue_item = $enqueue_items[0];

				if ( $enqueue_item && wp_script_is( $enqueue_item->handle, 'registered' ) ) {
					wp_add_inline_script( $enqueue_item->handle, $data, $position );
				}
			}
		}

		// Add inline script handle to waiting list
		$this->waiting_list[] = [
			'type'     => 'inline-script',
			'handle'   => $handle,
			'data'     => $data,
			'position' => $position,
		];
	}

	/**
	 * Get prefixed handle.
	 *
	 * @param string $handle
	 * @param bool   $return_all
	 *
	 * @return string|array
	 */
	public function get_prefixed_handle( $handle, $return_all = false ) {
		$enqueue_items = $this->get_enqueue_items( $handle );
		$handles       = wp_list_pluck( $enqueue_items, 'handle' );

		if ( $return_all ) {
			return $handles;
		}

		return current( $handles );
	}

	/**
	 * Register enqueue group.
	 *
	 * @param string   $group_name
	 * @param string[] $handles
	 *
	 * @return void
	 */
	public function register_enqueue_group( $group_name, $handles = [] ) {
		$handles_group = [];
		$enqueue_type  = null;

		if ( is_array( $handles ) ) {
			foreach ( $handles as $handle ) {
				if ( isset( $this->frontend[ $handle ] ) || isset( $this->admin[ $handle ] ) ) {
					$handles_group[] = $handle;
				}
			}
		}

		if ( ! empty( $handles_group ) ) {
			$this->grouped_enqueues[ $group_name ] = $handles_group;
		}
	}

	/**
	 * Deregister enqueue group.
	 *
	 * @param string $group_name
	 *
	 * @return bool
	 */
	public function deregister_enqueue_group( $group_name ) {
		if ( isset( $this->grouped_enqueues[ $group_name ] ) ) {
			unset( $this->grouped_enqueues[ $group_name ] );

			return true;
		}

		return false;
	}

	/**
	 * Register frontend enqueues.
	 *
	 * @return void
	 */
	public function _register_frontend_enqueues() {
		foreach ( $this->frontend as $handle => $args ) {
			$this->registered[ $handle ] = new Kalium_Enqueue_Item( $handle, $args );
		}

		// Register scripts and styles to WordPress
		$this->wp_register_scripts_and_styles();

		// Enqueue from waiting list
		$this->enqueue_waiting_list_items();
	}

	/**
	 * Register admin enqueues.
	 *
	 * @return void
	 */
	public function _register_admin_enqueues() {
		foreach ( $this->admin as $handle => $args ) {
			$this->registered[ $handle ] = new Kalium_Enqueue_Item( $handle, $args );
		}

		// Register scripts and styles to WordPress
		$this->wp_register_scripts_and_styles();
	}

	/**
	 * Register scripts and styles to WordPress.
	 *
	 * @return void
	 */
	private function wp_register_scripts_and_styles() {
		$build_num = '.' . str_pad( $this->build_num, 3, '0', STR_PAD_LEFT );

		foreach ( $this->registered as $item ) {
			$args = $item->args;

			if ( $item->is_style() && ! wp_style_is( $item->handle, 'registered' ) ) {
				wp_register_style( $item->handle, $args['src'], $args['deps'], $args['version'] . $build_num );
			} else if ( ! wp_script_is( $item->handle, 'registered' ) ) {
				wp_register_script( $item->handle, $args['src'], $args['deps'], $args['version'] . $build_num, $args['in_footer'] );
			}
		}
	}

	/**
	 * Enqueue assets from waiting list.
	 *
	 * @return void
	 */
	public function enqueue_waiting_list_items() {
		if ( ! empty( $this->waiting_list ) ) {
			foreach ( $this->waiting_list as $entry ) {
				$handle = $entry['handle'];

				switch ( $entry['type'] ) {
					case 'enqueue':
						$this->enqueue( $handle );
						break;

					case 'dequeue':
						$this->dequeue( $handle );
						break;

					case 'inline-script':
						$this->add_inline_script( $handle, $entry['data'], $entry['position'] );
						break;
				}
			}
		}

		// Clear waiting list
		$this->waiting_list = [];
	}

	/**
	 * Check if items can be enqueued to avoid WordPress warnings.
	 *
	 * @return bool
	 */
	private function doing_it_right() {
		return did_action( 'init' ) || did_action( 'admin_enqueue_scripts' ) || did_action( 'wp_enqueue_scripts' ) || did_action( 'login_enqueue_scripts' );
	}

	/**
	 * Register default enqueue groups.
	 *
	 * @return void
	 */
	private function register_default_enqueue_groups() {

		// Theme style
		$theme_style = [
			'bootstrap-css',
			'fontawesome-css',
		];

		if ( defined( 'KALIUM_CONCATENATE_STYLES' ) ) {
			$theme_style[] = 'main-css';
		} else {
			$theme_style[] = 'theme-base-css';

			// Portfolio style
			if ( kalium()->is->portfolio_plugin_active() ) {
				$theme_style[] = 'theme-portfolio-css';
			}

			// WooCommerce style
			if ( kalium()->is->woocommerce_active() ) {
				$theme_style[] = 'theme-woocommerce-css';
			}

			// Other style
			$theme_style[] = 'theme-other-css';
		}

		$this->register_enqueue_group( 'theme-style', $theme_style );

		// ScrollMagic library
		$this->register_enqueue_group( 'scrollmagic', [ 'scrollmagic-js', 'scrollmagic-gsap-js' ] );

		// Metafizzy library
		$this->register_enqueue_group( 'isotope', [ 'metafizzy-isotope-js', 'metafizzy-packery-js' ] );

		// GSAP library
		$this->register_enqueue_group( 'gsap', [ 'gsap-js', 'gsap-scrollto-js' ] );

		// VideoJS library
		$this->register_enqueue_group( 'videojs', [ 'videojs-css', 'videojs-js' ] );

		// VideoJS share library
		$this->register_enqueue_group( 'videojs-share', [ 'videojs-share-css', 'videojs-share-js' ] );

		// Slick library
		$this->register_enqueue_group( 'slick', [ 'slick-css', 'slick-js' ] );

		// Flickity library
		$this->register_enqueue_group( 'flickity', [ 'flickity-css', 'flickity-js' ] );

		// Flickity fade library
		$this->register_enqueue_group( 'flickity-fade', [ 'flickity-fade-css', 'flickity-fade-js' ] );

		// Fluidbox library
		$this->register_enqueue_group( 'fluidbox', [ 'jquery-throttle-debounce-js', 'fluidbox-css', 'fluidbox-js' ] );

		// Nivo library
		$this->register_enqueue_group( 'nivo', [
			'nivo-lightbox-css',
			'nivo-lightbox-default-css',
			'nivo-lightbox-js',
		] );

		// Light gallery library
		$this->register_enqueue_group( 'light-gallery', [
			'light-gallery-css',
			'light-gallery-transitions-css',
			'light-gallery-js',
		] );

		/**
		 * Admin script and style groups.
		 */

		// Admin JS and style
		$this->register_enqueue_group( 'theme-admin', [ 'admin-css', 'admin-js' ] );

		// About
		$this->register_enqueue_group( 'admin-about', [ 'about-css', 'about-js' ] );

		// Tooltipster
		$this->register_enqueue_group( 'tooltipster', [ 'tooltipster-css', 'tooltipster-js' ] );
	}
}
