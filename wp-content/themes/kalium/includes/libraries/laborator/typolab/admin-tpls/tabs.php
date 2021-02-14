<?php
/**
 * Kalium WordPress Theme
 *
 * Typolab Tabs.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$page           = kalium()->request->query( 'page' );
$current_page   = kalium()->request->query( 'typolab-page' );
$typolab_active = in_array( $current_page, [ 'settings', 'font-sizes' ] ) ? $current_page : 'main';

$typolab_main_link       = admin_url( "admin.php?page={$page}" );
$typolab_settings_link   = admin_url( "admin.php?page={$page}&typolab-page=settings" );
$typolab_font_sizes_link = admin_url( "admin.php?page={$page}&typolab-page=font-sizes" );

?>
<nav class="nav-tab-wrapper wp-clearfix">
    <a href="<?php echo esc_url( $typolab_main_link ); ?>" class="nav-tab<?php when_match( 'main' === $typolab_active, 'nav-tab-active' ); ?>">Fonts</a>
    <a href="<?php echo esc_url( $typolab_font_sizes_link ); ?>" class="nav-tab<?php when_match( 'font-sizes' === $typolab_active, 'nav-tab-active' ); ?>">Font Sizes</a>
    <a href="<?php echo esc_url( $typolab_settings_link ); ?>" class="nav-tab<?php when_match( 'settings' === $typolab_active, 'nav-tab-active' ); ?>">Settings</a>
</nav>