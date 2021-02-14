<?php
/**
 *	Font Sizes
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$font_sizes           = TypoLab_Font_Sizes::get_font_sizes( true );
$saved_font_sizes	  = TypoLab::get_setting( 'font_sizes' );
$viewport_breakpoints = TypoLab::$viewport_breakpoints;
$measuring_units      = [ 'px', 'em', 'rem', '%' ];
$font_cases			  = [ '' => 'Default', 'uppercase' => 'Upper case', 'lowercase' => 'Lower case', 'capitalize' => 'Capitalize' ];
?>
<form method="post" enctype="application/x-www-form-urlencoded" class="font-sizes-container">
<?php
	foreach ( $font_sizes as $group_index => $size_group ) :
		$group_id         = $size_group['id'];
		$selectors        = $size_group['selectors'];
		$builtin          = $size_group['builtin'];
		$selectors_count  = count( $selectors );
		
		// Get Sizes for current group
		$group_sizes = isset( $saved_font_sizes[ $group_index ] ) ? $saved_font_sizes[ $group_index ] : [];
		$selected_unit = isset( $group_sizes['unit'] ) ? $group_sizes['unit'] : '';
		?>
		<div class="font-size-group">
			<div class="font-size-group-description">
				<h4><?php echo esc_html( $size_group['title'] ); ?></h4>
				<?php echo wpautop( $size_group['description'] ); ?>
				
				<?php if ( ! $builtin ) : ?>
				<a href="<?php echo admin_url( "admin.php?page={$_GET['page']}&typolab-page={$_GET['typolab-page']}&typolab-action=delete-size-group&group-id={$group_id}" ); ?>" class="delete-custom-font-sizes-group">Delete Group</a>
				<?php endif; ?>
			</div>
			
			<div class="font-size-entries">
				
				<table class="typolab-table">
					<thead>
						<th class="viewport-column">Viewport</th>
						<?php 
						foreach ( $viewport_breakpoints as $device_type => $breakpoints ) : 
							switch ( $device_type ) :
								case 'desktop':
									$device_icon = 'dashicons-laptop';
									break;
									
								case 'tablet':
									$device_icon = 'dashicons-tablet';
									break;
									
								case 'mobile':
									$device_icon = 'dashicons-smartphone';
									break;
									
								default:
									$device_icon = 'dashicons-desktop';
							endswitch; 
						?>
						<th class="device-field<?php when_match( 'general' !== $device_type, 'hidden' ); ?>">
							<i title="<?php echo ucwords( $device_type ); ?>" class="dashicons <?php echo $device_icon; ?> tooltip"></i>
						</th>
						<?php endforeach; ?>
						<th class="device-field hidden" title="Font case, `text-transform` property">
							<i class="dashicons dashicons-editor-textcolor"></i>
						</th>
					</thead>
					<tbody>
					<?php
					foreach ( $selectors as $selector_id => $selector_path ) : 
						$selector_id_sanitized = sanitize_title( $selector_id );
						$current_case = '';

						if ( isset( $group_sizes['sizes'][ $selector_id_sanitized ]['text-transform'] ) ) {
						    $current_case = $group_sizes['sizes'][ $selector_id_sanitized ]['text-transform'];
                        }
						//$current_case = ! empty( $group_sizes['sizes'] ) ? get_array_key( $group_sizes['sizes'][ $selector_id_sanitized ], 'text-transform' ) : '';
					?>
						<tr class="hover<?php #when_match( $device_type != 'general', "hidden" ); ?>">
							<th class="label">
								<label for="<?php echo "font_sizes_{$group_index}_{$selector_id_sanitized}"; ?>"<?php if ( ! $builtin ) : ?> title="Selector: <?php echo esc_attr( $selector_path ); ?>"<?php endif; ?>><?php echo $selector_id; ?></label>
							</th>
							<?php
							$i = 0;
							foreach ( $viewport_breakpoints as $device_type => $breakpoints ) : 
							
								$size = '';
								
								if ( isset( $group_sizes['sizes'][ $selector_id_sanitized ][ $device_type ] ) ) {
									$size = $group_sizes['sizes'][ $selector_id_sanitized ][ $device_type ];
								}
							?>
							<td class="device-field hover<?php when_match( 'general' !== $device_type, 'hidden' ); ?>">
								<input<?php when_match( $i == 0, "id=\"font_sizes_{$group_index}_{$selector_id_sanitized}\"" ); ?> type="number" name="font_sizes[<?php echo $group_index; ?>][sizes][<?php echo $selector_id_sanitized; ?>][<?php echo $device_type; ?>]" class="center" step="any" value="<?php echo $size; ?>" placeholder="<?php echo ucwords( $device_type ); ?>">
							</td>
							<?php 
								$i++;
							endforeach;
							?>
							<td class="device-field hover hidden">
								<select name="font_sizes[<?php echo $group_index; ?>][sizes][<?php echo $selector_id_sanitized; ?>][text-transform]">
									<?php 
										foreach ( $font_cases as $case => $title ) :
											?>
											<option value="<?php echo esc_attr( $case ); ?>" <?php selected( $case, $current_case, true ); ?>><?php echo esc_html( $title ); ?></option>
											<?php
										endforeach; 
									?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
						<tr>
							<td class="no-padding" colspan="6">
								<a href="#show-responsive" class="show-responsive-options">
									<span class="responsive-show">Show Responsive</span>
									<span class="responsive-hide">Hide Responsive</span>
									
									<i class="dashicons dashicons-arrow-down-alt2"></i>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
				
				<div class="measuring-unit">
					Size Unit:
					<select name="font_sizes[<?php echo $group_index; ?>][unit]">
					<?php foreach ( $measuring_units as $measuring_unit ) : ?>
						<option value="<?php echo esc_attr( $measuring_unit ); ?>"<?php selected( $measuring_unit, $selected_unit ); ?>><?php echo $measuring_unit; ?></option>
					<?php endforeach; ?>
					
					</select>
				</div>
				
			</div>
		</div>
		<?php
	
	endforeach;
?>


<div class="add-new-font-size-group-container">
	
	<table class="typolab-table horizontal-borders">
		<thead>
			<tr>
				<th colspan="2" class="center">
					Add New Font Size Group
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="vtop">
				<th>
					<label for="new_group_title">Group Title:</label>
				</th>
				<td>
					<div class="grouped-input">
						<div class="grouped-input-col">
							<input type="text" name="new_group_title" id="new_group_title" placeholder="Font group title i.e. Footer font sizes">
						</div>
					</div>
				</td>
			</tr>
			<tr class="vtop">
				<th>
					<label for="new_group_description">Group Description:</label>
				</th>
				<td>
					<div class="grouped-input">
						<div class="grouped-input-col">
							<textarea name="new_group_description" id="new_group_description"></textarea>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th colspan="2" class="center">
					CSS Selectors
				</th>
			</tr>
			<tr>
				<td colspan="2">
													
					<table class="add-font-selectors-table">
						<thead>
							<tr>
								<th>Selector Alias</th>
								<th>CSS Selector</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="group-size-alias-col">
									<input type="text" name="new_group_size_alias[]" placeholder="Example: Copyright Text">
								</td>
								<td>
									<input type="text" name="new_group_size_path[]" placeholder="Example: .footer .copyrights">
								</td>
								<td class="add-remove">
									<a href="#" class="add-entry tooltip" title="Add new selector">
										<i class="dashicons dashicons-plus"></i>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
					
				</td>
			</tr>
		</tbody>
	</table>

</div>
	
	<div class="save-sizes-container">
		<?php wp_nonce_field( 'typolab-save-font-sizes' ); ?>
		<?php submit_button( 'Save Changes', 'primary', 'save_font_sizes' ); ?>
		
		<a href="#" id="new-font-sizes-group" class="button">
			<i class="dashicons dashicons-plus"></i>
			New Font Sizes Group
		</a>
	</div>
</form>