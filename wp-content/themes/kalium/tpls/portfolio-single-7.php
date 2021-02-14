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

do_action( 'kalium_portfolio_item_before', 'type-7' ); 
?>

<div class="vc-container portfolio-vc-type-container single-portfolio-holder portfolio-type-7">
	<?php the_content(); ?>
</div>

<div class="container">

	<div class="page-container">

		<div class="single-portfolio-holder">
			
			<?php include locate_template( 'tpls/portfolio-single-prevnext.php' ); ?>
			
		</div>
	
	</div>
	
</div>