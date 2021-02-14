<?php
/**
 *	Kalium WordPress Theme
 *
 *	Laborator.co
 *	www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

include locate_template( 'tpls/portfolio-single-item-details.php' );

do_action( 'kalium_portfolio_item_before', 'type-6' );

?>
<div class="container">

	<div class="page-container">

		<div class="single-portfolio-holder portfolio-type-6 clearfix">
			
			<div class="title section-title">
				<h1><?php the_title(); ?></h1>

				<?php if ( $sub_title ) : ?>
				<p><?php echo wp_kses_post( $sub_title ); ?></p>
				<?php endif; ?>
			</div>
			
			<?php if ( $post_thumbnail_id ) : ?>
			<a href="#open" data-portfolio-item-id="<?php echo the_ID(); ?>" class="lightbox-featured-image">
				<?php echo kalium_get_attachment_image( $post_thumbnail_id, apply_filters( 'kalium_single_portfolio_gallery_image', 'portfolio-single-img-1' ) ); ?>
				<em>
					<?php _e( 'Open in Lightbox', 'kalium' ); ?>
					<i></i>
				</em>
			</a>
			<?php endif; ?>
			
			<?php include locate_template( 'tpls/portfolio-single-prevnext.php' ); ?>
			
		</div>
	</div>

</div>
<?php

// Generate Portfolio Instance Object	
$portfolio_args = kalium_get_portfolio_query();

kalium_portfolio_generate_portfolio_instance_object( $portfolio_args );