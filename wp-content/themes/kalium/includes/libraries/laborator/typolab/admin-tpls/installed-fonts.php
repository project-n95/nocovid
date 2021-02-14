<?php
/**
 * Kalium WordPress Theme
 *
 * Installed Fonts List.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$total_fonts     = 0;
$fonts           = TypoLab::get_fonts( true );
$page            = kalium()->request->query( 'page' );
$next_sort_order = kalium_conditional( 'ASC' === strtoupper( kalium()->request->query( 'sort-by-name' ) ), 'desc', 'asc' );
?>
<form method="post" class="typolab-fonts-list">

    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1">Bulk Actions</option>
                <option value="delete">Delete</option>
            </select>
            <input type="submit" id="doaction" class="button action" value="Apply">
        </div>
        <br class="clear">
    </div>

    <table class="widefat">
        <thead>
        <td id="cb" class="manage-column column-cb check-column">
            <input id="cb-select-all" type="checkbox">
        </td>
        <th class="font-name-col manage-column column-date sortable <?php echo esc_html( $next_sort_order ); ?>">
            <a href="<?php echo add_query_arg( 'sort-by-name', $next_sort_order ); ?>">
                <span>Name</span>
                <span class="sorting-indicator"></span>
            </a>
        </th>
        <th class="font-preview-col">Font Preview</th>
        <th class="font-source-col">Source</th>
        <th class="font-status-col">Status</th>
        </thead>

        <tbody>
		<?php
		if ( count( $fonts ) ) :

			foreach ( $fonts as $font ) :

				if ( ! empty( $font['valid'] ) ) :

					$font_id = $font['id'];
					$font_family = kalium_get_array_key( $font, 'family', '(No font was specified)' );
					$font_source = TypoLab::$font_sources[ $font['source'] ];
					$font_status = 'published' == $font['font_status'] ? '<span class="published">Published</span>' : '<span class="unpublished">Unpublished</span>';

					$edit_link   = admin_url( sprintf( 'admin.php?page=%s&typolab-action=edit-font&font-id=%s', $page, $font_id ) );
					$delete_link = admin_url( sprintf( 'admin.php?page=%s&typolab-action=delete-font&font-id=%s', $page, $font_id ) );

					$missing_font = isset( TypoLab::$missing_fonts[ $font_id ] );

					$preview_url = $preview_box = '';

					switch ( $font['source'] ) {

						// Google Fonts Preview Link
						case 'google':
							$preview_url = TypoLab_Google_Fonts::single_line_preview( $font );
							break;

						// Font Squirrel Preview Link
						case 'font-squirrel':
							$preview_url = TypoLab_Font_Squirrel::single_line_preview( $font );
							break;

						// Premium Font Preview Link
						case 'premium':
							$preview_url = TypoLab_Premium_Fonts::single_line_preview( $font );
							break;

						// TypeKit Preview Link
						case 'typekit':
							if ( TypoLab::is_adobe_font( $font ) ) {
								$preview_url = TypoLab_Custom_Font::single_line_preview( $font );
							} else {
								$preview_url = TypoLab_TypeKit_Fonts::single_line_preview( $font );
							}
							break;

						// Uploaded Font Preview
						case 'uploaded-font':
							$preview_box = TypoLab_Uploaded_Font::single_line_preview( $font );
							break;

						// Custom Font Preview Link
						case 'custom-font':
							$preview_url = TypoLab_Custom_Font::single_line_preview( $font );
							break;
					}

					// Provider Image
					$provider_image = TypoLab::$typolab_assets_url . "/img/{$font['source']}.png";

					if ( 'typekit' == $font['source'] && false == TypoLab::is_adobe_font( $font ) ) {
						$provider_image = TypoLab::$typolab_assets_url . "/img/typekit-legacy.png";
					}

					// Install Font Link
					if ( $missing_font ) {
						$edit_link .= '#premium-font-downloader';
					}
					?>
                    <tr<?php when_match( $missing_font, 'class="not-installed"' ); ?>>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="checked[]" value="<?php echo esc_attr( $font_id ); ?>"/>
                        </th>
                        <td class="font-name-col">
							<?php if ( $missing_font ) : ?>
                                <span class="font-warning tooltip" title="This font is not installed in your site, click Install to proceed with font installation.">
								<i class="dashicons dashicons-warning"></i>
							</span>
							<?php endif; ?>
                            <a href="<?php echo esc_url( $edit_link ); ?>" class="font-family-name"><?php echo $font_family; ?></a>

                            <div class="typolab-actions">
                                <a href="<?php echo esc_url( $edit_link ); ?>" class="edit"><?php echo $missing_font ? 'Install' : 'Edit'; ?></a>
                                |
                                <a href="<?php echo esc_url( $delete_link ); ?>" class="trash">Delete</a>
                            </div>
                        </td>
                        <td class="font-preview-col">
                            <div class="font-preview-iframe">
								<?php if ( $preview_url && ! $missing_font ) : ?>
                                    <div class="is-loading">Loading font preview&hellip;</div>
                                    <iframe src="<?php echo esc_url( $preview_url ); ?>"></iframe>
								<?php elseif ( ! empty( $preview_box ) ) : ?>
									<?php echo $preview_box; ?>
								<?php else : ?>
                                    <div class="is-loading">No preview available</div>
								<?php endif; ?>
                            </div>
                        </td>
                        <td class="font-source-col">
                            <img src="<?php echo esc_url( $provider_image ); ?>" class="provider-logo-img">
                        </td>
                        <td class="font-status-col">
							<?php echo $font_status; ?>
                        </td>
                    </tr>
				<?php

				endif;

				$total_fonts ++;
			endforeach;
			?>
		<?php else: ?>
            <tr>
                <td colspan="5" class="no-records">
                    There are no installed fonts in your site.
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=typolab&typolab-action=add-font' ) ); ?>" class="button button-primary">Add
                        Font</a>
                </td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>

	<?php if ( $total_fonts > 0 ) : ?>
        <p class="installed-fonts-count">Total fonts installed: <?php echo esc_html( $total_fonts ); ?></p>
	<?php endif; ?>
</form>