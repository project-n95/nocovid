<?php
/**
 * Kalium WordPress Theme
 *
 * Custom font loader and manager.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Custom_Font {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	public static $provider_id = 'custom-font';

	/**
	 * Single Line Font Preview Link.
	 *
	 * @param array $font
	 *
	 * @return string
	 */
	public static function single_line_preview( $font ) {
		$font_url    = $font['options']['font_url'];
		$font_family = implode( ';', $font['options']['font_variants'] );

		return admin_url( sprintf( 'admin-ajax.php?action=typolab-preview-custom-font&single-line=true&font-url=%s&font-family=%s', rawurlencode( $font_url ), rawurlencode( $font_family ) ) );
	}

	/**
	 * Clear Font Family Name.
	 *
	 * @param string $font_family
	 *
	 * @return string
	 */
	public static function clear_font_family_name( $font_family ) {
		$font_family = str_replace( [ "'", '"' ], '', $font_family );
		$font_family = trim( $font_family );
		$font_family = explode( ',', $font_family );

		return esc_html( reset( $font_family ) );
	}

	/**
	 * Wrap font family names with sinqle quote.
	 *
	 * @param string|array $font_family
	 *
	 * @return string|array
	 */
	public static function wrap_font_family_name( $font_family ) {

		// Generic Font Names
		$generic_font_names = [
			'',
			'Arial',
			'Courier',
			'Garamond',
			'Geneva',
			'Georgia',
			'Helvetica',
			'Monaco',
			'Palatino',
			'Symbol',
			'Tahoma',
			'Verdana',
			'Times',
			'monospace',
			'sans-serif',
			'serif',
			'cursive',
			'fantasy',
		];

		// Wrapp array of font family names
		if ( is_array( $font_family ) ) {
			$font_family_names = [];

			foreach ( $font_family as $font_family_name ) {
				$font_family_wrapped = self::wrap_font_family_name( $font_family_name );

				if ( is_array( $font_family_wrapped ) ) {
					$font_family_names = array_merge( $font_family_names, $font_family_wrapped );
				} else {
					$font_family_names[] = $font_family_wrapped;
				}
			}

			return array_filter( $font_family_names );
		}

		// Split font family names
		if ( strpos( $font_family, ';' ) !== false ) {
			$font_families = explode( ';', $font_family );

			return self::wrap_font_family_name( $font_families );
		}

		// Wrap font family name
		$font_family = str_replace( array( 'font-family:' ), '', $font_family );
		$font_family = array_map( 'trim', explode( ',', str_replace( [ '"', "'" ], '', $font_family ) ) );

		foreach ( $font_family as $i => $font_family_name ) {
			$font_family[ $i ] = strpos( $font_family_name, ' ' ) !== false || ! in_array( $font_family_name, $generic_font_names ) ? "'{$font_family_name}'" : $font_family_name;
		}

		return implode( ', ', array_filter( $font_family ) );
	}

	/**
	 * Initialize TypoLab Custom Font Adapter.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_typolab-preview-custom-font', [ $this, '_preview' ] );
	}

	/**
	 * Preview Font.
	 *
	 * @return void
	 */
	public function _preview() {
		$font_url    = kalium()->request->query( 'font-url' );
		$font_family = wp_unslash( kalium()->request->query( 'font-family' ) );

		if ( ! $font_url || ! $font_family ) {
			return;
		}

		// Adobe Font Project ID
		$adobe_fonts_project_id = kalium()->request->query( 'adobe-project-id' );

		// Typekit ID
		if ( empty( $adobe_fonts_project_id ) && ! empty( $font_url ) && strlen( $font_url ) <= 10 ) {
			$adobe_fonts_project_id = $font_url;
		}

		// Font URL
		$font_url = wp_extract_urls( $font_url );
		$font_url = $font_url ? rtrim( reset( $font_url ), '\\' ) : '';

		// Check for adobe font project id
		if ( ! empty( $adobe_fonts_project_id ) ) {
			$font_url = TypoLab_TypeKit_Fonts::get_stylesheet_url( $adobe_fonts_project_id );
		}

		// Font Family Entries
		$font_family_entries = self::wrap_font_family_name( $font_family );

		if ( ! is_array( $font_family_entries ) ) {
			$font_family_entries = [ $font_family_entries ];
		}

		// Check if its single line
		$single_line = isset( $_GET['single-line'] );

		if ( $single_line ) {
			$font_family_entries = array_splice( $font_family_entries, 0, 1 );
		}

		?>
        <html lang="en">
        <head>
            <link rel="stylesheet" href="<?php echo esc_url( TypoLab::$typolab_assets_url . '/css/typolab.min.css' ); ?>">
            <link rel="stylesheet" href="<?php echo esc_url( $font_url ); ?>">
            <style>
                .font-entry p {
                    font-size: <?php echo intval( TypoLab::$font_preview_size ); ?>px;
                }
            </style>
        </head>
        <body id="preview-mode">
        <div class="font-preview">
			<?php
			foreach ( $font_family_entries as $font_family ) :
				?>
                <div class="font-entry<?php when_match( $single_line, 'single-entry' ); ?>">
                    <p style="font-family: <?php echo esc_attr( self::wrap_font_family_name( $font_family ) ); ?>;"><?php echo esc_html( TypoLab::$font_preview_str ); ?></p>
					<?php if ( ! $single_line ) : ?>
                        <span><?php echo self::clear_font_family_name( $font_family ); ?></span>
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

new TypoLab_Custom_Font();