<?php
/**
 * Kalium WordPress Theme
 *
 * Plugins install page.
 *
 * @var bool $is_theme_registered
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
    <div class="row align-items-end">
        <div class="col col-7 col-md-12">
            <div class="about-kalium__heading no-top-margin">
                <h2>Plugins</h2>
                <p>All premium plugins bundled with Kalium include free updates but require theme registration. You can
                    install, update and activate plugins from this page.</p>
            </div>
        </div>
        <div class="col col-5 c text-align-right">
            <div class="about-kalium__plugins-filter button-group">
                <a href="#all" data-filter="all" class="button button-primary">All</a>
                <a href="#required" data-filter="required" class="button">Required</a>
                <a href="#premium" data-filter="premium" class="button">Premium</a>
                <a href="#recommended" data-filter="recommended" class="button">Recommended</a>
                <a href="#optional" data-filter="optional" class="button">Optional</a>
            </div>
        </div>
    </div>

<?php
// Theme register notice
if ( ! $is_theme_registered ) : ?>
    <div class="about-kalium__plugins-register-notice">
        <strong>Note:</strong>
        To install any of the premium plugins you must
        <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'theme-registration' ) ); ?>">register the theme &raquo;</a>
    </div>
<?php
endif;

/**
 * List theme plugins.
 */
kalium()->theme_plugins->list_theme_plugins();
