<?php
/**
 *	Post hover layer inside the loop
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$classes
 *	@var		$hover_icon
 *	@var		$hover_icon_custom
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	
	<?php
		// Simple icon
		if ( 'static-eye' == $hover_icon ) {
			printf( '<span class="hover-icon basic"><i class="%s"></i></span>', esc_attr( apply_filters( 'kalium_blog_loop_hover_icon', 'icon icon-basic-eye' ) ) );
		}
		// Animated eye icon
		elseif ( 'animated-eye' == $hover_icon ) {
			echo '<span class="hover-icon animated-eye"></span>';
		}
		// Custom icon
		elseif ( 'custom' == $hover_icon ) {
			printf( '<span class="hover-icon custom">%s</span>', $hover_icon_custom );
		}
	?>
	
</div>