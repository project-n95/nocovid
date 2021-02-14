<?php
/**
 * Kalium WordPress Theme
 *
 * Google fonts loader and manager.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Google_Fonts {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	public static $provider_id = 'google-fonts';

	/**
	 * Loaded Google Fonts.
	 *
	 * @var array
	 */
	public static $fonts_list = [];

	/**
	 * Load Google Fonts from JSON File.
	 *
	 * @return array
	 */
	public static function get_fonts_list() {

		// Once initialized, no need to load fonts list again
		if ( self::$fonts_list ) {
			return self::$fonts_list;
		}

		$google_fonts = file_get_contents( sprintf( '%s/assets/json/google-fonts.json', TypoLab::$typolab_path ) );
		$fonts_json   = json_decode( $google_fonts );

		if ( ! is_null( $fonts_json ) && isset( $fonts_json->items ) ) {
			self::$fonts_list = $fonts_json->items;
		}

		return self::$fonts_list;
	}

	/**
	 * Generate Font URL.
	 *
	 * @param string $family
	 * @param array  $variants
	 * @param array  $subsets
	 *
	 * @return string
	 */
	public static function font_url( $family, $variants = [ 'regular' ], $subsets = [ 'latin' ] ) {

		// Googe Fonts API URL
		$url = 'https://fonts.googleapis.com/css?';

		// Font Family
		$url .= 'family=';
		$url .= urlencode( $family );

		// Variants
		$url .= ':';
		$url .= implode( ',', array_map( 'urlencode', $variants ) );

		// Subset
		$url .= '&';
		$url .= implode( ',', array_map( 'urlencode', $subsets ) );

		return $url;
	}

	/**
	 * Single Line Font Preview Link.
	 *
	 * @param array $font
	 *
	 * @return string
	 */
	public static function single_line_preview( $font ) {
		$font_data = $font['options']['data'];

		return admin_url( sprintf( 'admin-ajax.php?action=typolab-preview-google-fonts&single-line=true&font-family=%s', rawurlencode( $font_data->family ) ) );
	}

	/**
	 * Get alphabetic order.
	 *
	 * @return array
	 */
	public static function group_fonts_by_first_letter() {
		$alphabetic_order = [];

		foreach ( self::$fonts_list as $font ) {
			$first = strtoupper( substr( $font->family, 0, 1 ) );
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
			$category = $font->category;

			switch ( $category ) {
				case 'display':
					$category = 'Display';
					break;

				case 'handwriting':
					$category = 'Handwriting';
					break;

				case 'monospace':
					$category = 'Monospace';
					break;

				case 'sans-serif':
					$category = 'Sans Serif';
					break;

				case 'serif':
					$category = 'Serif';
					break;
			}

			if ( ! isset( $font_categories[ $font->category ] ) ) {
				$font_categories[ $font->category ] = [
					'name'  => $category,
					'count' => 1,
				];
			} else {
				$font_categories[ $font->category ]['count'] ++;
			}
		}

		uasort( $font_categories, function ( $a, $b ) {
			return strcmp( $a['name'], $b['name'] );
		} );

		return $font_categories;
	}

	/**
	 * Initialize TypoLab Google Fonts Adapter.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_typolab-preview-google-fonts', [ $this, '_preview' ] );
	}

	/**
	 * Preview font.
	 *
	 * @return void
	 */
	public function _preview() {
		$font_family  = kalium()->request->query( 'font-family' );
		$font_to_load = null;

		$fonts_list = self::get_fonts_list();

		foreach ( $fonts_list as $font ) {
			if ( $font_family == $font->family ) {
				$font_to_load = $font;
				break;
			}
		}

		if ( ! $font_family || ! $font_to_load ) {
			return;
		}

		// Font Details
		$family   = $font_to_load->family;
		$variants = $font_to_load->variants;
		$subsets  = $font_to_load->subsets;

		// Font URL
		$font_url = self::font_url( $family, $variants, $subsets );

		// Check if its single line
		$single_line = isset( $_GET['single-line'] );

		if ( $single_line ) {
			$variants = array_splice( $variants, 0, 1 );
		}

		?>
        <html>
        <head>
            <link rel="stylesheet" href="<?php echo TypoLab::$typolab_assets_url . '/css/typolab.min.css'; ?>">
            <link rel="stylesheet" href="<?php echo $font_url; ?>">
            <style>
                .font-entry p {
                    font-family: '<?php echo esc_attr( $family ); ?>', sans-serif;
                    font-size: <?php echo intval( TypoLab::$font_preview_size ); ?>px;
                }
            </style>
        </head>
        <body id="preview-mode">
        <div class="font-preview">
			<?php
			foreach ( $variants as $variant ) :
				$italic = strpos( $variant, 'italic' ) !== false;
				$font_weight = str_replace( 'italic', '', $variant );
				?>
                <div class="font-entry<?php when_match( $single_line, 'single-entry' ); ?>">
                    <p style="font-style: <?php echo $italic ? 'italic' : 'normal'; ?>; font-weight: <?php echo $font_weight; ?>;"><?php echo esc_html( TypoLab::$font_preview_str ); ?></p>
					<?php if ( ! $single_line ) : ?>
                        <span><?php echo trim( str_replace( 'italic', ',italic', $variant ), ',' ); ?></span>
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

new TypoLab_Google_Fonts();