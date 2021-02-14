<?php
/**
 * Kalium WordPress Theme
 *
 * TypoLab Fonts.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab {

	/**
	 * Instance of TypoLab.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Option var.
	 *
	 * @var string
	 */
	public static $settings_var = 'typolab_fonts';

	/**
	 * Registered/Supported Font Sources.
	 *
	 * @var array
	 */
	public static $font_sources = [
		'google'        => [
			'name'        => 'Google Fonts',
			'description' => "Google's free font directory is one of the most exciting developments in web typography in a very long time.\n\nGoogle Fonts catalog are published under licenses that allow you to use them on any website, whether it’s commercial or personal.\n\nChoose between <strong>1000+</strong> available fonts to use with your site."
		],
		'font-squirrel' => [
			'name'        => 'Font Squirrel',
			'description' => "Font Squirrel is a collection of free fonts for commercial use.\n\nApart from Google fonts, Font Squirrel requires to download and install fonts in order to use them. Installation process is automatic, just hit the <strong>Download</strong> button.\n\nChoose between <strong>1000+</strong> available fonts to use with your site."
		],
		'premium'       => [
			'name'        => 'Premium Fonts',
			'description' => "Premium fonts worth of <strong>$149</strong> (per site) are available for Laborator customers only.\n\nIt has the same installation procedures as Font Squirrel, you need to download and install fonts that you want to use in this site.\n\nTheme activation is required in order to install fonts from this source."
		],
		'typekit'       => [
			'name'        => 'Adobe Fonts (formerly TypeKit)',
			'description' => "Adobe Fonts is a subscription service for fonts which you can use on a website.\n\nInstead of licensing individual fonts, you can sign up for the plan that best suits your needs and get a library of fonts from which to choose.\n\nTo import Adobe Fonts fonts in your site, simply enter the <strong>Project ID</strong> and you are all set."
		],
		'uploaded-font' => [
			'name'        => 'Uploaded Font',
			'description' => "We have made it easier to upload web font formats such WOFF2, WOFF, TTF, EOT and SVG.\n\nFor better support you can upload all file formats, however WOFF2 is enough for modern browsers.\n\nThis method also complies with GDPR regulations by hosting the font on your website rather than fetching from external sources.",
		],
		'custom-font'   => [
			'name'        => 'Custom Font',
			'description' => "If you can't find the right font from above sources then Custom Fonts got covered you.\n\nTo import a custom font, simply enter the stylesheet URL that includes @font-face's and specify font variant names.\n\nThis font type is suitable for services that provide stylesheet URL only and not the web fonts individually."
		],
	];

	/**
	 * TypoLab Path.
	 *
	 * @var string
	 */
	public static $typolab_path;

	/**
	 * Fonts Path.
	 *
	 * @var string
	 */
	public static $fonts_path;

	/**
	 * Fonts URL.
	 *
	 * @var string
	 */
	public static $fonts_url;

	/**
	 * Assets URL to TypoLab.
	 *
	 * @var string
	 */
	public static $typolab_assets_url;

	/**
	 * Font Preview String.
	 *
	 * @var string
	 */
	public static $font_preview_str = 'Mist enveloped the ship three hours out from port.';

	/**
	 * Font Preview Size.
	 *
	 * @var int
	 */
	public static $font_preview_size = 16;

	/**
	 * TypoLab Execute on Frontend.
	 *
	 * @var bool
	 */
	public static $typolab_enabled = true;

	/**
	 * Default font import code placement.
	 *
	 * @var string
	 */
	public static $font_placement = 'head';

	/**
	 * Font combining.
	 *
	 * @var bool
	 */
	public static $font_combining = true;

	/**
	 * Responsive Sizes.
	 *
	 * @var array
	 */
	public static $viewport_breakpoints = [
		'general' => [ null, null ],
		'desktop' => [ 992, 1200 ],
		'tablet'  => [ 768, 992 ],
		'mobile'  => [ null, 768 ],
	];

	/**
	 * List of Not Installed Fonts
	 *
	 * @var array
	 */
	public static $missing_fonts = [];

	/**
	 * Create TypoLab instance (singleton).
	 *
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get settings.
	 *
	 * @param string $var
	 * @param string $default
	 *
	 * @return mixed
	 */
	public static function get_setting( $var = null, $default = '' ) {
		$typolab_settings = get_option( self::$settings_var, [] );

		// Get All Vars
		if ( is_null( $var ) ) {
			return $typolab_settings;
		}

		// Get Single Var
		if ( isset( $typolab_settings[ $var ] ) ) {
			return $typolab_settings[ $var ];
		}

		return $default;
	}

	/**
	 * Save Variable in Settings Array.
	 *
	 * @param string $var
	 * @param string $value
	 */
	public static function set_setting( $var, $value = '' ) {
		$settings = self::get_setting();

		$settings[ $var ] = $value;

		update_option( self::$settings_var, $settings );
	}

	/**
	 * Get registered fonts.
	 *
	 * @param bool $valid_fonts_only
	 * @param bool $published_fonts_only
	 *
	 * @return array
	 */
	public static function get_fonts( $valid_fonts_only = false, $published_fonts_only = false ) {
		$fonts = self::get_setting( 'registered_fonts', [] );

		// Sort by source
		uasort( $fonts, function ( $a, $b ) {
			return strcmp( $a['source'], $b['source'] );
		} );

		// Sort by name
		if ( kalium()->request->query( 'sort-by-name' ) ) {
			uasort( $fonts, function ( $a, $b ) {
				if ( 'DESC' === strtoupper( kalium()->request->query( 'sort-by-name' ) ) ) {
					return strcmp( $b['family'], $a['family'] );
				}

				return strcmp( $a['family'], $b['family'] );
			} );
		}

		if ( $valid_fonts_only || $published_fonts_only ) {
			foreach ( $fonts as $i => $font ) {
				// Valid Fonts Only
				if ( $valid_fonts_only && ! isset( $font['valid'] ) ) {
					unset( $fonts[ $i ] );
				} // Published Fonts Only
				else if ( $published_fonts_only && ( ! isset( $font['font_status'] ) || 'published' !== $font['font_status'] ) ) {
					unset( $fonts[ $i ] );
				}
			}
		}

		return apply_filters( 'typolab_get_fonts', $fonts, $valid_fonts_only, $published_fonts_only );
	}

	/**
	 * Get single font.
	 *
	 * @param string|int $id
	 *
	 * @return array|null
	 */
	public static function get_font( $id ) {
		$fonts = self::get_fonts();

		// Convert to font-{id}
		if ( is_numeric( $id ) ) {
			$id = "font-{$id}";
		}

		foreach ( $fonts as $font ) {
			if ( isset( $font['id'] ) && $id === $font['id'] ) {
				return $font;
			}
		}

		return null;
	}

	/**
	 * Get Font Settings.
	 *
	 * @return array
	 */
	public static function get_font_settings() {
		return self::get_setting( 'font_settings', [] );
	}

	/**
	 * Font mime types to allow upload.
	 *
	 * @return array
	 */
	public static function get_font_mime_types() {
		return [
			'woff'  => 'font/woff|application/font-woff|application/x-font-woff|application/octet-stream',
			'woff2' => 'font/woff2|application/octet-stream|font/x-woff2',
			'ttf'   => 'application/x-font-ttf|application/octet-stream|font/ttf',
			'svg'   => 'image/svg+xml|application/octet-stream|image/x-svg+xml',
			'eot'   => 'application/vnd.ms-fontobject|application/octet-stream|application/x-vnd.ms-fontobject',
		];
	}

	/**
	 * Delete a font entry.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public static function delete_font( $id ) {
		$fonts_list   = self::get_fonts();
		$font_deleted = false;

		foreach ( $fonts_list as $i => $font ) {
			if ( $id == $font['id'] ) {
				unset( $fonts_list[ $i ] );
				$font_deleted = true;
			}
		}

		if ( $font_deleted ) {
			self::set_setting( 'registered_fonts', $fonts_list );
		}

		return $font_deleted;
	}

	/**
	 * Update font data.
	 *
	 * @param string $font_id
	 * @param array  $font_updated
	 *
	 * @return void
	 */
	public static function save_font( $font_id, $font_updated ) {
		$fonts_list = self::get_fonts();

		foreach ( $fonts_list as & $font ) {
			if ( $font_updated['id'] == $font['id'] ) {
				$font = array_merge( $font, $font_updated );
			}
		}

		self::set_setting( 'registered_fonts', $fonts_list );
	}

	/**
	 * Add new font.
	 *
	 * @param string $font_source
	 *
	 * @return string
	 */
	public static function add_font( $font_source ) {
		$new_id     = self::new_id();
		$fonts_list = self::get_fonts();

		$font = [
			'id'      => $new_id,
			'source'  => $font_source,
			'options' => [],
		];

		if ( 'typekit' === $font_source ) {
			$font['options']['adobe_fonts'] = true;
		}

		$fonts_list[] = $font;

		self::set_setting( 'registered_fonts', $fonts_list );

		return $new_id;
	}

	/**
	 * Generate Unique Font ID.
	 *
	 * @return string
	 */
	public static function new_id() {
		$new_id = self::get_setting( 'id_iterator', 1 );

		// Increment ID iterator
		self::set_setting( 'id_iterator', $new_id + 1 );

		// If ID exists
		if ( self::get_font( $new_id ) ) {
			return self::new_id();
		}

		return "font-{$new_id}";
	}

	/**
	 * Check if font is Adobe Fonts.
	 *
	 * @param array $font
	 *
	 * @return bool
	 */
	public static function is_adobe_font( $font ) {
		return is_array( $font ) && isset( $font['options']['adobe_fonts'] ) && true === $font['options']['adobe_fonts'];
	}

	/**
	 * Silence is golden file maker.
	 *
	 * @param string|string[] $directory
	 *
	 * @return bool|int
	 */
	public static function silence_is_golden( $directory ) {
		$silence = '<!-- Silence is golden. -->';

		if ( is_array( $directory ) ) {
			$res = [];

			foreach ( $directory as $dir ) {
				$res[] = self::silence_is_golden( $dir );
			}

			return count( array_filter( $res ) ) == count( $res );
		}

		return kalium()->filesystem->put_contents( wp_normalize_path( $directory . '/index.html' ), $silence );
	}

	/**
	 * Init Kalium Filesystem.
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public static function init_filesystem( $url = '' ) {

		// Load filesystem functions
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		// Temporary fix for FS_METHOD ftpext
		if ( 'ftpext' === get_filesystem_method() ) {
			add_filter( 'filesystem_method', kalium_hook_return_value( 'direct' ), 100 );
		}

		// Initialize $wp_filesystem
		kalium()->filesystem->initialize( $url );
	}

	/**
	 * Download a font (new implementation).
	 *
	 * @param string $package
	 * @param string $provider
	 *
	 * @return string|WP_Error
	 */
	public static function download_font( $package, $provider = '' ) {
		$dir = self::$fonts_path;

		// Point to provider folder
		if ( ! empty( $provider ) ) {
			$dir .= "/{$provider}";
		}

		// Normalize path
		$name      = 'downloaded-font-' . time() . '.zip';
		$dir       = wp_normalize_path( $dir );
		$file_path = wp_normalize_path( $dir . '/' . $name );

		// Create directory
		if ( wp_mkdir_p( $dir ) ) {
			$request = wp_remote_get( $package, [
				'stream'   => true,
				'filename' => $file_path,
				'timeout'  => 60,
			] );

			$response_code    = wp_remote_retrieve_response_code( $request );
			$response_message = wp_remote_retrieve_response_message( $request );

			// HTTP Error
			if ( 300 <= $response_code ) {
				return new WP_Error( 'typolab_download_v2_http_error', "({$response_code}) {$response_message}" );
			} // WP Error
			else if ( is_wp_error( $request ) ) {
				return $request;
			} else {
				return $file_path;
			}

		} else {
			return new WP_Error( 'typolab_download_v2_cannot_create_directory', 'Cannot create directory, no permissions!' );
		}
	}

	/**
	 * "Any" item of select input.
	 *
	 * @return array
	 */
	public static function any_item() {
		return [
			'value' => '',
			'text'  => '- Any -',
		];
	}

	/**
	 * Delete combined files.
	 *
	 * @return void
	 */
	public static function delete_combined_files() {
		$font_import_files = self::get_setting( 'combined_font_import_files', [] );

		self::init_filesystem();

		foreach ( $font_import_files as $hosted_font ) {
			kalium()->filesystem->delete( $hosted_font['path'] );
		}

		self::set_setting( 'combined_font_import_files', [] );
	}

	/**
	 * Initialize TypoLab.
	 *
	 * @return void
	 */
	public function __construct() {

		// TypoLab Path
		self::$typolab_path = __DIR__;

		// TypoLab Assets URL
		self::$typolab_assets_url = kalium()->locate_file_url( 'includes/libraries/laborator/typolab/assets' );

		// Fonts Path
		$uploads    = wp_upload_dir();
		$fonts_path = $uploads['basedir'] . '/typolab-fonts/';
		$fonts_url  = $uploads['baseurl'] . '/typolab-fonts/';

		self::$fonts_path = $fonts_path;
		self::$fonts_url  = $fonts_url;

		// TypoLab Font Providers
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-google-fonts.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-font-squirrel.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-premium-fonts.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-typekit-fonts.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-custom-font.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-uploaded-font.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-font-sizes.php' );
		kalium()->require_file( __DIR__ . '/inc/classes/typolab-font-loader.php' );

		// Font loader
		add_action( 'init', function () {
			new TypoLab_Font_Loader();
		} );

		// Other Actions
		add_action( 'admin_menu', [ $this, '_typography_menu_item' ] );
		add_action( 'admin_init', [ $this, '_typolab_admin_init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, '_admin_enqueue_scripts' ] );

		// Export/Import Manager
		add_action( 'wp_ajax_typolab-export-import-manager', [ $this, '_ajax_font_export_import_manager' ] );

		// Allow font file types
		add_filter( 'upload_mimes', [ $this, '_upload_mime_types' ] );
		add_filter( 'wp_check_filetype_and_ext', [ $this, '_fix_wp_check_file_type_and_ext' ], 10, 4 );
	}

	/**
	 * TypoLab Admin Screen.
	 *
	 * @return void
	 */
	public function _typolab_page() {
		$admin_tpls = self::$typolab_path . '/admin-tpls';
		$page       = kalium()->request->query( 'typolab-page' );
		$action     = kalium()->request->query( 'typolab-action' );

		switch ( $page ) {
			case 'settings':
				$title = 'Font Settings';
				require $admin_tpls . '/typolab-settings.php';
				break;

			case 'font-sizes':
				$title = 'Font Sizes';
				require $admin_tpls . '/typolab-font-sizes.php';
				break;

			default:
				// Default Title
				$title = 'Fonts {add-font-link}';

				// Add Font
				if ( 'add-font' == $action ) {
					$title = 'Add New Font';
					require $admin_tpls . '/typolab-add-font.php';

					return;
				} // Edit Font
				else if ( 'edit-font' == $action && ( $font_id = kalium()->request->query( 'font-id' ) ) ) {
					$font = self::get_font( $font_id );

					if ( $font ) {
						$title = 'Edit Font';

						if ( ! empty( $font['family'] ) ) {
							$title .= ': "' . $font['family'] . '"';
						} else {
							if ( 'uploaded-font' === $font['source'] ) {
								$title .= ' (Upload Font)';
							} else {
								$title .= " (Select Font)";
							}
						}

						// Font Source
						$sub_title = 'Source: ' . self::$font_sources[ $font['source'] ]['name'];

						require $admin_tpls . '/typolab-edit-font.php';

						return;
					}
				} // Bulk actions
				else if ( kalium()->request->has( 'action', 'post' ) && kalium()->request->has( 'checked', 'post' ) ) {
					$bulk_action = kalium()->request->input( 'action' );
					$checked     = kalium()->request->input( 'checked' );

					// Delete fonts
					if ( 'delete' === $bulk_action && ! empty( $checked ) ) {
						$deleted = false;
						foreach ( $checked as $font_id ) {
							if ( TypoLab::delete_font( $font_id ) ) {
								$deleted = true;
							}
						}

						// Show notice
						if ( $deleted ) {
							kalium()->helpers->add_admin_notice( 'Selected fonts have been deleted!', 'info' );
						}
					}
				}

				require $admin_tpls . '/typolab-main.php';
		}
	}

	/**
	 * Register TypoLab Required Resources.
	 *
	 * @return void
	 */
	public function _typolab_admin_init() {

		// Only for logged users
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Register TypoLab Resources
		wp_register_style( 'typolab-main', self::$typolab_assets_url . '/css/typolab.min.css', null, '1.0', false );
		wp_register_script( 'typolab-main', self::$typolab_assets_url . '/js/typolab-main.min.js', null, '1.1.1', true );

		// Get Available Post Entries for Post Type
		add_action( 'wp_ajax_typolab_get_post_type_entries', [ $this, '_ajax_get_posts_for_post_type' ] );
		add_action( 'wp_ajax_typolab_get_taxonomy_entries', [ $this, '_ajax_get_taxonomies' ] );

		// Switch Pages and Process Actions
		if ( 'typolab' === kalium()->request->query( 'page' ) ) {
			wp_enqueue_style( 'typolab-main' );
			wp_enqueue_script( 'typolab-main' );

			// Tooltips
			wp_enqueue_style( 'tooltipster-bundle', 'https://cdn.jsdelivr.net/jquery.tooltipster/4.1.4/css/tooltipster.bundle.min.css', null, '4.1.4' );
			wp_enqueue_script( 'tooltipster-bundle', 'https://cdn.jsdelivr.net/jquery.tooltipster/4.1.4/js/tooltipster.bundle.min.js', null, '4.1.4' );

			$action  = kalium()->request->query( 'typolab-action' );
			$font_id = kalium()->request->query( 'font-id' );

			// Edit Font Page
			if ( 'edit-font' === $action ) {
				wp_enqueue_script( 'jquery-ui-sortable' );

				// Save Font
				if ( kalium()->request->input( 'save_font_changes' ) && check_admin_referer( 'typolab-save-font-changes' ) ) {
					$this->save_font_changes();
				}

				// Show notice for current editing font when its unpublished
				$font = self::get_font( $font_id );

				if ( $font_status = kalium()->request->input( 'font_status' ) ) {
					$font['font_status'] = $font_status;
				}

				if ( $font && ! empty( $font['font_status'] ) && 'unpublished' == $font['font_status'] ) {
					kalium()->helpers->add_admin_notice( 'This font is not published. To enable this font click <strong>Show Advanced Options</strong> &raquo; <strong>Font status</strong> below in this page.', 'warning' );
				}
			} // Delete Font from List
			else if ( 'delete-font' === $action ) {
				if ( self::delete_font( $font_id ) ) {
					kalium()->helpers->add_admin_notice( 'Font has been deleted.', 'information' );
				}
			} // Delete Combined Files
			else if ( 'delete-combined-files' === $action ) {
				self::delete_combined_files();
				kalium()->helpers->add_admin_notice( 'Combined files are deleted successfully' );
			} // Delete Custom Font Sizes Group
			else if ( 'delete-size-group' === $action ) {
				$delete_group_id = kalium()->request->query( 'group-id' );

				if ( TypoLab_Font_Sizes::delete_custom_font_group( $delete_group_id ) ) {
					kalium()->helpers->add_admin_notice( 'Custom font sizes group has been deleted.', 'info' );
				}
			}

			// Font Sizes Page
			if ( 'font-sizes' === kalium()->request->query( 'typolab-page' ) ) {
				// Save Font Sizes
				if ( kalium()->request->input( 'save_font_sizes' ) && check_admin_referer( 'typolab-save-font-sizes' ) ) {
					$this->save_font_size_changes();
				}
			} // Font Settings Page
			else if ( 'settings' === kalium()->request->query( 'typolab-page' ) ) {

				// Delete Font Files
				if ( $downloaded_font = kalium()->request->query( 'delete-font-files' ) ) {
					$delete_font    = explode( ',', $downloaded_font );
					$delete_font_id = isset( $delete_font[1] ) ? $delete_font[1] : '';
					$font_to_delete = null;

					switch ( $delete_font[0] ) {

						// Delete "Premium Font" entry from database of downloaded fonts
						case 'premium-fonts':
							$premium_fonts_downloads = TypoLab_Premium_Fonts::get_downloaded_fonts();

							if ( isset( $premium_fonts_downloads[ $delete_font_id ] ) ) {
								$font_to_delete = $premium_fonts_downloads[ $delete_font_id ];

								TypoLab_Premium_Fonts::remove_downloaded_font( $delete_font_id );
							}
							break;

						// Delete "Font Squirrel" entry from database of downloaded fonts
						case 'font-squirrel':
							$font_squirrel_downloads = TypoLab_Font_Squirrel::get_downloaded_fonts();

							if ( isset( $font_squirrel_downloads[ $delete_font_id ] ) ) {
								$font_to_delete = $font_squirrel_downloads[ $delete_font_id ];

								TypoLab_Font_Squirrel::remove_downloaded_font( $delete_font_id );
							}
							break;
					}

					// Delete Font Files
					if ( $font_to_delete ) {

						// Init WP Filesystem
						kalium()->filesystem->initialize();

						$uploads  = wp_upload_dir();
						$font_dir = $uploads['basedir'] . '/' . $font_to_delete['path'];

						// Delete font files
						$deleted = kalium()->filesystem->rmdir( $font_dir, true );

						if ( true === $deleted ) {
							kalium()->helpers->add_admin_notice( 'Font files have been deleted successfully.' );
						} else {
							kalium()->helpers->add_admin_notice( 'Cannot delete font files directory, no permissions!', 'error' );
						}
					}
				}

				// Save Font Settings
				if ( kalium()->request->input( 'save_font_settings' ) && check_admin_referer( 'typolab-save-font-settings' ) ) {
					$typolab_enabled = kalium()->request->input( 'typolab_enabled' );

					$font_preview_text = kalium()->request->input( 'font_preview_text' );
					$font_preview_size = kalium()->request->input( 'font_preview_size' );
					$font_placement    = kalium()->request->input( 'font_placement' );
					$font_combining    = kalium()->request->input( 'font_combining' );

					$import_font_settings = kalium()->request->input( 'typolab_import_font_settings' );

					// Font Settings
					$font_settings = self::get_font_settings();

					// Font Preview Text
					$font_settings['typolab_enabled'] = 'yes' === $typolab_enabled;

					// Font Preview Text
					if ( $font_preview_text ) {
						$font_settings['font_preview_str'] = $font_preview_text;
					}

					// Font Preview Size
					if ( is_numeric( $font_preview_size ) && $font_preview_size > 0 ) {
						$font_settings['font_preview_size'] = $font_preview_size;
					}

					// Font Placement
					if ( ! empty( $font_placement ) ) {
						$font_settings['font_placement'] = $font_placement;
					}

					// Font Files Combining
					$font_settings['font_combining'] = 'yes' === $font_combining;

					// Save font settings
					self::set_setting( 'font_settings', $font_settings );

					kalium()->helpers->add_admin_notice( 'Font settings have been saved.' );

					// Import Font Settings
					if ( ! empty( $import_font_settings ) ) {
						$import_font_settings = maybe_unserialize( base64_decode( $import_font_settings ) );

						if ( is_array( $import_font_settings ) ) {
							include_once( self::$typolab_path . '/inc/classes/typolab-font-export-import.php' );

							$export_import_manager = new TypoLab_Font_Export_Import();

							// Import Settings
							if ( $export_import_manager->import( $import_font_settings ) ) {
								kalium()->helpers->add_admin_notice( 'Font import was successful.' );
							}
						}
					}

				}
			}
		}

		// Font Settings
		$font_settings = self::get_font_settings();

		// TypoLab Plugin Status
		if ( isset( $font_settings['typolab_enabled'] ) ) {
			self::$typolab_enabled = $font_settings['typolab_enabled'];
		}

		// Font Preview Text
		if ( false == empty( $font_settings['font_preview_str'] ) ) {
			self::$font_preview_str = $font_settings['font_preview_str'];
		}

		// Font Preview Size
		if ( false == empty( $font_settings['font_preview_size'] ) ) {
			self::$font_preview_size = $font_settings['font_preview_size'];
		}

		// Font Placement
		if ( false == empty( $font_settings['font_placement'] ) ) {
			self::$font_placement = $font_settings['font_placement'];
		}

		// Font Files Combining
		if ( isset( $font_settings['font_combining'] ) ) {
			self::$font_combining = $font_settings['font_combining'];
		}

		// Missing Fonts
		$fonts = self::get_fonts( true, true );

		$downloaded_fonts = [
			'font-squirrel' => TypoLab_Font_Squirrel::get_downloaded_fonts(),
			'premium'       => TypoLab_Premium_Fonts::get_downloaded_fonts()
		];

		foreach ( $fonts as $font ) {
			$font_id = $font['id'];

			// Check if font squirrel is installed
			if ( 'font-squirrel' == $font['source'] ) {
				$font_data = $font['options']['data'];

				if ( is_array( $font_data ) ) {
					$font_data = reset( $font_data );
				}

				$family_urlname = $font_data->family_urlname;

				if ( ! isset( $downloaded_fonts['font-squirrel'][ $family_urlname ] ) ) {
					self::$missing_fonts[ $font_id ] = $font;
				}
			} // Check if premium font is installed
			else if ( 'premium' == $font['source'] ) {
				$family_urlname = $font['options']['data']->family_urlname;

				if ( ! isset( $downloaded_fonts['premium'][ $family_urlname ] ) ) {
					self::$missing_fonts[ $font_id ] = $font;
				}
			}
		}

		// Add Sitewide Font Install Warning
		if ( self::$missing_fonts && 'typolab' !== kalium()->request->query( 'page' ) ) {
			$missing_fonts_count = count( self::$missing_fonts );
			$font_warning        = sprintf( '<strong>TypoLab:</strong> %s to be installed. <a href="%s">Click here</a> to install them &raquo;', $missing_fonts_count > 1 ? "There are <strong>{$missing_fonts_count}</strong> fonts that need" : "There is a font that needs", admin_url( 'admin.php?page=typolab' ) );

			kalium()->helpers->add_admin_notice( $font_warning, 'warning', false );
		}

		// Disabled fonts
		if ( false === self::$typolab_enabled && 'typolab' === kalium()->request->query( 'page' ) && 'settings' !== kalium()->request->query( 'typolab-page' ) ) {
			kalium()->helpers->add_admin_notice( sprintf( 'TypoLab fonts are currently disabled, to enable fonts click <a href="%s">here</a> &raquo;', esc_url( admin_url( 'admin.php?page=typolab&typolab-page=settings&typolab-advanced-settings' ) ) ), 'warning' );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function _admin_enqueue_scripts() {
		if ( 'typolab' === kalium()->request->query( 'page' ) ) {
			kalium_enqueue( 'fontawesome-css' );
		}
	}

	/**
	 * Export/Import Manager.
	 *
	 * @return void
	 */
	public function _ajax_font_export_import_manager() {
		include_once( self::$typolab_path . '/inc/classes/typolab-font-export-import.php' );

		$response = [];
		$exporter = new TypoLab_Font_Export_Import();

		// Export settings
		if ( kalium()->request->input( 'doExport' ) ) {
			$font_faces    = kalium()->request->input( 'fontFaces' );
			$font_sizes    = kalium()->request->input( 'fontSizes' );
			$font_settings = kalium()->request->input( 'fontSettings' );

			$response['exported'] = base64_encode( maybe_serialize( $exporter->export( $font_faces, $font_sizes, $font_settings ) ) );
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Get Post Entries for Post Type (Conditional Loading Module).
	 *
	 * @return void
	 */
	public function _ajax_get_posts_for_post_type() {
		$resp = [
			'success' => false,
		];

		if ( $post_type = esc_attr( kalium()->request->input( 'post_type' ) ) ) {
			$entries          = new WP_Query( "post_type={$post_type}&posts_per_page=-1" );
			$entries_select   = [];
			$entries_select[] = self::any_item();

			while ( $entries->have_posts() ) {
				$entries->the_post();

				$entries_select[] = [
					'value' => get_the_ID(),
					'text'  => get_the_title()
				];
			}

			$resp['entries'] = $entries_select;
			$resp['success'] = true;
		}

		echo json_encode( $resp );
		die();
	}

	/**
	 * Get Taxonomy Entries (Conditional Loading Module).
	 *
	 * @return void
	 */
	public function _ajax_get_taxonomies() {
		$resp = [
			'success' => false,
		];

		if ( $taxonomy = kalium()->request->input( 'taxonomy' ) ) {
			$entries = get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => false
			] );

			$entries_select   = [];
			$entries_select[] = self::any_item();

			foreach ( $entries as $entry ) {

				$entries_select[] = [
					'value' => $entry->term_id,
					'text'  => $entry->name
				];
			}

			$resp['entries'] = $entries_select;
			$resp['success'] = true;
		}

		echo json_encode( $resp );
		die();
	}

	/**
	 * Typography Menu Item.
	 *
	 * @return void
	 */
	public function _typography_menu_item() {

		// Add New Font
		if ( ! is_null( kalium()->request->input( 'typolab_add_font' ) ) && check_admin_referer( 'typolab-add-font' ) ) {
			$font_source      = kalium()->request->input( 'font_source' );
			$font_sources_ids = array_keys( self::$font_sources );

			if ( in_array( $font_source, $font_sources_ids ) ) {
				$font_id = self::add_font( $font_source );
				$url     = admin_url( 'admin.php?page=' . kalium()->request->query( 'page' ) . '&typolab-action=edit-font&font-id=' . $font_id );
				wp_redirect( $url );
				exit();
			}
		}

		add_submenu_page( 'laborator_options', 'Typography', 'Typography', 'edit_theme_options', 'typolab', [
			& $this,
			'_typolab_page'
		] );
	}

	/**
	 * Add font file types to allowed mimes.
	 *
	 * @param array $mime_types
	 *
	 * @return array
	 */
	public function _upload_mime_types( $mime_types ) {
		if ( current_user_can( 'manage_options' ) ) {
			foreach ( self::get_font_mime_types() as $type => $mime ) {
				if ( ! isset( $mime_types[ $type ] ) ) {
					$mime_types[ $type ] = $mime;
				}
			}
		}

		return $mime_types;
	}

	/**
	 * A workaround for upload validation which relies on a PHP extension (fileinfo) with inconsistent reporting behaviour.
	 *
	 * @param array    $data
	 * @param string   $file
	 * @param string   $filename
	 * @param string[] $mimes
	 *
	 * @return array
	 */
	public function _fix_wp_check_file_type_and_ext( $data, $file, $filename, $mimes ) {
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}

		$registered_file_types = self::get_font_mime_types();
		$filetype              = wp_check_filetype( $filename, $mimes );

		if ( ! isset( $registered_file_types[ $filetype['ext'] ] ) ) {
			return $data;
		}

		return [
			'ext'             => $filetype['ext'],
			'type'            => $filetype['type'],
			'proper_filename' => $data['proper_filename'],
		];
	}

	/**
	 * Save Font Changes from Form Method.
	 *
	 * @return void
	 */
	private function save_font_changes() {
		$font_id = kalium()->request->query( 'font-id' );
		$font    = self::get_font( $font_id );

		$font_family    = kalium()->request->input( 'font_family' );
		$font_variants  = kalium()->request->input( 'font_variants' );
		$font_subsets   = kalium()->request->input( 'font_subsets' );
		$font_data      = kalium()->request->input( 'font_data' );
		$font_selectors = kalium()->request->input( 'font_selectors' );

		// Set Font Family
		if ( ! empty( $font_family ) ) {
			$font['valid']  = true;
			$font['family'] = $font_family;
		}

		// Font Variants
		$font['variants'] = $font_variants;

		// Font Subsets
		if ( ! empty( $font_subsets ) ) {
			$font['subsets'] = $font_subsets;
		}

		// Font Data
		if ( ! empty( $font_data ) ) {
			$font['options']['data'] = @json_decode( wp_unslash( $font_data ) );
		}

		// Font Selectors
		$font['options']['selectors'] = array_map( 'stripslashes_deep', $this->preserve_selectors_order( $font_selectors ) );

		// Creation Date
		if ( empty( $font['options']['created_time'] ) ) {
			$font['options']['created_time'] = time();
		}

		// Font Squirrel Generate Variants File
		if ( 'font-squirrel' === $font['source'] ) {
			$selected_variants = [];
			$downloaded_fonts  = TypoLab_Font_Squirrel::get_downloaded_fonts();

			if ( ! empty( $font['options']['data'] ) && ! empty( $font_variants ) ) {
				$font_squirrel_font_variant = $font['options']['data'];
				$family_urlname             = $font_squirrel_font_variant[0]->family_urlname;

				foreach ( $font_variants as $variant ) {
					foreach ( $font_squirrel_font_variant as $font_squirrel_font ) {
						if ( $variant == $font_squirrel_font->fontface_name ) {
							$selected_variants[] = $font_squirrel_font;
						}
					}
				}

				// Generate Font Load File with Selected Variants
				if ( isset( $downloaded_fonts[ $family_urlname ] ) ) {
					$uploads   = wp_upload_dir();
					$font_path = $uploads['basedir'] . "/{$downloaded_fonts[ $family_urlname ]['path']}";

					TypoLab_Font_Squirrel::create_font_include_file( $selected_variants, $font_path, 'load.css' );
				}
			}
		} // Premium Font Generate Variants & Subsets File
		else if ( 'premium' === $font['source'] ) {
			$downloaded_fonts = TypoLab_Premium_Fonts::get_downloaded_fonts();

			if ( $font['options']['data'] ) {
				$premium_font_data = $font['options']['data'];

				if ( isset( $downloaded_fonts[ $premium_font_data->family_urlname ] ) ) {
					$uploads  = wp_upload_dir();
					$font_dir = $uploads['basedir'] . '/' . $downloaded_fonts[ $premium_font_data->family_urlname ]['path'];

					TypoLab_Premium_Fonts::create_font_include_file( $premium_font_data, $font_dir, 'load.css' );
				}
			}
		} // Custom Font Process Options
		else if ( 'custom-font' === $font['source'] || self::is_adobe_font( $font ) ) {
			$font_url_str  = kalium()->request->input( 'font_url' );
			$font_url      = wp_extract_urls( $font_url_str );
			$font_variants = TypoLab_Custom_Font::wrap_font_family_name( kalium()->request->input( 'font_variants' ) );

			// Get only the first url
			$font_url = $font_url ? rtrim( reset( $font_url ), '\\' ) : '';

			if ( empty( $font_url ) && strlen( $font_url_str ) <= 10 ) {
				$font_url = $font_url_str;
			}

			if ( is_array( $font_variants ) ) {
				$font_variants = array_map( 'wp_unslash', $font_variants );
			}

			$font['valid']  = true;
			$font['family'] = TypoLab_Custom_Font::clear_font_family_name( $font_variants[0] );

			$font['options']['font_url']      = $font_url;
			$font['options']['font_variants'] = $font_variants;
		} // TypeKit Font Process
		else if ( 'typekit' === $font['source'] && ! self::is_adobe_font( $font ) ) {
			$kit_id = kalium()->request->input( 'kit_id' );

			// Save Kit ID
			$font['kit_id'] = $kit_id;

			// Get Kit Options
			$typekit  = new Typekit();
			$kit_info = $typekit->get( $kit_id );

			// Kit does exists
			if ( $kit_info !== null ) {
				$font_family = $kit_info['kit']['families'];

				if ( is_array( $font_family ) ) {
					$font_family = $kit_info['kit']['families'][0]['name'];

					$font['valid']  = true;
					$font['family'] = $font_family;
				}

				$font['options']['data'] = $kit_info;
			} // Kit does not exits
			else {
				kalium()->helpers->add_admin_notice( 'Kit ID <strong>' . esc_html( $kit_id ) . '</strong> does not exists, font will not be loaded in frontend.', 'error' );
			}
		} // Uploaded Font Process
		else if ( 'uploaded-font' === $font['source'] ) {
			$font_variants = kalium()->request->input( 'font_variant' );

			if ( empty( $font_variants ) ) {
				$font_variants = [];
			}

			$font['valid']                    = true;
			$font['family']                   = kalium()->request->input( 'font_face' );
			$font['options']['font_variants'] = array_values( $font_variants );
		}

		// Conditional Font Loading
		$statements = kalium()->request->input( 'statements' );
		$operators  = kalium()->request->input( 'operators' );
		$criterions = kalium()->request->input( 'criterions' );

		$conditional_statements = [];

		if ( is_array( $statements ) && count( $statements ) ) {
			foreach ( $statements as $i => $statement ) {
				$operator = $operators[ $i ];
				$criteria = $criterions[ $i ];

				$conditional_statements[] = [
					'statement' => $statement,
					'operator'  => $operator,
					'criteria'  => $criteria
				];
			}
		}

		$font['options']['conditional_loading'] = $conditional_statements;

		// Font Status
		$font_status         = kalium()->request->input( 'font_status' );
		$font['font_status'] = $font_status;

		// Font Enqueue
		$font_placement         = kalium()->request->input( 'font_placement' );
		$font['font_placement'] = $font_placement;

		// Delete Combined Files
		self::delete_combined_files();

		// Delete Font Loading Cache
		TypoLab_Font_Loader::delete_font_selectors_cache();

		// Save Font
		self::save_font( $font_id, $font );

		// Show Font Updated Message
		kalium()->helpers->add_admin_notice( 'Font changes have been saved.' );
	}

	/**
	 * Save Font Size Changes.
	 *
	 * @return void
	 */
	private function save_font_size_changes() {
		$new_font_sizes = kalium()->request->input( 'font_sizes' );

		// Get values from defined grup sizes
		$font_sizes = TypoLab_Font_Sizes::get_font_sizes();

		foreach ( $font_sizes as $i => & $size_group ) {
			$size_group = array_merge( $size_group, $new_font_sizes[ $i ] );
		}

		// Save Settings
		self::set_setting( 'font_sizes', $font_sizes );
		kalium()->helpers->add_admin_notice( 'Font sizes have been saved.', 'success' );

		// Create Group Info
		$new_group_title       = kalium()->request->input( 'new_group_title' );
		$new_group_description = kalium()->request->input( 'new_group_description' );
		$new_group_size_alias  = kalium()->request->input( 'new_group_size_alias' );
		$new_group_size_path   = kalium()->request->input( 'new_group_size_path' );

		// Delete Font Loading Cache
		TypoLab_Font_Loader::delete_font_selectors_cache();

		// Add Custom Selectors Group
		if ( $new_group_title && is_array( $new_group_size_path ) && is_array( $new_group_size_path ) ) {
			$new_selectors = [];

			foreach ( $new_group_size_alias as $i => $selector_id ) {
				if ( ! empty( $selector_id ) && ! empty( $new_group_size_path[ $i ] ) ) {
					$new_selectors[ $selector_id ] = $new_group_size_path[ $i ];
				}
			}

			$custom_font_size_group = [
				'title'       => $new_group_title,
				'description' => $new_group_description,
				'selectors'   => stripslashes_deep( $new_selectors ),
				'builtin'     => false,
				'sizes'       => [],
			];

			TypoLab_Font_Sizes::addCustomFontSizeGroup( $custom_font_size_group );

			kalium()->helpers->add_admin_notice( 'Font size group has been created.', 'success' );
		}
	}

	/**
	 * Preserve Selectors Order.
	 *
	 * @param array $selectors
	 *
	 * @return array
	 */
	private function preserve_selectors_order( $selectors ) {
		$new_array = [];

		if ( ! is_array( $selectors ) ) {
			return $new_array;
		}

		foreach ( $selectors as $selector_row ) {
			$new_array[] = $selector_row;
		}

		return $new_array;
	}
}

// Initialize Typolab
TypoLab::instance();
