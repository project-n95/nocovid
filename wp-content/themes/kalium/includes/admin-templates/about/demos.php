<?php
/**
 * Kalium WordPress Theme
 *
 * Demo content install page.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
    <div class="about-kalium__heading">
        <h2>Demos</h2>
        <p>Choose the demo content pack to install on your site. You may install one or more demo content
            packs at the same time. You can remove an imported content pack by simply clicking Uninstall
            button and choose the content types to remove from your site.
        </p>
    </div>

    <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'system-status' ) ); ?>" class="button margin-right">Check
        your System Status</a>
    <a href="https://documentation.laborator.co/kb/kalium/demo-content-import/" target="_blank" rel="noreferrer noopener" class="button button-primary">Read
        more</a>

<?php
/**
 * Demo content packs.
 */
Kalium_Demo_Content_Importer::instance()->list_demo_content_packs();
