<?php
/**
 * Kalium WordPress Theme
 *
 * What's new page.
 *
 * @var string $version
 * @var array $changelog
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
    <div class="row">
        <div class="col col-xs-12 col-auto">
            <div class="about-kalium__version-num">
				<?php echo esc_html( $version ); ?>
            </div>
        </div>
        <div class="col">
            <div class="about-kalium__heading no-top-margin">
                <h2>What&rsquo;s new in Kalium</h2>
                <p>
                    Kalium continuously expands with new features, bug fixes and other adjustments to provide a smoother experience for everyone.
                    Scroll down to see what&rsquo;s new in this version. For a complete list of changes <a href="#changelog">read the full changelog</a>.</p>
            </div>
        </div>
    </div>

    <div class="about-kalium__whats-new row">
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/9-woocommerce48.jpg' ); ?>" width="330" height="200" alt="woocommerce-compatibility">
                </a>

                <h4 class="about-kalium__whats-new-item-title">WooCommerce 4.8 Compatibility</h4>

                <p class="about-kalium__whats-new-item-description">
                    Better shopping experience with the new update from WooCommerce which is compatible with this version of Kalium.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/8-wordpress56.jpg' ); ?>" width="330" height="200" alt="wordpress-compatibility">
                </a>

                <h4 class="about-kalium__whats-new-item-title">WordPress 5.6 Compatibility</h4>

                <p class="about-kalium__whats-new-item-description">
                    In WordPress 5.6, your site gets new power in three major areas: speed, search, and security.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/6-top-menu.jpg' ); ?>" width="330" height="200" alt="lighter-theme">
                </a>

                <h4 class="about-kalium__whats-new-item-title">New Widgets in Top Header Bar</h4>

                <p class="about-kalium__whats-new-item-description">
                    New widgets are now available in Top Header Bar: Breadcrumb, Date, My Account and Search Field.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/1-create-your-own.gif' ); ?>" width="330" height="200" alt="header-builder">
                </a>

                <h4 class="about-kalium__whats-new-item-title">Header Builder</h4>

                <p class="about-kalium__whats-new-item-description">
                    Create the header on your own, use pre-made templates or simply drag and drop the elements you want to show in the site header.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/2-top-header.jpg' ); ?>" width="330" height="200" alt="top-header-bar">
                </a>

                <h4 class="about-kalium__whats-new-item-title">Top Header Bar</h4>

                <p class="about-kalium__whats-new-item-description">
                    Your site information can be displayed now on top (before header) with 5 different widgets and
                    custom styling.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/3-elementor.jpg' ); ?>" width="330" height="200" alt="portfolio-in-elementor">
                </a>

                <h4 class="about-kalium__whats-new-item-title">Portfolio in Elementor</h4>

                <p class="about-kalium__whats-new-item-description">
                    You can add Portfolio Items element from Elementor with a number of options to customize for a unique look.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="<?php echo Kalium_About::get_tab_link( 'demos' ); ?>" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/4-demo-importer.jpg' ); ?>" width="330" height="200" alt="demo-content-importer">
                </a>

                <h4 class="about-kalium__whats-new-item-title">New Demo Content Importer</h4>

                <p class="about-kalium__whats-new-item-description">
                    More accurate content import and fewer actions required by the user, a simple click will do all the work. The great news is that uninstall option is possible too.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="<?php echo Kalium_About::get_tab_link( 'plugins' ); ?>" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/5-plugin-management.jpg' ); ?>" width="330" height="200" alt="plugin-manager">
                </a>

                <h4 class="about-kalium__whats-new-item-title">Plugin Manager</h4>

                <p class="about-kalium__whats-new-item-description">
                    A single place to install, activate and manage theme plugins including recommended free plugins as
                    well.
                </p>
            </div>

        </div>
        <div class="col col-4 col-md-6 col-xs-12">

            <div class="about-kalium__whats-new-item">
                <a href="#" target="_blank" rel="noreferrer noopener" class="about-kalium__whats-new-item-link disabled">
                    <img src="<?php echo kalium()->assets_url( 'admin/images/whats-new/7-bug-fixes.jpg' ); ?>" width="330" height="200" alt="bug-fixes">
                </a>

                <h4 class="about-kalium__whats-new-item-title">Bug Fixes</h4>

                <p class="about-kalium__whats-new-item-description">
                    Numerous bugs which have been reported by our users have been fixed by our team, we cannot count
                    them.
                </p>
            </div>

        </div>
    </div>

<?php
// Show changelog
if ( ! empty( $changelog ) ) :

	// Changelog date format
	$date_format = 'F d, Y';
	?>
    <a id="changelog"></a>

	<?php
	foreach ( $changelog as $changelog_entry ) :
		if ( ! kalium_get_array_key( $changelog_entry, 'expand' ) ) {
			continue;
		}
		?>
        <div class="about-kalium__changelog">
            <h3 class="about-kalium__changelog-title"><?php echo sprintf( 'Changelog &ndash; Version %s (%s)', esc_html( $changelog_entry['version'] ), esc_html( date_i18n( $date_format, strtotime( $changelog_entry['date'] ) ) ) ); ?></h3>

			<?php
			// Change type
			foreach ( $changelog_entry['changes'] as $type => $changes ) {

				if ( empty( $changes ) ) {
					continue;
				}

				?>
                <div class="about-kalium__changelog-type">
                    <div class="about-kalium__changelog-type-title about-kalium__changelog-type-title-<?php echo sanitize_title( $type ); ?>"><?php echo esc_html( $type ); ?></div>
                    <ul>
						<?php foreach ( $changes as $title ) : ?>
                            <li><?php echo links_add_target( make_clickable( esc_html( $title ) ) ); ?></li>
						<?php endforeach; ?>
                    </ul>
                </div>
				<?php
			}
			?>
        </div>
	<?php endforeach; ?>

	<?php if ( ! empty( $changelog ) ) : ?>
    <div class="about-kalium__changelog-previous-title row align-items-center">
        <div class="col">
            <h3 class="no-top-margin">Previous versions</h3>
        </div>
        <div class="col text-align-right">
            <a href="https://documentation.laborator.co/kb/kalium/kalium-changelog/" target="_blank" rel="noreferrer noopener">View all changelogs</a>
        </div>
    </div>

    <ul class="about-kalium__changelog-previous">
		<?php
		foreach ( $changelog as $changelog_entry ) :
			if ( kalium_get_array_key( $changelog_entry, 'expand' ) ) {
				continue;
			}
			?>
            <li class="about-kalium__changelog-previous-entry">
                <div class="about-kalium__changelog-previous-entry-date"><?php echo esc_html( date_i18n( $date_format, strtotime( $changelog_entry['date'] ) ) ); ?></div>
                <div class="about-kalium__changelog-previous-entry-version"><?php echo esc_html( $changelog_entry['version'] ); ?></div>
                <div class="about-kalium__changelog-previous-entry-link">
                    <a href="https://documentation.laborator.co/kb/kalium/kalium-changelog/#version-<?php echo esc_attr( $changelog_entry['version'] ); ?>" target="_blank" rel="noreferrer noopener">
                        Read changelog
                    </a>
                </div>
            </li>
		<?php
		endforeach;
		?>
    </ul>

<?php endif; ?>

<?php
endif;
?>