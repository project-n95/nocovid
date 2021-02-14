<?php
/**
 * Kalium WordPress Theme
 *
 * Typekit loader and manager.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_TypeKit_Fonts {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	public static $provider_id = 'typekit-fonts';

	/**
	 * Loaded Typekit Fonts.
	 *
	 * @var array
	 */
	public static $fonts_list = [];

	/**
	 * Get Fonts List.
	 *
	 * @return array
	 */
	public static function get_fonts_list() {

		// Once initialized, no need to load fonts list again
		if ( self::$fonts_list ) {
			return self::$fonts_list;
		}

		return self::$fonts_list;
	}

	/**
	 * Single Line Font Preview Link.
	 *
	 * @param array $font
	 *
	 * @return string
	 */
	public static function single_line_preview( $font ) {
		if ( ! isset( $font['kit_id'] ) ) {
			return '';
		}

		return admin_url( sprintf( 'admin-ajax.php?action=typolab-preview-typekit-font&single-line=true&kit-id=%s', rawurlencode( $font['kit_id'] ) ) );
	}

	/**
	 * Embedd Kit in JavaScript.
	 *
	 * @param string $kit_id
	 *
	 * @return void
	 */
	public static function embed_kit_js( $kit_id ) {
		$opts = [
			'async' => true,
		];
		?>
        <script src="https://use.typekit.net/<?php echo esc_attr( $kit_id ); ?>.js"></script>
        <script>try {
				Typekit.load(<?php echo wp_json_encode( apply_filters( 'typolab_typekit_embed_load_args', $opts ) ); ?>);
			} catch ( e ) {
			}</script>
		<?php
	}

	/**
	 * Get stylesheet URL based on project ID.
	 *
	 * @param string $project_id
	 *
	 * @return string
	 */
	public static function get_stylesheet_url( $project_id ) {
		$href = $project_id;

		if ( strlen( $project_id ) <= 10 ) {
			$href = sprintf( 'https://use.typekit.net/%s.css', $project_id );
		}

		return esc_url( $href );
	}

	/**
	 * Initialize Premium Fonts Adapter.
	 *
	 * @return void
	 */
	public function __construct() {

		// TypeKit Library
		require_once TypoLab::$typolab_path . '/inc/typekit-client.php';

		// Preview font
		add_action( 'wp_ajax_typolab-preview-typekit-font', [ $this, '_preview' ] );
	}

	/**
	 * Preview font.
	 *
	 * @return void
	 */
	public function _preview() {
		$kit_id = kalium()->request->query( 'kit-id' );

		if ( ! $kit_id ) {
			return;
		}

		$typekit  = new Typekit();
		$kit_info = $typekit->get( $kit_id );

		// Check if its single line
		$single_line = isset( $_GET['single-line'] );

		if ( $single_line ) {
			$kit_info['kit']['families'] = array_splice( $kit_info['kit']['families'], 0, 1 );
		}

		?>
        <html>
        <head>
            <link rel="stylesheet" href="<?php echo TypoLab::$typolab_assets_url . '/css/typolab.min.css'; ?>">
			<?php self::embed_kit_js( $kit_id ); ?>
        </head>
        <body id="preview-mode" style="visibility: hidden;">
		<?php if ( $kit_info ) : ?>
            <div class="font-preview">
				<?php
				foreach ( $kit_info['kit']['families'] as $font ) :
					$css_stack = $font['css_stack'];
					?>
                    <div class="font-entry<?php when_match( $single_line, 'single-entry' ); ?>">
                        <p style="font-family: <?php echo esc_attr( $css_stack ); ?>;"><?php echo esc_html( TypoLab::$font_preview_str ); ?></p>
						<?php if ( ! $single_line ) : ?>
                            <span><?php echo $font['name']; ?></span>
						<?php endif; ?>
                    </div>
				<?php
				endforeach;
				?>
            </div>
		<?php else : ?>
            <p style="padding: 20px 15px;font-family: Helvetica, sans-serif;">Kit ID
                <strong><?php echo $kit_id; ?></strong> doesn't exists!</p>
		<?php endif; ?>

        <script>
			window.onload = function () {
				document.body.style.visibility = 'visible';
				window.kitInfo = <?php echo wp_json_encode( $kit_info ); ?>;
			}
        </script>
        </body>
        </html>
		<?php

		die();
	}
}

new TypoLab_TypeKit_Fonts();