<?php
/**
 * Kalium WordPress Theme
 *
 * Demo content pack uninstall dialog.
 *
 * @var Kalium_Demo_Content_Pack $content_pack
 * @var array                    $imported_content_type
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
				<?php echo sprintf( 'Uninstall %1$s Demo', $content_pack->get_name() ); ?>
            </h3>

            <p>
                Selected content type will be removed and options will be replaced with previous values before
                installing <strong><?php echo esc_html( $content_pack->get_name() ); ?></strong> demo content.
            </p>
        </div>

    </div>

    <div class="kalium-demos__content-pack-view-import-body">

        <div class="kalium-demos__content-pack-view-import-body-column kalium-demos__content-pack-view-uninstall-contents">
            <h4>Remove Content Types</h4>
            <p>Choose the content type you want to remove</p>

            <ul class="kalium-demos__content-pack-view-imports">
				<?php
				foreach ( $imported_content_type as $content ) :
					$import_id = $content['id'];
					$import_type = $content['type'];
					$import_name = $content['name'];
					?>
                    <li class="kalium-demos__content-pack-view-import" data-import-type="<?php echo esc_attr( $import_type ); ?>" data-import-id="<?php echo esc_attr( $import_id ); ?>">
                        <div class="kalium-demos__content-pack-view-imports-checkbox">
                            <input type="checkbox" name="imports[<?php echo esc_attr( $import_id ); ?>][value]" class="import-field" id="import_<?php echo esc_html( $import_id ); ?>" value="<?php echo esc_attr( $import_id ); ?>">
                        </div>
                        <label class="kalium-demos__content-pack-view-imports-label" for="import_<?php echo esc_html( $import_id ); ?>">
							<?php echo esc_html( $content['name'] ); ?>
                        </label>
                    </li>
				<?php
				endforeach;
				?>
            </ul>
        </div>

    </div>

    <div class="kalium-demos__content-pack-view-footer">

        <div class="kalium-demos__content-pack-view-footer-buttons">
            <button type="button" class="button button-primary" id="start_uninstall" disabled>Uninstall</button>
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

            <h3 class="kalium-demos__content-pack-finish-view-heading-title">Uninstall Complete</h3>

            <p class="kalium-demos__content-pack-finish-view-heading-description">
                Selected content types from <strong>{{contentPackName}}</strong> are removed successfully.
            </p>
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
                <strong>{{contentPackName}}</strong> demo content is partly removed because of these errors:
            </p>
        </div>

        <ul class="kalium-demos__content-pack-finish-view-errors hidden">
        </ul>
    </div>
</script>