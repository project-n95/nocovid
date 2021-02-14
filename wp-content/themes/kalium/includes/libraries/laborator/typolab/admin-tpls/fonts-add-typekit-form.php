<?php
/**
 *	TypeKit Add Font Form
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$kit_id = isset( $font['kit_id'] ) ? $font['kit_id'] : '';
?>
<div class="typekit-font-form-layout">

	<table class="typolab-table">
		<thead>
			<th colspan="2">Font Face</th>
		</thead>
		<tbody>
			<tr class="hover vtop">
				<th width="30%">
					<label for="kit_id">Kit ID:</label>
				</th>
				<td class="no-bg">
					<input type="text" name="kit_id" id="kit_id" value="<?php echo esc_attr( $kit_id ); ?>" required="required">
										
					<p class="description">
						If you don't know where to find Kit ID, <a href="https://d.pr/i/3yQI" target="_blank">click here</a> to learn more.
					</p>
				</td>
			</tr>
		</tbody>
	</table>
		
	<a href="#" class="button" id="typekit-font-generate-preview">
		<i class="dashicons dashicons-update-alt"></i>
		Generate Preview
	</a>
	
</div>