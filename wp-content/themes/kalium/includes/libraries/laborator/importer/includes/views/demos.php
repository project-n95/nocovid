<?php
/**
 * Kalium WordPress Theme
 *
 * Demo content page.
 *
 * @var bool                       $is_theme_registered
 * @var Kalium_Demo_Content_Pack[] $content_packs
 * @var Kalium_Demo_Content_Pack[] $installed_content_packs
 *
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Theme register notice
if ( ! $is_theme_registered ) : ?>
    <div class="kalium-demos__content-warning">
        <strong>Note:</strong>
        To import any of the demo content packs below you must
        <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'theme-registration' ) ); ?>">register the theme
            &raquo;</a>
    </div>
<?php endif; ?>

<?php
/**
 * List installed demo content packs.
 */
if ( ! empty ( $installed_content_packs ) ) :
	?>
    <ul class="kalium-demos row">
		<?php
		foreach ( $installed_content_packs as $content_pack ) :
			$import_instance = $content_pack->get_import_instance();
			$imported_content = wp_list_pluck( $import_instance->get_imported_content_type(), 'name' );

			$classes = [
				'col',
				'col-12',
			];
			?>
            <li <?php kalium_class_attr( $classes ); ?>>

                <div class="kalium-demos__installed-content-pack-entry" data-content-pack-id="<?php echo esc_attr( $content_pack->get_id() ); ?>">

                    <div class="kalium-demos__installed-content-pack-entry-thumbnail">
                        <div class="kalium-demos__installed-content-pack-entry-thumbnail-container">
                            <img src="<?php echo esc_url( $content_pack->get_thumbnail_url() ); ?>" alt="demo-content-thumb"/>
                        </div>
                    </div>

                    <div class="kalium-demos__installed-content-pack-entry-details">
                        <h3>Installed Demo: <?php echo esc_html( $content_pack->get_name() ); ?></h3>
                        <p>
                            <strong>Imported content:</strong>
							<?php echo esc_html( implode( ', ', $imported_content ) ); ?>
                        </p>

                        <div class="kalium-demos__installed-content-pack-entry-buttons">
                            <a href="<?php echo esc_url( home_url() ); ?>" target="_blank" rel="noopener" class="button">Preview</a>
                            <a href="#<?php echo $content_pack->get_id(); ?>" data-link="<?php echo esc_url( $content_pack->get_link() ); ?>" class="button button-uninstall kalium-demos__content-pack-entry-link">Uninstall</a>
                        </div>
                    </div>

                </div>

            </li>
		<?php
		endforeach;
		?>
    </ul>
<?php

endif;
?>

<ul class="kalium-demos row">
	<?php
	/**
	 * List demo content packs.
	 *
	 * @var Kalium_Demo_Content_Pack $content_pack
	 */
	foreach ( $content_packs as $content_pack ) :
		$import_title = 'Import';
		$import_button_classes = [
			'button',
            'button-primary',
			'kalium-demos__content-pack-entry-link',
		];

		if ( $content_pack->is_installed() ) {
			$import_title            = 'Uninstall';
			$import_button_classes[] = 'button-uninstall';
		}
		?>
        <li class="col col-3 col-lg-4 col-md-6 col-xs-12">
            <div class="kalium-demos__content-pack-entry" data-content-pack-id="<?php echo esc_attr( $content_pack->get_id() ); ?>">
                <a href="#<?php echo $content_pack->get_id(); ?>" data-link="<?php echo esc_url( $content_pack->get_link() ); ?>" class="kalium-demos__content-pack-entry-link kalium-demos__content-pack-entry-image">
                    <img src="<?php echo esc_url( $content_pack->get_thumbnail_url() ); ?>" alt="demo-content-thumb"/>
                </a>

                <div class="kalium-demos__content-pack-entry-details">
                    <h3><?php echo esc_html( $content_pack->get_name() ); ?></h3>

                    <div class="kalium-demos__content-pack-entry-details-buttons wp-clearfix">
                        <a href="#<?php echo $content_pack->get_id(); ?>" data-link="<?php echo esc_url( $content_pack->get_link() ); ?>" title="Demo Content Pack &raquo; <?php echo esc_attr( $content_pack->get_name() ); ?>" <?php kalium_class_attr( $import_button_classes ); ?>><?php echo esc_html( $import_title ); ?></a>
                        <a href="<?php echo esc_url( $content_pack->get_preview_url() ); ?>" target="_blank" class="button button-secondary" title="Preview this demo">Preview</a>
                    </div>
                </div>
            </div>
        </li>
	<?php

	endforeach;
	?>
</ul>
