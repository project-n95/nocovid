<?php
/**
 * Kalium WordPress Theme
 *
 * Add New Font.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$current_font_source = kalium()->request->input( 'font_source' );

$font_source_image_sizes = [
	'google'        => 130,
	'font-squirrel' => 80,
	'premium'       => 95,
	'typekit'       => 130,
	'custom-font'   => 135,
	'uploaded-font' => 135,
];
?>
<form id="typolab-add-new" method="post" enctype="application/x-www-form-urlencoded">
	<?php wp_nonce_field( 'typolab-add-font' ); ?>

    <p>Select font source first and then continue to font options, each font source has different configuration set.</p>

    <div class="row-layout">
        <div class="col col-7">
            <table class="typolab-table typolab-select-font-source horizontal-borders">
                <thead>
                <tr>
                    <th colspan="2">Font Source</th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ( self::$font_sources as $source_id => $font_source ) :
					?>
                    <tr>
                        <td class="radio-input">
                            <input type="radio" name="font_source" id="font_source_<?php echo $source_id; ?>" value="<?php echo $source_id; ?>"<?php when_match( $source_id == $current_font_source || ( 'google' == $source_id && is_null( $current_font_source ) ), 'checked' ); ?>>
                        </td>
                        <td>
                            <img src="<?php echo self::$typolab_assets_url . "/img/{$source_id}.png"; ?>" width="<?php echo $font_source_image_sizes[ $source_id ]; ?>">
                        </td>
                    </tr>
				<?php
				endforeach;
				?>
                </tbody>
            </table>
        </div>
        <div class="col col-5">
            <table class="typolab-table typolab-selected-font-source">
                <thead>
                <tr>
                    <th>Selected Font Source</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
						<?php
						foreach ( self::$font_sources as $source_id => $font_source ) :

							?>
                            <div class="font-source-description font-source-description-<?php echo $source_id; ?>">
                                <h3><?php echo esc_html( $font_source['name'] ); ?></h3>
								<?php echo wpautop( $font_source['description'] ); ?>
                            </div>
						<?php

						endforeach;
						?>
                    </td>
                </tr>
                </tbody>
            </table>

			<?php submit_button( 'Continue', 'primary', 'typolab_add_font', '' ); ?>
        </div>
    </div>

</form>
