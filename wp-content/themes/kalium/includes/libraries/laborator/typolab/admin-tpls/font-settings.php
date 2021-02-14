<?php
/**
 * Settings Input
 *
 * Laborator.co
 * www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Font Settings
$font_settings = TypoLab::get_setting( 'font_settings', [] );

// Create fonts directory if not exists
if ( false === file_exists( TypoLab::$fonts_path ) ) {
	wp_mkdir_p( TypoLab::$fonts_path );
}

// Fonts direcotry and permissions
$fonts_directory          = str_replace( ABSPATH, '~/', TypoLab::$fonts_path );
$fonts_directory_writable = file_exists( TypoLab::$fonts_path ) && true === is_writable( TypoLab::$fonts_path );

// Font Downloads
$premium_fonts_downloads = TypoLab_Premium_Fonts::get_downloaded_fonts();
$font_squirrel_downloads = TypoLab_Font_Squirrel::get_downloaded_fonts();
$downloaded_fonts        = [];

foreach ( $premium_fonts_downloads as $font_id => $font_info ) {
	$downloaded_fonts[] = array_merge( [
		'id'     => $font_id,
		'source' => 'premium-fonts',
	], $font_info );
}

foreach ( $font_squirrel_downloads as $font_id => $font_info ) {
	$downloaded_fonts[] = array_merge( [
		'id'     => $font_id,
		'source' => 'font-squirrel',
	], $font_info );
}

?>
<form id="typolab-settings-form" method="post" enctype="application/x-www-form-urlencoded">

    <h3 class="typolab-h3">General Settings</h3>

    <table class="typolab-table mutual-column-width">
        <thead>
        <tr>
            <th>
                <label for="font_preview_text">Font Preview Text</label>
            </th>
            <th>
                <label for="font_preview_size">Font Preview Size</label>
            </th>
            <th>
                <label for="font_placement">Font Import Placement</label>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <div class="grouped-input">
                    <div class="grouped-input-col input">
                        <input type="text" name="font_preview_text" id="font_preview_text" required="required" value="<?php echo esc_attr( TypoLab::$font_preview_str ); ?>">
                    </div>
                </div>
            </td>
            <td>
                <div class="grouped-input">
                    <div class="grouped-input-col input">
                        <input type="number" name="font_preview_size" id="font_preview_size" value="<?php echo esc_attr( TypoLab::$font_preview_size ); ?>">
                    </div>
                </div>
            </td>
            <td>
                <div class="grouped-input no-border">
                    <div class="grouped-input-col select">
                        <select name="font_placement" id="font_placement">
                            <option value="head"<?php selected( 'head', TypoLab::$font_placement ); ?>>Inside &lt;head&gt;
                                tag
                            </option>
                            <option value="body"<?php selected( 'body', TypoLab::$font_placement ); ?>>Before &lt;/body&gt;
                                tag
                            </option>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="hover vtop">
            <td>
                <div class="description">Preview text to display font variants.</div>
            </td>
            <td>
                <div class="description">Enter font size for preview text in pixels unit.</div>
            </td>
            <td>
                <div class="description">
                    Set default placement for font import code in HTML document.
                    <small>This setting can be overridden individually for each font (in font settings page).</small>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="typolab-advanced-settings"<?php echo kalium()->request->has( 'typolab-advanced-settings' ) ? ' class="visible"' : ''; ?>>
        <table class="typolab-table">
            <thead>
            <tr>
                <th class="col-enable-frontend">
                    <label for="typolab_enabled">TypoLab Status</label>
                </th>
                <th class="col-export">
                    <label id="typolab_export_font_settings_code">Export Font Settings</label>
                </th>
                <th class="col-import">
                    <label for="typolab_import_font_settings">Import Font Settings</label>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr class="vtop">
                <td>
                    <div class="grouped-input no-border">
                        <div class="grouped-input-col select">
                            <select name="typolab_enabled" id="typolab_enabled">
                                <option value="yes"<?php selected( true, TypoLab::$typolab_enabled ); ?>>Enabled</option>
                                <option value="no"<?php selected( false, TypoLab::$typolab_enabled ); ?>>Disabled</option>
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="font-export-options">
                        <p class="no-top-padding">Choose what type of settings you want to export:</p>

                        <div class="checkboxes">
                            <label>
                                <input type="checkbox" checked="checked" id="typolab_export_font_faces">
                                Font Faces
                            </label>
                            <label>
                                <input type="checkbox" checked="checked" id="typolab_export_font_sizes">
                                Font Sizes
                            </label>
                            <label>
                                <input type="checkbox" id="typolab_export_font_settings">
                                Font Settings
                            </label>
                        </div>
                    </div>

                    <div class="font-export-loading">Font Export in Progress...</div>

                    <div class="font-export-code">
                        <div class="grouped-input">
                            <div class="grouped-input-col">
                                <textarea name="typolab_export_font_settings_code" id="typolab_export_font_settings_code" readonly="readonly" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="grouped-input">
                        <div class="grouped-input-col">
                            <textarea name="typolab_import_font_settings" id="typolab_import_font_settings" rows="5"></textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="hover vtop">
                <td>
                    <div class="description">
                        Enable or disable TypoLab on front-end.
                        <small>This setting is helpful for debugging purpose.</small>
                    </div>
                </td>
                <td>
                    <a href="#" class="button" id="typolab-export-settings">
                        <i class="dashicons dashicons-upload"></i>
                        Export Settings
                    </a>
                </td>
                <td>
                    <button type="button" id="typolab-import-button" class="button">
                        <i class="dashicons dashicons-download"></i>
                        Import Settings
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <a href="#" class="typolab-toggle-advanced-font-settings">
        <i class="dashicons dashicons-admin-generic"></i>
        Advanced Font Settings
    </a>

    <h3 class="typolab-h3">Hosted Font Settings</h3>

    <table class="typolab-table mutual-column-width">
        <thead>
        <tr>
            <th>
                <label>Fonts Directory</label>
            </th>
            <th>
                <label for="font_placement">Combine Files</label>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <code><?php echo $fonts_directory; ?></code>
				<?php echo $fonts_directory_writable ? '<mark class="writable">Writable</mark>' : '<mark class="not-writable">Not Writable</mark>'; ?>
            </td>
            <td>
                <div class="grouped-input no-border">
                    <div class="grouped-input-col select">
                        <select name="font_combining" id="font_combining">
                            <option value="yes"<?php selected( true, TypoLab::$font_combining ); ?>>Yes</option>
                            <option value="no"<?php selected( false, TypoLab::$font_combining ); ?>>No</option>
                        </select>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="hover vtop">
            <td>
                <div class="description">
                    Fonts directory and permissions.
					<?php if ( ! $fonts_directory_writable ) : ?>
                        <small>If you don't know how to make directory writable,
                            <a href="https://www.dummies.com/web-design-development/wordpress/navigation-customization/how-to-change-file-permissions-using-filezilla-on-your-ftp-site/" target="_blank">click
                                here</a> to learn more.</small>
                        <div class="error">
                            <p>Fonts directory is not writable, you cannot install fonts from these sources: <strong>Font
                                    Squirrel</strong> or <strong>Premium Fonts</strong>.</p>
                        </div>
					<?php else : ?>
                        <small>Fonts from sources: <strong>Font Squirrel</strong> and <strong>Premium Fonts</strong>
                            will be saved here.</small>
					<?php endif; ?>
                </div>
            </td>
            <td>
                <div class="description">
                    This option is applied only for Font Squirrel and Premium Fonts.
                    <small>If set to <strong>Yes</strong>, downloaded font files are going to be combined in one single
                        file.</small>
                </div>
            </td>
        </tr>
        </tbody>
    </table>


    <h3 class="typolab-h3">Downloaded Fonts</h3>

    <table class="downloaded-fonts-list typolab-table narrow horizontal-borders hover-rows">
		<?php if ( $downloaded_fonts ) : ?>
            <thead>
            <tr>
                <th class="dfl-family">Font Family</th>
                <th class="dfl-directory">Directory</th>
                <th class="dfl-source">Source</th>
                <th class="dfl-size">
                    <i class="dashicons dashicons-info" title="Indicates total size of folder including font files, stylesheets and other related files. In front-end the size may vary based on font variants you want to include."></i>
                    Size
                </th>
                <th class="dfl-date">Download Date</th>
                <th class="dfl-actions"></th>
            </tr>
            </thead>
		<?php endif; ?>
        <tbody>
		<?php
		if ( $downloaded_fonts ) :

			require_once( ABSPATH . 'wp-includes/ms-functions.php' );
			$uploads  = wp_upload_dir();

			foreach ( $downloaded_fonts as $font_info ) :
				$date = $font_info['date'];
				$path = $font_info['path'];

				// Display Information
				$font_id     = $font_info['id'];
				$font_source = $font_info['source'];

				$font_family_name = ucwords( str_replace( '-', ' ', $font_id ) );

				$font_dir        = "{$uploads['basedir']}/{$path}";
				$font_dir_exists = true === file_exists( $font_dir );

				$font_size = $font_dir_exists ? size_format( get_dirsize( $font_dir ), 2 ) : '-';
				$font_date = date( 'M d, Y', $date );
				?>
                <tr>
                    <td>
						<?php echo $font_family_name; ?>
                    </td>
                    <td>
                        <code><?php echo $path; ?></code>
						<?php if ( ! $font_dir_exists ) : ?>
                            <br>
                            <span class="label-error">Directory doesn't exists!</span>
						<?php endif; ?>
                    </td>
                    <td>
                        <strong class="source source-<?php echo esc_attr( $font_source ); ?>">
							<?php
							switch ( $font_source ) {
								case 'premium-fonts':
									echo 'Premium Fonts';
									break;

								case 'font-squirrel':
									echo 'Font Squirrel';
									break;

								default:
									echo $font_source;
							}
							?>
                        </strong>
                    </td>
                    <td>
						<?php echo $font_size; ?>
                    </td>
                    <td>
						<?php echo $font_date; ?>
                    </td>
                    <td>
                        <a href="<?php echo admin_url( "admin.php?page={$_GET['page']}&typolab-page={$_GET['typolab-page']}&delete-font-files=" . urlencode( "{$font_source},{$font_id}" ) ); ?>" class="trash" title="Delete Font Files">
                            <i class="dashicons dashicons-no"></i>
                        </a>
                    </td>
                </tr>
			<?php
			endforeach;

		else : ?>
            <tr>
                <td colspan="5" class="no-records">
                    There are no downloaded fonts yet!
                </td>
            </tr>
		<?php
		endif;
		?>
        </tbody>
    </table>

	<?php wp_nonce_field( 'typolab-save-font-settings' ); ?>
	<?php submit_button( 'Save Changes', 'primary', 'save_font_settings' ); ?>
</form>