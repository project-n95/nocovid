<?php
/**
 *	Font Other Options
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$font_status = isset( $font['font_status'] ) ? $font['font_status'] : '';
$font_placement = isset( $font['font_placement'] ) ? $font['font_placement'] : '';
?>
<table class="typolab-table">
	<thead>
		<tr>
			<th colspan="2">Other Options</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>
				<label>Font import placement</label>
			</th>
			<td>
				<div class="grouped-input no-border">
					<div class="grouped-input-col select">
						<select name="font_placement">
							<option value="">Default</option>
							<option value="head"<?php selected( $font_placement, 'head' ); ?>>Inside &lt;head&gt; tag</option>
							<option value="body"<?php selected( $font_placement, 'body' ); ?>>Before &lt;/body&gt; tag</option>
						</select>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<label>Font status</label>
			</th>
			<td>
				<div class="grouped-input no-border">
					<div class="grouped-input-col select">
						<select name="font_status">
							<option value="published">Published</option>
							<option value="unpublished"<?php selected( $font_status, 'unpublished' ); ?>>Unpublished</option>
						</select>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>
