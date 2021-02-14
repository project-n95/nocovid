<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Footer texts
$footer_text       = kalium_get_theme_option( 'footer_text' );
$footer_text_right = kalium_get_theme_option( 'footer_text_right' );

?>
<footer id="footer" role="contentinfo" <?php kalium_class_attr( kalium_get_footer_classes() ); ?>>

	<?php
	// Display footer widgets, if enabled
	if ( kalium_get_theme_option( 'footer_widgets' ) ) {
		get_template_part( 'tpls/footer-widgets' );
	}
	?>

	<?php if ( kalium_get_theme_option( 'footer_bottom_visible' ) ) : ?>

        <div class="footer-bottom">

            <div class="container">

                <div class="footer-bottom-content">

					<?php if ( $footer_text_right ) : ?>

                        <div class="footer-content-right">
							<?php echo do_shortcode( wp_kses_post( $footer_text_right ) ); ?>
                        </div>

					<?php endif; ?>

					<?php if ( $footer_text ) : ?>

                        <div class="footer-content-left">

                            <div class="copyrights site-info">
                                <p><?php echo do_shortcode( wp_kses_post( $footer_text ) ); ?></p>
                            </div>

                        </div>

					<?php endif; ?>
                </div>

            </div>

        </div>

	<?php endif; ?>

</footer>