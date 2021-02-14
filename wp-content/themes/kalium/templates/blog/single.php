<?php
/**
 *	Single post post
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post' ); ?>>
	
	<?php
		/**
		 * Before post details
		 *
		 * @hooked kalium_blog_single_post_image_boxed - 10
		 */
		do_action( 'kalium_blog_single_post_details_before' );
	?>
	
	<section class="post--column post-body">
		
		<?php
			/**
			 * Single post details
			 *
			 * @hooked kalium_blog_post_title - 10
			 * @hooked kalium_blog_post_content - 20
			 * @hooked kalium_blog_single_post_tags_list - 30
			 * @hooked kalium_blog_single_post_share_networks - 40
			 * @hooked kalium_blog_single_post_author_info_below - 50
			 * @hooked kalium_blog_single_post_prev_next_navigation - 60
			 */
			do_action( 'kalium_blog_single_post_details' );
		?>
		
	</section>
	
	<?php
		/**
		 * After post details
		 *
		 * @hooked kalium_blog_single_post_author_and_meta_aside - 10
		 * @hooked kalium_blog_single_post_prev_next_navigation - 20
		 */
		do_action( 'kalium_blog_single_post_details_after' );
	?>
	
</article>