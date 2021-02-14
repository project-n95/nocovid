<?php
/**
 *	Blog posts loop
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$id
 *	@var		$classes
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<ul id="<?php echo esc_attr( $id ); ?>" class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $classes ) ); ?>">
	
	<?php
		/**
		 * Blog posts loop
		 */
		if ( have_posts() ) :
			
			/**
			 * Before blog loop
			 *
			 * @hooked kalium_blog_loop_loading_posts_indicator - 10
			 */
			do_action( 'kalium_blog_loop_before' );
		 
			while ( have_posts() ) : the_post();
				
				kalium_blog_loop_post_template();
				
			endwhile;
			
			/**
			 * After blog loop
			 */
			do_action( 'kalium_blog_loop_after' );
		
		/**
		 * No posts found
		 */
		else :
			
			/**
			 * No posts to show
			 *
			 * @hooked kalium_blog_no_posts_found_message - 10
			 */
			do_action( 'kalium_blog_no_posts_found' );
		
		endif;
	?>
	
</ul>