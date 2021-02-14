<?php
/**
 * Kalium WordPress Theme
 *
 * Laborator.co
 * www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

if ( count( $checklists ) ) : ?>
    <div class="services row">
		<?php
		foreach ( $checklists as $checklist ) :
			$checklist_arr = array_filter( explode( PHP_EOL, trim( $checklist['checklist'] ) ) );

			if ( empty( $checklist_arr ) ) {
				continue;
			}
			?>
            <div class="checklist-entry<?php echo $checklist['column_width'] == '1-2' ? ' col-sm-6' : ' col-sm-12'; ?>">
				<?php if ( $checklist['checklist_title'] ) : ?>
                    <h3><?php echo esc_html( $checklist['checklist_title'] ); ?></h3>
				<?php endif; ?>

                <ul>
					<?php foreach ( $checklist_arr as $checklist_line ) : ?>
                        <li><?php echo wp_kses_post( $checklist_line ); ?></li>
					<?php endforeach; ?>
                </ul>
            </div>
		<?php
		endforeach;
		?>
    </div>
<?php endif; ?>