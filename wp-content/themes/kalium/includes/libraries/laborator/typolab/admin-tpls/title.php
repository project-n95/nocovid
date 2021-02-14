<?php
/**
 * Kalium WordPress Theme
 *
 * TypoLab Title.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$page          = kalium()->request->query( 'page' );
$add_font_link = sprintf( '<a href="%s" class="page-title-action">Add Font</a>', esc_url( admin_url( "admin.php?page={$page}&typolab-action=add-font" ) ) );
?>
<h1>
	<?php if ( isset( $title ) ) : ?>
		<?php echo str_replace( '{add-font-link}', $add_font_link, esc_html( $title ) ); ?>
		<?php if ( isset( $sub_title ) ) : ?>
            <small><?php echo esc_html( $sub_title ); ?></small>
		<?php endif; ?>
	<?php else : ?>
        Typography <?php echo $add_font_link; ?>
	<?php endif; ?>
</h1>