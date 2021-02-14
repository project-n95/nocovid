<?php
/**
 * Kalium WordPress Theme
 *
 * Product registration success page.
 *
 * @var $theme_name
 * @var $save_backups
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Remove extra top padding from the toolbar
kalium_append_custom_css( 'html.wp-toolbar', 'padding-top: 0px;' );

?>
<div class="about-kalium__register-theme-success">

    <div class="about-kalium__register-theme-success-heading">

        <i class="about-kalium__register-theme-success-icon">
            <svg class="checkmark success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark_circle_success" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark_check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" stroke-linecap="round"/>
            </svg>
        </i>

        <h1 class="about-kalium__register-theme-success-title">Registration complete!</h1>

        <div class="about-kalium__register-theme-success-description">
            Congratulations! <strong><?php echo esc_html( $theme_name ); ?></strong> has been successfully registered
            and now you can get latest updates of the theme.
        </div>

    </div>

    <form method="post" action="" class="about-kalium__register-theme-success-backups">
        <p>Below you can enable or disable creation of theme backups before updating the theme to a newer version:</p>

        <select name="theme_backups" id="theme_backups">
            <option value="1" <?php echo selected( true, $save_backups ); ?>>Enable Backups</option>
            <option value="0" <?php echo selected( false, $save_backups ); ?>>Disable Backups</option>
        </select>
        <button type="submit" class="button" id="theme_backups_save">Save</button>
    </form>

    <a href="#" class="about-kalium__register-theme-success-start-button close-this-window">
        Start using Kalium!
    </a>

    <br>
    <p>You can <a href="#" class="close-this-window">close this window</a> now.</p>
</div>
<script>
	// Resize popup container
	jQuery( document ).ready( function ( $ ) {
		var $startButton = jQuery( '.about-kalium__register-theme-success-start-button' ),
			newWindowHeight = Math.round( $startButton.offset().top + $startButton.outerHeight() );

		// Inner and outer height gap
        newWindowHeight += window.outerHeight - window.innerHeight;

		// Resize window
		window.resizeTo( $( window ).width(), newWindowHeight );
	} );
</script>