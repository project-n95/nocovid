<?php
/**
 * Kalium WordPress Theme
 *
 * Premium fonts.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Premium_Fonts {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	public static $provider_id = 'premium-fonts';

	/**
	 * Loaded Premium Fonts.
	 *
	 * @var array
	 */
	public static $fonts_list = [];

	/**
	 * Unicode ranges.
	 *
	 * @var array
	 */
	public static $unicode_ranges = [
		'macroman'      => 'U+20-126, U+161-255, U+338-339, U+376, U+710, U+732, U+2019, U+201C-201E, U+8192-8202, U+8208-8212, U+8216-8218, U+8220-8222, U+8226, U+8230, U+8239, U+8249-8250, U+8287, U+8364, U+8482, U+9724, U+64257-64258, U+FFEB',
		'afrikaans'     => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+200-203, U+206-207, U+212, U+219, U+232-235, U+238-239, U+244, U+251, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'english'       => 'U+20-126, U+162-163, U+165, U+169, U+174, U+180, U+2019, U+201C-201E, U+8211-8212, U+8216-8217, U+8220-8221, U+8226, U+8230, U+8364, U+8482, U+FFEB',
		'french'        => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+192, U+194, U+198-203, U+206-207, U+212, U+217, U+219-220, U+224, U+226, U+230-235, U+238-239, U+244, U+249, U+251-252, U+255, U+338-339, U+376, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'german'        => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+196, U+214, U+220, U+223, U+228, U+246, U+252, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'latin'         => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'latin-extreme' => 'U+20-126, U+160-263, U+268-275, U+278-283, U+286-287, U+290-291, U+298-299, U+302-305, U+310-311, U+313-318, U+321-328, U+332-333, U+336-347, U+350-357, U+362-363, U+366-371, U+376-382, U+536-539, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8226, U+8230, U+8249-8250, U+8364, U+8482, U+64257-64258, U+FFEB',
		'spanish'       => 'U+20-126, U+161-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+191, U+193, U+201, U+205, U+209, U+211, U+218, U+220, U+225, U+233, U+237, U+241, U+243, U+250, U+252, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'swedish'       => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+192-193, U+196-197, U+201, U+203, U+214, U+220, U+224-225, U+228-229, U+233, U+235, U+246, U+252, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB',
		'turkish'       => 'U+20-126, U+162-163, U+165, U+168-169, U+171, U+174, U+180, U+184, U+187, U+194, U+199, U+206, U+214, U+219-220, U+226, U+231, U+238, U+246, U+251-252, U+286-287, U+304-305, U+350-351, U+710, U+730, U+732, U+2019, U+201C-201E, U+8211-8212, U+8216-8218, U+8220-8222, U+8230, U+8249-8250, U+8364, U+8482, U+FFEB'
	];

	/**
	 * Single Line Font Preview Link.
	 *
	 * @param array $font
	 *
	 * @return string
	 */
	public static function single_line_preview( $font ) {
		if ( ! isset( $font['options']['data']->family_urlname ) ) {
			return '';
		}

		$font_data = $font['options']['data'];

		return admin_url( sprintf( 'admin-ajax.php?action=typolab-preview-premium-fonts&single-line=true&font-family=%s', rawurlencode( $font_data->family_urlname ) ) );
	}

	/**
	 * Install font family.
	 *
	 * @param string $font_family
	 * @param array  $font_to_import
	 *
	 * @return true|WP_Error
	 * @since 3.0
	 */
	public static function install_font( $font_family, $font_to_import = [] ) {
		$fonts_list   = self::get_fonts_list();
		$install_font = null;

		foreach ( $fonts_list as $font ) {
			if ( $font_family === $font->family_urlname || $font_family === $font->family ) {
				$install_font = $font;
			}
		}

		// Font doesn't exists
		if ( is_null( $install_font ) ) {
			return new WP_Error( 'typolab_premium_font_not_exists', 'Font family doesn\' exists!' );
		}

		// If theme is not registered
		if ( ! kalium()->theme_license->is_theme_registered() ) {
			return new WP_Error( 'typolab_theme_not_registered', 'Theme must be registered in order to download premium fonts!' );
		}

		// Font family url name
		$family_urlname = $install_font->family_urlname;

		// Package download link
		$package = str_replace( '{license-key}', kalium()->theme_license->get_license_key(), $install_font->package );

		if ( version_compare( kalium_get_openssl_version_number(), '1.0', '<' ) ) {
			$package = set_url_scheme( $package, 'http' );
		}

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
		$extracted_files = list_files( $extract_tmp_dir, 1 );
		$extracted_dir   = reset( $extracted_files );

		// Font directory
		$upload_dir   = wp_upload_dir();
		$font_dir     = wp_normalize_path( dirname( $package_file ) . "/{$family_urlname}" );
		$font_dir_rel = ltrim( str_replace( wp_normalize_path( $upload_dir['basedir'] ), '', $font_dir ), '/' );

		// Create font directory if not exists
		if ( ! file_exists( $font_dir ) ) {

			// Move extracted folder to its fonts directory
			kalium()->filesystem->move( $extracted_dir, $font_dir, true );

			// Delete extract folder
			kalium()->filesystem->delete( $extract_tmp_dir, true );

			// Silence is golden folder
			TypoLab::silence_is_golden( $font_dir );
		}

		// Generate load file
		if ( ! empty( $font_to_import ) && isset( $font_to_import['options'], $font_to_import['options']['data'] ) ) {
			TypoLab_Premium_Fonts::create_font_include_file( $font_to_import['options']['data'], $font_dir, 'load.css' );
		}

		// Create preview file
		self::create_font_include_file( $install_font, $font_dir, 'preview.css' );

		// Save as downloaded font
		self::add_downloaded_font( $family_urlname, $font_dir_rel );

		return true;
	}

	/**
	 * Create CSS File that Includes Specified Font Variants.
	 *
	 * @param object $font
	 * @param string $font_dir
	 * @param string $font_file_name
	 *
	 * @return string
	 */
	public static function create_font_include_file( $font, $font_dir, $font_file_name = 'font.css' ) {
		$font_file_path = "{$font_dir}/{$font_file_name}";
		$stylesheets    = [];

		$font_subsets  = $font->subsets;
		$font_variants = $font->variants;

		// Convert From Object to Array
		if ( is_object( $font_subsets ) ) {
			$font_subsets = array_keys( (array) $font_subsets );
		}

		if ( is_object( $font_variants ) ) {
			$font_variants = array_keys( (array) $font_variants );
		}

		if ( $font_subsets ) {
			foreach ( $font_subsets as $subset ) {
				if ( $font_variants ) {
					foreach ( $font_variants as $variant ) {
						$font_files  = glob( "{$font_dir}/{$subset}/{$variant}_*/stylesheet.css" );
						$stylesheets = array_merge( $stylesheets, $font_files );
					}
				}
			}
		}


		// Append Font Faces
		$contents = [];

		foreach ( $stylesheets as $stylesheet ) {
			$font_variant_relative_path = dirname( $stylesheet );
			$font_variant_path          = basename( $font_variant_relative_path );
			$font_subset_path           = basename( dirname( $font_variant_relative_path ) );

			$font_facename = preg_replace( '/_[a-z0-9-]+$/i', '', $font_variant_path );

			$stylesheet_contents = file_get_contents( $stylesheet );
			$stylesheet_contents = str_replace( "url('", "url('{$font_subset_path}/{$font_variant_path}/", $stylesheet_contents );

			// Set Font Name
			$stylesheet_contents = preg_replace( "/font-family: '.*?';/", "font-family: '{$font_facename}';", $stylesheet_contents );

			// Set Unicode Range for the given subset
			if ( isset( self::$unicode_ranges[ $font_subset_path ] ) ) {
				$unicode_range       = self::$unicode_ranges[ $font_subset_path ];
				$stylesheet_contents = preg_replace( "/(\@font-face\s+\{)(.*?)(\})/si", "/* Subset: {$font_subset_path} */\n\\1\\2\tunicode-range: {$unicode_range};\n\\3", $stylesheet_contents );
			}

			$contents[] = $stylesheet_contents;
		}

		// Init file system to write the CSS file
		TypoLab::init_filesystem();

		return kalium()->filesystem->put_contents( $font_file_path, implode( '', $contents ) );
	}

	/**
	 * Get downloaded fonts.
	 *
	 * @return array
	 */
	public static function get_downloaded_fonts() {
		return TypoLab::get_setting( 'premium_fonts_downloads', [] );
	}

	/**
	 * Add downloaded font.
	 *
	 * @param string $family
	 * @param string $font_path
	 *
	 * @return void
	 */
	public static function add_downloaded_font( $family, $font_path ) {
		$downloaded_fonts = self::get_downloaded_fonts();

		$downloaded_fonts[ $family ] = array(
			'date' => time(),
			'path' => $font_path
		);

		TypoLab::set_setting( 'premium_fonts_downloads', $downloaded_fonts );
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

		TypoLab::set_setting( 'premium_fonts_downloads', $downloaded_fonts );
	}

	/**
	 * Load Premium Fonts from JSON File.
	 *
	 * @return array
	 */
	public static function get_fonts_list() {

		// Once initialized, no need to load fonts list again
		if ( self::$fonts_list ) {
			return self::$fonts_list;
		}

		$fonts_json       = file_get_contents( TypoLab::$typolab_path . '/assets/json/premium-fonts.json' );
		self::$fonts_list = @json_decode( $fonts_json );

		return self::$fonts_list;
	}

	/**
	 * Get Alphabetic Order.
	 *
	 * @return array
	 */
	public static function group_fonts_by_first_letter() {
		$alphabetic_order = [];

		foreach ( self::$fonts_list as $font ) {
			$first = strtoupper( substr( $font->family, 0, 1 ) );
			if ( ! isset( $alphabetic_order[ $first ] ) ) {
				$alphabetic_order[ $first ] = array( 'letter' => $first, 'count' => 1 );
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
	 * Get All Font Categories.
	 *
	 * @return array
	 */
	public static function group_fonts_by_category() {
		$font_categories = [];

		foreach ( self::$fonts_list as $font ) {
			$category = $font->category;

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
	 * Initialize Premium Fonts Adapter.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_typolab_premium_fonts_download', [ & $this, '_ajax_install_font' ] );
		add_action( 'wp_ajax_typolab-preview-premium-fonts', [ & $this, '_preview' ] );
	}

	/**
	 * Download and Install Premium Font.
	 *
	 * @return void
	 */
	public function _ajax_install_font() {
		$response = [
			'errors'    => true,
			'error_msg' => '',
		];

		$font_family = kalium()->request->input( 'font_family' );
		$result      = self::install_font( $font_family );

		if ( is_wp_error( $result ) ) {
			$response['error_msg'] = $result->get_error_messages();
		} else {
			$response['errors']     = false;
			$response['downloaded'] = true;
		}

		echo wp_json_encode( $response );
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
		$fonts_list   = self::get_fonts_list();
		$preview_font = null;

		// Get the font
		foreach ( $fonts_list as $font ) {
			if ( $font_family == $font->family_urlname ) {
				$preview_font = $font;
			}
		}

		// Font doesn't exits
		if ( ! $preview_font ) {
			return;
		}

		// Font URL
		$uploads          = wp_upload_dir();
		$font_url         = $uploads['baseurl'] . '/';
		$downloaded_fonts = self::get_downloaded_fonts();

		if ( isset( $downloaded_fonts[ $font_family ] ) ) {
			$font_url .= $downloaded_fonts[ $font_family ]['path'];
			$font_url .= '/preview.css';
		}

		// Font Variants
		$variants = (array) $preview_font->variants;

		// Check if its single line
		$single_line = isset( $_GET['single-line'] );

		if ( $single_line ) {
			$variants = array_slice( $variants, 0, 1 );
		}

		?>
        <html>
        <head>
            <link rel="stylesheet" href="<?php echo TypoLab::$typolab_assets_url . '/css/typolab.min.css'; ?>">
            <link rel="stylesheet" href="<?php echo $font_url; ?>">
            <style>
                .font-entry p {
                    font-size: <?php echo intval( TypoLab::$font_preview_size ); ?>px;
                }
            </style>
        </head>
        <body id="preview-mode">
        <div class="font-preview">
			<?php
			foreach ( $variants as $font_face => $variant ) :
				?>
                <div class="font-entry<?php when_match( $single_line, 'single-entry' ); ?>">
                    <p style="font-family: '<?php echo esc_attr( $font_face ); ?>';"><?php echo esc_html( TypoLab::$font_preview_str ); ?></p>
					<?php if ( ! $single_line ) : ?>
                        <span><?php echo $variant->name; ?></span>
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

new TypoLab_Premium_Fonts();
