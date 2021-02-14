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

global $product;

?>
<div class="product-item">
	<a href="<?php echo $product->get_permalink(); ?>" class="product-link">
		<?php 
			// Product Thumbnail
			woocommerce_template_loop_product_thumbnail(); 
				
			// Product Title
			echo '<span class="product-title">';
			the_title();
			echo '</span>';
			
			// Product Price
			echo '<span class="product-price">';
			echo $product->get_price_html();
			echo '</span>';
		?>
	</a>
	
	<div class="add-to-cart">
	<?php
		// Add To Cart
		woocommerce_template_loop_add_to_cart();
	?>
	</div>
</div>
