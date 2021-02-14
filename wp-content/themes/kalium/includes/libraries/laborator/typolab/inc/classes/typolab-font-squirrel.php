<?php
/**
 * Kalium WordPress Theme
 *
 * Font squirrel loader and manager.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Font_Squirrel {

	/**
	 * Font provider ID.
	 *
	 * @var string
	 */
	public static $provider_id = 'font-squirrel';

	/**
	 * Loaded Font Squirrel fonts.
	 *
	 * @return array
	 */
	public static $fonts_list = [];

	/**
	 * Load Font Squirrel fonts from JSON file.
	 *
	 * @return array
	 */
	public static function get_fonts_list() {

		// Once initialized, no need to load fonts list again
		if ( self::$fonts_list ) {
			return self::$fonts_list;
		}

		$fonts_json       = file_get_contents( TypoLab::$typolab_path . '/assets/json/font-squirrel.json' );
		self::$fonts_list = json_decode( $fonts_json );

		return self::$fonts_list;
	}

	/**
	 * Get downloaded fonts.
	 *
	 * @return array
	 */
	public static function get_downloaded_fonts() {
		return TypoLab::get_setting( 'font_squirrel_downloads', [] );
	}

	/**
	 * Get font family info.
	 *
	 * @param string $family_urlname
	 *
	 * @return mixed|WP_Error
	 */
	public static function get_font_family_info( $family_urlname ) {

		// Font Info
		$request = wp_remote_get( sprintf( 'http://www.fontsquirrel.com/api/familyinfo/%s', $family_urlname ) );

		// On error
		if ( is_wp_error( $request ) ) {
			return $request;
		}

		return json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Get downloaded fonts.
	 *
	 * @param string $family
	 * @param string $font_path
	 *
	 * @return void
	 */
	public static function add_downloaded_font( $family, $font_path ) {
		$downloaded_fonts = self::get_downloaded_fonts();

		$downloaded_fonts[ $family ] = [
			'date' => time(),
			'path' => $font_path
		];

		TypoLab::set_setting( 'font_squirrel_downloads', $downloaded_fonts );
	}

	/**
	 * Remove downloaded font.
	 *
	 * @param string $font_id
	 *
	 * @return void
	 */
	public static function remove_downloaded_font( $font_id ) {
		$downloaded_fonts = self::get_downloaded_fonts();

		if ( isset( $downloaded_fonts[ $font_id ] ) ) {
			unset( $downloaded_fonts[ $font_id ] );
		}

		TypoLab::set_setting( 'font_squirrel_downloads', $downloaded_fonts );
	}

	/**
	 * Single line font preview link.
	 *
	 * @param array $font
	 *
	 * @return string
	 */
	public static function single_line_preview( $font ) {
		$font_data = $font['options']['data'];

		if ( is_array( $font_data ) ) {
			$font_data = reset( $font_data );
		}

		return admin_url( sprintf( 'admin-ajax.php?action=typolab-preview-font-squirrel&single-line=true&font-family=%s', rawurlencode( $font_data->family_urlname ) ) );
	}

	/**
	 * Download and install font squirrel font face.
	 *
	 * @param string $font_family
	 * @param array  $font_to_import
	 *
	 * @return true|WP_Error
	 */
	public static function install_font( $font_family, $font_to_import = null ) {
		$fonts_list   = self::get_fonts_list();
		$install_font = null;

		foreach ( $fonts_list as $font ) {
			if ( $font_family === $font->family_urlname || $font_family === $font->family_name ) {
				$install_font = $font;
				break;
			}
		}

		// Font doesn't exists
		if ( is_null( $install_font ) ) {
			return new WP_Error( 'typolab_font_squirrel_not_exists', 'Font family doesn\' exists!' );
		}

		// Font family url name
		$family_urlname = $install_font->family_urlname;

		// Package
		$package = 'https://www.fontsquirrel.com/fontfacekit/' . $family_urlname;

		// Package file path
		$package_file = TypoLab::download_font( $package, self::$provider_id );

		// On error
		if ( is_wp_error( $package_file ) ) {
			return $package_file;
		}

		// Init filesystem
		TypoLab::init_filesystem();

		// Extract
		$extract_tmp_dir = dirname( $package_file ) . '/extracted';
		$result          = kalium()->filesystem->unzip_file( $package_file, $extract_tmp_dir );

		// Extract errors
		if ( is_wp_error( $result ) ) {
			kalium()->filesystem->delete( $package_file );

			return $result;
		}

		// Delete zip file
		kalium()->filesystem->delete( $package_file );

		// Get extracted folder path
		$extracted_files = list_files( $extract_tmp_dir . '/web fonts/', 1 );

		if ( ! $extracted_files ) {
			return new WP_Error( 'typolab_font_squirrel_not_valid_font', 'Not a valid font!' );
		}

		// Font directory
		$upload_dir   = wp_upload_dir();
		$font_dir     = wp_normalize_path( dirname( $package_file ) . "/{$family_urlname}" );
		$font_dir_rel = ltrim( str_replace( wp_normalize_path( $upload_dir['basedir'] ), '', $font_dir ), '/' );

		// Create font directory if not exists
		if ( ! file_exists( $font_dir ) ) {

			// Move to font dir
			foreach ( $extracted_files as $extracted_file ) {
				$font_variant_dir = $font_dir . DIRECTORY_SEPARATOR . wp_basename( $extracted_file ) . DIRECTORY_SEPARATOR;

				if ( ! file_exists( $font_variant_dir ) ) {
					wp_mkdir_p( $font_variant_dir );
				}

				// Copy font variant to parent font directory
				kalium()->filesystem->copy_dir( $extracted_file, $font_variant_dir );

				// Silence is golden
				TypoLab::silence_is_golden( $font_variant_dir );
			}

			// Delete extract folder
			kalium()->filesystem->delete( $extract_tmp_dir, true );

			// Silence is golden folder
			TypoLab::silence_is_golden( $font_dir );
		}

		// Font Info
		$font_info = self::get_font_family_info( $family_urlname );

		// Generate load file
		if ( ! empty( $font_to_import ) && ! empty( $font_to_import['variants'] ) ) {
			$font_variants = [];

			foreach ( $font_info as $font_variant ) {
				if ( in_array( $font_variant->fontface_name, $font_to_import['variants'] ) ) {
					$font_variants[] = $font_variant;
				}
			}

			self::create_font_include_file( $font_variants, $font_dir, 'load.css' );
		}

		// Create Preview File
		self::create_font_include_file( $font_info, $font_dir, 'preview.css' );

		// Save as downloaded font
		self::add_downloaded_font( $family_urlname, $font_dir_rel );
	}

	/**
	 * Create CSS file that includes specified font variants.
	 *
	 * @param array  $variants
	 * @param string $font_dir
	 * @param string $font_file_name
	 *
	 * @return bool|int
	 */
	public static function create_font_include_file( $variants, $font_dir, $font_file_name = 'font.css' ) {
		if ( true !== file_exists( $font_dir ) ) {
			return false;
		}

		$font_file_path = "{$font_dir}/{$font_file_name}";
		$contents       = [];

		foreach ( $variants as $variant ) {
			$font_family = $variant->fontface_name;
			$style_name  = strtolower( str_replace( ' ', '', $variant->style_name ) );
			$stylesheets = glob( "{$font_dir}/*_{$style_name}_*/stylesheet.css" );

			if ( $stylesheets ) {
				$stylesheets = array_slice( $stylesheets, 0, 1 );

				foreach ( $stylesheets as $stylesheet ) {
					$font_variant_relative_path = basename( dirname( $stylesheet ) );

					$stylesheet_contents = file_get_contents( $stylesheet );
					$stylesheet_contents = str_replace( "url('", "url('{$font_variant_relative_path}/", $stylesheet_contents );
					$stylesheet_contents = preg_replace( "/font-family: '(.*?)';/", "font-family: '{$font_family}';", $stylesheet_contents );

					$contents[] = $stylesheet_contents;
				}
			}
		}

		// Init file system to write the CSS file
		TypoLab::init_filesystem();

		return kalium()->filesystem->put_contents( $font_file_path, implode( '', $contents ) );
	}

	/**
	 * Get alphabetic order.
	 *
	 * @return array
	 */
	public static function group_fonts_by_first_letter() {
		$alphabetic_order = [];

		foreach ( self::$fonts_list as $font ) {
			$first = strtoupper( substr( $font->family_name, 0, 1 ) );
			if ( ! isset( $alphabetic_order[ $first ] ) ) {
				$alphabetic_order[ $first ] = [ 'letter' => $first, 'count' => 1 ];
			} else {
				$alphabetic_order[ $first ]['count'] ++;
			}
		}

		uasort( $alphabetic_order, function ( $a, $b ) {
			return strcmp( $a['letter'], $b['letter'] );
		} );

		return $alphabetic_order;
	}

	/**
	 * Get all font categories.
	 *
	 * @return array
	 */
	public static function group_fonts_by_category() {
		$font_categories = [];

		foreach ( self::$fonts_list as $font ) {
			$category = $font->classification;

			if ( ! isset( $font_categories[ $category ] ) ) {
				$font_categories[ $category ] = [
					'name'  => $category,
					'count' => 1
				];
			} else {
				$font_categories[ $category ]['count'] ++;
			}
		}

		uasort( $font_categories, function ( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		} );

		return $font_categories;
	}

	/**
	 * Initialize TypoLab Google fonts adapter.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_typolab_font_squirrel_download', [ $this, '_ajax_install_font' ] );
		add_action( 'wp_ajax_typolab-preview-font-squirrel', [ $this, '_preview' ] );
	}

	/**
	 * Download font from Font Squirrel and install.
	 *
	 * @return void
	 */
	public function _ajax_install_font() {
		$response = [
			'errors'    => true,
			'error_msg' => 'Unknown error happened!',
		];

		$font_family = kalium()->request->input( 'font_family' );
		$result      = self::install_font( $font_family );

		if ( is_wp_error( $result ) ) {
			$response['error_msg'] = $result->get_error_messages();
		} else {
			$response['errors']     = false;
			$response['downloaded'] = true;
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Preview font.
	 *
	 * @return void
	 */
	public function _preview() {
		$font_family = kalium()->request->query( 'font-family' );

		if ( ! $font_family ) {
			return;
		}

		// Font Info
		$font_info = self::get_font_family_info( $font_family );

		// Font URL
		$uploads          = wp_upload_dir();
		$font_url         = $uploads['baseurl'] . '/';
		$downloaded_fonts = self::get_downloaded_fonts();

		if ( isset( $downloaded_fonts[ $font_family ] ) ) {
			$font_url .= $downloaded_fonts[ $font_family ]['path'];
			$font_url .= '/preview.css';
		}

		// Check if its single line
		$single_line = isset( $_GET['single-line'] );

		if ( $single_line ) {
			$font_info = array_splice( $font_info, 0, 1 );
		}

		?>
        <html>
        <head>
            <link rel="stylesheet" href="<?php echo TypoLab::$typolab_assets_url . '/css/typolab.min.css'; ?>">
            <link rel="stylesheet" href="<?php echo $font_url; ?>">
            <style>
                .font-entry p {
                    font-family: '<?php echo esc_attr( $font_family ); ?>', sans-serif;
                    font-size: <?php echo intval( TypoLab::$font_preview_size ); ?>px;
                }
            </style>
        </head>
        <body id="preview-mode">
        <div class="font-preview">
			<?php
			foreach ( $font_info as $font_variant ) :
				$fontface_name = $font_variant->fontface_name;
				$style_name = $font_variant->style_name;
				?>
                <div class="font-entry<?php when_match( $single_line, 'single-entry' ); ?>">
                    <p style="font-family: '<?php echo esc_attr( $fontface_name ); ?>';"><?php echo esc_html( TypoLab::$font_preview_str ); ?></p>
					<?php if ( ! $single_line ) : ?>
                        <span><?php echo $style_name; ?></span>
					<?php endif; ?>
                </div>
			<?php
			endforeach;
			?>
        </div>
        </body>
        </html>
		<?php
		die();
	}
}

new TypoLab_Font_Squirrel();