<?php
/**
 * Kalium WordPress Theme
 *
 * Font upload form.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

wp_enqueue_media();
wp_print_scripts( array( 'wp-util' ) );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

$font_face     = isset( $font['family'] ) ? $font['family'] : '';
$font_variants = isset( $font['options']['font_variants'] ) ? $font['options']['font_variants'] : '';

?>
<table class="typolab-table">
    <tbody>
    <tr class="hover vtop">
        <th width="35%">
            <label for="font_url">Font Face:</label>
        </th>
        <td class="no-bg">
            <input type="text" name="font_face" id="font_face" value="<?php echo esc_attr( $font_face ); ?>"
                   required="required" placeholder="Enter font family name (@font-face)">
        </td>
    </tr>
    </tbody>
</table>

<!-- font variants container -->
<div class="uploaded-font-form-layout"></div>

<!-- font entry template -->
<script id="tmpl-uploaded-font-entry" type="text/html">
    <#
    var entryId = "uploaded-font-entry-" + data.entryId;
    #>
    <table class="typolab-table" id="{{ data.entryId }}">
        <thead>
        <th colspan="2" class="typolab-toggle-body">
            Font Variant

            <span class="toggle-indicator"></span>
        </th>
        </thead>
        <tbody>
        <tr class="hover vtop">
            <th width="35%">
                <label for="{{ data.entryId }}-style">Font Style:</label>
            </th>
            <td class="no-bg">
                <select class="select-font-style" name="font_variant[{{ data.index }}][style]" id="{{ data.entryId }}-style">
                    <option value="normal">Normal</option>
                    <option value="italic">Italic</option>
                    <option value="oblique">Oblique</option>
                </select>
            </td>
        </tr>
        <tr class="hover vtop">
            <th>
                <label for="{{ data.entryId }}-weight">Font Weight:</label>
            </th>
            <td class="no-bg">
                <select class="select-font-weight" name="font_variant[{{ data.index }}][weight]" id="{{ data.entryId }}-weight">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
					<?php for ( $i = 100; $i <= 900; $i += 100 ) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php endfor; ?>
                </select>
            </td>
        </tr>
        <tr class="hover vtop">
            <th class="delete-container">
                <label for="font_url">Font Files:</label>

                <a href="#delete" class="delete">Delete</a>
            </th>
            <td class="no-bg">

                <div class="font-file-entry font-file-woff">
                    <label for="{{ data.entryId }}-file-woff">WOFF File:</label>
                    <input type="text" name="font_variant[{{ data.index }}][files][woff]"
                           id="{{ data.entryId }}-file-woff"
                           placeholder="Pretty Modern Browsers">
                    <button class="button" type="button">Upload File</button>
                </div>

                <div class="font-file-entry font-file-woff2">
                    <label for="{{ entryId }}-file-woff2">WOFF2 File:</label>
                    <input type="text" name="font_variant[{{ data.index }}][files][woff2]"
                           id="{{ data.entryId }}-file-woff2"
                           placeholder="Super Modern Browsers">
                    <button class="button" type="button">Upload File</button>
                </div>

                <div class="font-file-entry font-file-ttf">
                    <label for="{{ data.entryId }}-file-ttf">TTF File:</label>
                    <input type="text" name="font_variant[{{ data.index }}][files][ttf]"
                           id="{{ data.entryId }}-file-ttf"
                           placeholder="Safari, Android, iOS">
                    <button class="button" type="button">Upload File</button>
                </div>

                <div class="font-file-entry font-file-svg">
                    <label for="{{ data.entryId }}-file-svg">SVG File:</label>
                    <input type="text" name="font_variant[{{ data.index }}][files][svg]"
                           id="{{ data.entryId }}-file-svg"
                           placeholder="Legacy iOS">
                    <button class="button" type="button">Upload File</button>
                </div>

                <div class="font-file-entry font-file-eot">
                    <label for="{{ data.entryId }}-file-eot">EOT File:</label>
                    <input type="text" name="font_variant[{{ data.index }}][files][eot]"
                           id="{{ data.entryId }}-file-eot"
                           placeholder="IE6-IE8 and IE9 Compat Modes">
                    <button class="button" type="button">Upload File</button>
                </div>

            </td>
        </tr>
        </tbody>
    </table>
</script>

<!-- font preview template -->
<script id="tmpl-font-preview" type="text/html">
    <div class="font-preview-row">
        <div class="font-preview-row-bg">
            <span class="row-title">Live Preview</span>

            <ul class="font-variants-list">
                <#
                for ( var i in data.variants ) {
                    var font = data.variants[ i ];
                #>
                <li>
                    <span class="preview-variant" style="font-family: {{ font.family }}; font-style: {{ font.style }}; font-weight: {{ font.weight }};"><?php echo esc_html( TypoLab::$font_preview_str ); ?></span>
                    <em class="font-variant-style">{{ font.style }}</em>
                    <# if ( 'normal' !== font.weight ) { #>
                    <em class="font-variant-weight"> / {{ font.weight }}</em>
                    <# } #>
                </li>
                <#
                }
                #>
            </ul>
        </div>
    </div>
</script>

<!-- font variants -->
<script id="uploaded-font-variants" type="text/html"><?php echo json_encode( $font_variants ); ?></script>

<a href="#" class="button" id="add-uploaded-font-variant">
    <i class="dashicons dashicons-plus"></i>
    Add font variant
</a>

<div id="uploaded-font-preview-data"></div>