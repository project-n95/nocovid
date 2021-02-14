<?php
/**
 *	Previous and next post links
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$prev
 *	@var		$prev_title
 *	@var		$next
 *	@var		$next_title
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<nav class="post-navigation">
	
	<?php if ( ! empty( $next ) ) : ?>
	<a href="<?php echo get_permalink( $next ); ?>" class="post-navigation--next">
		<span class="post-navigation--arrow">
			<i class="flaticon-arrow427"></i>
		</span>
		
		<span class="post-navigation--label">
			<em><?php echo esc_html( $next_title ); ?></em>
			<strong class="post-navigation--post-title">
				<?php echo get_the_title( $next ); ?>
			</strong>
		</span>
	</a>
	<?php endif; ?>
	
	<?php if ( ! empty( $prev ) ) : ?>
	<a href="<?php echo get_permalink( $prev ); ?>" class="post-navigation--prev">
		<span class="post-navigation--arrow">
			<i class="flaticon-arrow413"></i>
		</span>
		
		<span class="post-navigation--label">
			<em><?php echo esc_html( $prev_title ); ?></em>
			<strong class="post-navigation--post-title">
				<?php echo get_the_title( $prev ); ?>
			</strong>
		</span>
	</a>
	<?php endif; ?>
	
</nav>