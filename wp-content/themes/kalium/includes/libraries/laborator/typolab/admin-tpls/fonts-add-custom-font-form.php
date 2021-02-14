<?php
/**
 * Kalium WordPress Theme
 *
 * Add Custom Font Form.
 *
 * @var array $font
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$font_url = isset( $font['options']['font_url'] ) ? $font['options']['font_url'] : '';
$font_variants = isset( $font['options']['font_variants'] ) ? $font['options']['font_variants'] : '';
$main_font_variant = is_array( $font_variants ) && count( $font_variants ) ? array_shift( $font_variants ) : '';
$is_adobe_font = TypoLab::is_adobe_font( $font );
?>
<div class="custom-font-form-layout<?php echo $is_adobe_font ? ' is-adobe-font' : '' ?>">
	
	<table class="typolab-table">
		<thead>
			<th colspan="2">Font Face</th>
		</thead>
		<tbody>
			<tr class="hover vtop">
				<th width="35%">
					<label for="font_url">
                        <?php if ( $is_adobe_font ) : ?>
                            Project ID:
                        <?php else : ?>
                            Font Stylesheet URL:
                        <?php endif; ?>
                    </label>
				</th>
				<td class="no-bg">
					<input type="text" name="font_url" id="font_url" value="<?php echo esc_attr( $font_url ); ?>" required="required">
					
					<p class="description">
						<?php if ( $is_adobe_font ) : ?>
                            To retrieve project ID go to <strong>My Adobe Fonts > Web Projects</strong>
						<?php else : ?>
						    Enter absolute URL of CSS file which will import custom font.
						<?php endif; ?>

					</p>
				</td>
			</tr>
			<tr class="hover vtop">
				<th>
					<label for="font_variants_1">Font Family Name:</label>
				</th>
				<td class="no-bg">
					
					<div id="font-family-names" class="typolab-font-input">
						<input type="text" name="font_variants[]" id="font_variants_1" value="<?php echo esc_attr( $main_font_variant ); ?>" placeholder="e.g. Proxima Nova, Helvetica, sans-serif" required="required">
						
						<ul class="font-family-entries">
							<?php if ( is_array( $font_variants ) ) : foreach ( $font_variants as $variant ) : ?>
							<li>
								<input type="text" name="font_variants[]" value="<?php echo esc_attr( $variant ); ?>">
								<a href="#" class="remove"><i class="dashicons dashicons-no"></i></a>
							</li>
							<?php endforeach; endif; ?>
						</ul>
					</div>
					
				</td>
			</tr>
		</tbody>
	</table>
	
	<a href="#" class="button" id="add-custom-font-input">
		<i class="dashicons dashicons-plus"></i>
		Add another font family 
	</a>
	
	<a href="#" class="button" id="custom-font-generate-preview">
		<i class="dashicons dashicons-update-alt"></i>
		Generate Preview
	</a>
	
</div>