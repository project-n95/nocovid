<?php
/**
 * Kalium WordPress Theme
 *
 * Logo element template.
 *
 * @var array   $classes
 * @var string  $link
 * @var string  $logo_name
 * @var array   $logo_image {
 * @type string $src
 * @type int    $width
 * @type int    $height
 * }
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Hook: kalium_before_logo.
 */
do_action( 'kalium_before_logo' );

?>
    <a href="<?php echo esc_url( $link ); ?>" <?php kalium_class_attr( $classes ) ?>>
		<?php if ( ! empty( $logo_image ) ) : ?>
            <img src="<?php echo esc_url( $logo_image['src'] ); ?>" class="main-logo" width="<?php echo esc_attr( $logo_image['width'] ); ?>" height="<?php echo esc_attr( $logo_image['width'] ); ?>" alt="<?php echo esc_attr( $logo_name ); ?>"/>
		<?php else: ?>
            <span class="logo-text"><?php echo esc_html( $logo_name ); ?></span>
		<?php endif; ?>
    </a>
<?php

/**
 * Hook: kalium_after_logo.
 */
do_action( 'kalium_after_logo' );
