<?php
/**
 *	Single post image (or post format content)
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@args		$post, $post_format_content, $thumbnail_size
 *	@version	2.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<div class="post-image">
	
	<?php
		/**
		 * Before post image hooks
		 *
		 * @hooked none
		 */
		do_action( 'kalium_blog_single_before_post_image' );
	?>
	
	<?php 
		// Show post format content if it contains
		if ( ! empty( $post_format_content ) ) :
			
			kalium_show_post_format_content( $post_format_content );
		
		else :

			// Show featured image
			if ( has_post_thumbnail() ) :
			
				?>
				<a href="<?php echo kalium_blog_post_image_link( $post ); ?>" class="featured-image">
					<?php
						echo kalium_get_attachment_image( get_post_thumbnail_id(), $thumbnail_size );
					?>
				</a>
				<?php
			
			endif;
			
		endif;
	?>
	
	<?php
		/**
		 * After post image hooks
		 *
		 * @hooked none
		 */
		do_action( 'kalium_blog_single_after_post_image' );
	?>
	
</div>