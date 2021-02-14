<?php
/**
 * Kalium WordPress Theme
 *
 * About tabs.
 *
 * @var array $tabs
 * @var string $page
 * @var string $current_tab
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<nav class="about-kalium__header-navigation about__header-navigation nav-tab-wrapper wp-clearfix">
	<?php
	foreach ( $tabs as $tab_id => $title ) :

		// Classes
		$classes = [
			'nav-tab',
		];

		// Active tab class
		if ( $tab_id === $current_tab ) {
			$classes[] = 'nav-tab-active';
		}

		?>
        <a href="<?php echo Kalium_About::get_tab_link( $tab_id ); ?>" <?php kalium_class_attr( $classes ); ?>><?php echo esc_html( $title ); ?></a>
	    <?php

	endforeach;
	?>
</nav>
