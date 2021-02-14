<?php
/**
 * Kalium WordPress Theme
 *
 * Demo content pack import dialog.
 *
 * @var Kalium_Demo_Content_Pack $content_pack
 *
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="kalium-demos__content-pack-view" data-name="<?php echo esc_attr( $content_pack->get_name() ); ?>">

    <div class="kalium-demos__content-pack-view-heading">

        <div class="kalium-demos__content-pack-view-heading-thumbnail">
            <img src="<?php echo esc_url( $content_pack->get_thumbnail_url() ); ?>" width="570" height="740" alt="<?php echo esc_attr( $content_pack->get_id() ); ?>-thumbnail"/>
        </div>

        <div class="kalium-demos__content-pack-view-heading-description">
            <h3 class="kalium-demos__content-pack-view-heading-title">
				<?php echo sprintf( 'Install %1$s Demo', $content_pack->get_name() ); ?>
            </h3>

            <p>
                Select what type of content you want to import:
            </p>
        </div>

    </div>

    <div class="kalium-demos__content-pack-view-import-body">

        <div class="kalium-demos__content-pack-view-import-body-column kalium-demos__content-pack-view-import-required-plugins">
            <h4>Required Plugins</h4>
            <p>Plugins that will be installed automatically</p>

            <ul class="kalium-demos__content-pack-view-required-plugins">
				<?php
				foreach ( $content_pack->get_required_plugins() as $plugin_slug => $plugin_info ) :
					$plugin_status = 'install';
					$plugin_status_title = 'Install';

					// Plugin is active
					if ( kalium()->is->plugin_active( $plugin_info['basename'] ) ) {
						$plugin_status       = 'active';
						$plugin_status_title = 'Active';
					} // Plugin is installed
                    elseif ( kalium()->is->plugin_installed( $plugin_info['basename'] ) ) {
						$plugin_status       = 'activate';
						$plugin_status_title = 'Activate';
					}
					?>
                    <li data-plugin-slug="<?php echo esc_attr( $plugin_slug ); ?>">
                        <div class="kalium-demos__content-pack-view-required-plugins-title"><?php echo esc_html( $plugin_info['name'] ); ?></div>
                        <div class="kalium-demos__content-pack-view-required-plugins-status">
							<?php echo sprintf( '<span class="%2$s">%1$s</span>', esc_html( $plugin_status_title ), $plugin_status ); ?>
                        </div>
                    </li>
				<?php
				endforeach;
				?>
            </ul>
        </div>

        <div class="kalium-demos__content-pack-view-import-body-column kalium-demos__content-pack-view-import-contents">
            <h4>Content Import</h4>
            <p>Choose the content type you want to import</p>

            <ul class="kalium-demos__content-pack-view-imports">
				<?php
				foreach ( $content_pack->get_imports() as $import ) :

					// When content type is already imported
					if ( $import->is_imported() ) {
						$import->is_disabled( true );
						$import->is_checked( true );
					}

					?>
                    <li class="kalium-demos__content-pack-view-import" data-import-type="<?php echo esc_attr( $import->get_type() ); ?>" data-import-id="<?php echo esc_attr( $import->get_import_id() ); ?>">
						<?php
						// Render content type import checkbox
						$import->render_import_field();

						// Render content type import args fields
						$import->render_import_args_fields();
						?>
                    </li>
				<?php
				endforeach;
				?>
            </ul>
        </div>

    </div>

    <div class="kalium-demos__content-pack-view-footer">

        <div class="kalium-demos__content-pack-view-footer-buttons">
            <button type="button" class="button button-primary" id="start_import">Import</button>
        </div>

        <div class="kalium-demos__content-pack-view-footer-status">

            <div class="kalium-demos__content-pack-view-progress">
                <div class="kalium-demos__content-pack-view-progress-status">
                    <div class="kalium-demos__content-pack-view-progress-status-text">
                        &nbsp;
                    </div>
                    <div class="kalium-demos__content-pack-view-progress-status-percentage">
                    </div>
                </div>
                <div class="kalium-demos__content-pack-view-progress-bar">
                    <div class="kalium-demos__content-pack-view-progress-bar-fill">
                        <div class="kalium-demos__content-pack-view-progress-bar-fill-stripes"></div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script class="content-import-success-template" type="text/template">
    <div class="kalium-demos__content-pack-finish-view" data-import-status="success">
        <div class="kalium-demos__content-pack-finish-view-heading">
            <div class="kalium-demos__content-pack-finish-view-heading-icon success">
                <svg class="checkmark success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark_circle_success" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark_check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" stroke-linecap="round"/>
                </svg>
            </div>

            <h3 class="kalium-demos__content-pack-finish-view-heading-title">Hooray!</h3>
            <p class="kalium-demos__content-pack-finish-view-heading-description">
                <strong>{{contentPackName}}</strong> demo content imported successfully.
            </p>

            <a href="<?php echo esc_url( home_url() ); ?>" target="_blank" rel="noopener" class="kalium-demos__content-pack-finish-view-heading-link">
                View your site now &raquo;
            </a>
        </div>

        <ul class="kalium-demos__content-pack-finish-view-errors hidden">
        </ul>
    </div>
</script>

<script class="content-import-failed-template" type="text/template">
    <div class="kalium-demos__content-pack-finish-view" data-import-status="failed">
        <div class="kalium-demos__content-pack-finish-view-heading">
            <div class="kalium-demos__content-pack-finish-view-heading-icon failed">
                <svg class="checkmark error" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark_circle_error" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark_check" stroke-linecap="round" fill="none" d="M16 16 36 36 M36 16 16 36"/>
                </svg>
            </div>

            <h3 class="kalium-demos__content-pack-finish-view-heading-title">Oops!</h3>
            <p class="kalium-demos__content-pack-finish-view-heading-description">
                <strong>{{contentPackName}}</strong> demo content was partly imported because of these errors:
            </p>
        </div>

        <ul class="kalium-demos__content-pack-finish-view-errors hidden">
        </ul>

        <div class="kalium-demos__content-pack-finish-view-footer-content">
            It is often related to
            <a href="https://documentation.laborator.co/kb/kalium/bad-hosting-environments/" rel="noreferrer noopener" target="_blank">bad
                hosting environment</a> that have limited hosting resources and make demo installation to fail. Please
            review if your server meets
            <a href="https://documentation.laborator.co/kb/kalium/kalium-server-requirements/" rel="noreferrer noopener" target="_blank">suggested
                requirements</a> otherwise contact our support.
        </div>
    </div>
</script>