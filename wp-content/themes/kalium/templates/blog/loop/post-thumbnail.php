<?php
/**
 *	Post thumbnail inside the loop
 *	
 *	Laborator.co
 *	www.laborator.co 
 *
 *	@author		Laborator
 *	@var		$thumbnail_size
 *	@var		$post_format_content
 *	@version	3.0.9
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<div class="post-thumbnail">
	
	<?php
		/**
		 * Before post thumbnail hooks.
		 */
		do_action( 'kalium_blog_loop_before_post_thumbnail' );
	?>

	<?php
		// Show post format content if it contains
		if ( ! empty( $post_format_content ) ) :
			
			kalium_show_post_format_content( $post_format_content );
		
		else :
		
			?>
			<a href="<?php the_permalink(); ?>" target="<?php echo esc_attr( kalium_blog_post_link_target() ); ?>" rel="bookmark">
			<?php
			
			// Show featured image
			if ( has_post_thumbnail() ) :
			
				echo kalium_get_attachment_image( get_post_thumbnail_id(), $thumbnail_size );
			
			// Show image placeholder
			elseif ( apply_filters( 'kalium_blog_loop_show_image_placeholder', true ) ) : 
				
				echo '<div class="blog-image-placeholder"></div>';
			
			endif;
			
			?>
			</a>
			<?php
			
		endif;
	?>
	
	<?php
		/**
		 * After post thumbnail hooks
		 *
		 * @hooked kalium_blog_post_hover_layer - 10
		 * @hooked kalium_blog_post_format_icon - 20
		 */
		do_action( 'kalium_blog_loop_after_post_thumbnail' );
	?>
	
</div>
