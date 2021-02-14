<?php
/**
 *	Blog post inside loop
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		@classes
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<li <?php post_class( 'post' ); ?>>

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		
		<?php
			/**
			 * kalium_blog_loop_post_before hook
			 *
			 * @hooked kalium_blog_post_thumbnail - 10
			 */
			do_action( 'kalium_blog_loop_post_before' );
		?>
		
		<div class="post-details">
			
			<?php
				/**
				 * kalium_blog_loop_post_details hook
				 *
				 * @hooked kalium_blog_post_title - 10
				 * @hooked kalium_blog_post_excerpt - 20
				 * @hooked kalium_blog_post_date - 30
				 * @hooked kalium_blog_post_category - 40
				 */
				do_action( 'kalium_blog_loop_post_details' );
			?>
			
		</div>
		
		<?php
			/**
			 * kalium_blog_loop_post_after hook
			 *
			 * @hooked none
			 */
			do_action( 'kalium_blog_loop_post_after' );
		?>
		
	</div>
	
</li>