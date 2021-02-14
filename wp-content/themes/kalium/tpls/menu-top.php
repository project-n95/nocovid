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

$menu_id = 'main-menu';

$menu_top_menu_id       = kalium_get_theme_option( 'menu_top_menu_id' );
$menu_top_items_per_row = kalium_get_theme_option( 'menu_top_items_per_row' );

$menu_top_show_widgets            = kalium_get_theme_option( 'menu_top_show_widgets' );
$menu_top_nav_links_center        = kalium_get_theme_option( 'menu_top_nav_links_center' );
$menu_top_widgets_per_row         = kalium_get_theme_option( 'menu_top_widgets_per_row' );
$menu_top_widgets_container_width = kalium_get_theme_option( 'menu_top_widgets_container_width' );

if ( $menu_top_menu_id != '-' ) {
	if ( $menu_top_menu_id != 'default' ) {
		$menu_id = str_replace( 'menu-', '', $menu_top_menu_id );
	}

	$nav = kalium_nav_menu( $menu_id );
}

$menu_container_width    = 'col-sm-12';
$widgets_container_width = 'col-sm-12';

if ( $menu_top_show_widgets ) {
	switch ( $menu_top_widgets_container_width ) {
		case 'col-3':
			$menu_container_width    = 'col-sm-9';
			$widgets_container_width = 'col-sm-3';
			break;

		case 'col-4':
			$menu_container_width    = 'col-sm-8';
			$widgets_container_width = 'col-sm-4';
			break;

		case 'col-5':
			$menu_container_width    = 'col-sm-7';
			$widgets_container_width = 'col-sm-5';
			break;

		case 'col-7':
			$menu_container_width    = 'col-sm-5';
			$widgets_container_width = 'col-sm-7';
			break;

		case 'col-8':
			$menu_container_width    = 'col-sm-4';
			$widgets_container_width = 'col-sm-8';
			break;

		default:
			$menu_container_width    = 'col-sm-6';
			$widgets_container_width = 'col-sm-6';
	}

	if ( $menu_top_menu_id == '-' ) {
		$widgets_container_width = 'col-sm-12';
	}
}


// Widgets columns (Temporary)
switch ( kalium_get_number_from_word( $menu_top_widgets_per_row ) ) {
	// Two widgets per row
	case 6:
		$widgets_columns = 2;
		break;

	// Four widgets per row
	case 3:
		$widgets_columns = 4;
		break;

	// Three widgets per row
	default:
		$widgets_columns = 3;
}

?>
<div class="top-menu-container menu-type-<?php echo kalium_get_theme_option( 'main_menu_type' ); ?> <?php echo kalium_get_theme_option( 'menu_top_skin' ); ?>">
    <div class="container">
        <div class="row row-table row-table-middle">

			<?php if ( isset( $nav ) && $nav ) : ?>
                <div class="<?php echo esc_attr( $menu_container_width ); ?>">
                    <nav class="top-menu menu-row-<?php echo esc_attr( $menu_top_items_per_row );
					echo $menu_top_nav_links_center ? ' first-level-centered' : '' ?>">
						<?php echo $nav; ?>
                    </nav>
                </div>
			<?php endif; ?>

			<?php if ( $menu_top_show_widgets ) : ?>
                <div class="<?php echo esc_attr( $widgets_container_width ); ?>">
                    <div class="top-menu--widgets widgets--columned-layout widgets--columns-<?php echo $widgets_columns; ?> widget-area" role="complementary">
						<?php dynamic_sidebar( 'top_menu_sidebar' ); ?>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>