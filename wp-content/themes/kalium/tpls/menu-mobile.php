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

$nav_id = 'main-menu';

if ( has_nav_menu( 'mobile-menu' ) ) {
	$nav_id = 'mobile-menu';
}

$mobile_menu_class = [ 'mobile-menu-wrapper' ];

// Menu Type
$is_fullscreen_type = '0' === kalium_get_theme_option( 'menu_mobile_type' );

if ( $is_fullscreen_type ) {
	$mobile_menu_class[] = 'mobile-menu-fullscreen';
} else {
	$mobile_menu_class[] = 'mobile-menu-slide';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $mobile_menu_class ) ); ?>">

    <div class="mobile-menu-container">

		<?php
		// Hooks before mobile menu
		do_action( 'kalium_mobile_menu_before' );

		// Mobile menu
		wp_nav_menu( array(
			'theme_location' => $nav_id,
			'container'      => '',
			'menu_class'     => 'menu',
		) );

		// Mobile cart icon
		if ( kalium()->is->woocommerce_active() && kalium_get_theme_option( 'shop_cart_icon_menu' ) ) {
			kalium_woocommerce_cart_menu_icon_mobile();
		}

		// WPML Switcher
		if ( kalium_get_theme_option( 'header_wpml_language_switcher' ) ) {
			kalium_wpml_language_switcher();
		}
		?>

		<?php if ( kalium_get_theme_option( 'menu_mobile_search_field', true ) ) : ?>
            <form role="search" method="get" class="search-form" action="<?php echo esc_url( kalium_search_url() ); ?>">
                <input type="search" class="search-field" placeholder="<?php echo esc_attr__( 'Search site...', 'kalium' ); ?>" value="<?php echo get_search_query(); ?>" name="s" id="search_mobile_inp"/>

                <label for="search_mobile_inp">
                    <i class="fa fa-search"></i>
                </label>

                <input type="submit" class="search-submit" value="<?php echo esc_attr__( 'Go', 'kalium' ); ?>"/>
            </form>
		<?php endif; ?>

		<?php if ( $is_fullscreen_type ) : ?>
            <a href="#" class="mobile-menu-close-link toggle-bars exit menu-skin-light">
				<?php kalium_menu_icon_or_label(); ?>
            </a>
		<?php endif; ?>

		<?php
		// Hooks after mobile menu
		do_action( 'kalium_mobile_menu_after' );
		?>

    </div>

</div>

<div class="mobile-menu-overlay"></div>