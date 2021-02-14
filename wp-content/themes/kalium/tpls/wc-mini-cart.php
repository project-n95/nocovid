<?php
/**
 *    Kalium WordPress Theme
 *
 *    Laborator.co
 *    www.laborator.co
 *
 * @deprecated 3.0 This template file will be removed or replaced with new one in templates/ folder.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

$is_empty = WC()->cart->get_cart_contents_count() == 0;

if ( false == defined( 'DOING_AJAX' ) ) {

	?>
    <div class="empty-loading-cart-contents">
		<?php _e( 'Loading cart contents...', 'kalium' ); ?>
    </div>
	<?php

	return;
}

?>
<div class="cart-items">

	<?php if ( $is_empty ) : ?>
        <div class="empty-loading-cart-contents">
			<?php echo sprintf( __( 'Your cart is empty! <a href="%s">Go shopping &raquo;</a>', 'kalium' ), get_permalink( wc_get_page_id( 'shop' ) ) ); ?>
        </div>
	<?php endif; ?>

	<?php

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			$price             = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );;

			?>
            <div class="cart-item">
                <div class="product-image">
					<?php
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

					if ( ! $product_permalink ) {
						echo $thumbnail; // PHPCS: XSS ok.
					} else {
						printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
					}
					?>
                </div>
                <div class="product-details">

                    <h3>
						<?php
						if ( ! $_product->is_visible() ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}
						?>
                    </h3>

                    <span class="mc-quantity">
						<?php echo sprintf( '%s: %d &times; %s', esc_html__( 'Quantity', 'woocommerce' ), $cart_item['quantity'], $price ); ?>
					</span>

					<?php
					/**
					 * Hooks after product quantity is shown
					 *
					 * @arg $cart_item        Current cart item
					 * @arg $cart_item_key    Current cart item key
					 * @arg $_product        Product instance
					 */
					do_action( 'kalium_woocommerce_mini_cart_after_quantity', $cart_item, $cart_item_key, $_product );
					?>
                </div>
                <div class="product-subtotal">
					<?php
					echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					?>
                </div>
            </div>
			<?php
		}

	}
	?>

</div>

<div class="cart-action-buttons">

	<?php if ( ! $is_empty ) : ?>
        <div class="mc-buttons-container">
            <div class="go-to-cart">
                <a href="<?php echo wc_get_cart_url(); ?>" class="button button-block button-secondary"><?php esc_html_e( 'View Cart', 'kalium' ); ?></a>
            </div>

            <div class="go-to-checkout">
                <a href="<?php echo wc_get_checkout_url(); ?>" class="button button-block"><?php esc_html_e( 'Checkout', 'kalium' ); ?></a>
            </div>
        </div>
	<?php endif; ?>

    <div class="cart-subtotal">
		<?php _e( 'Subtotal', 'kalium' ); ?>: <strong><?php wc_cart_totals_subtotal_html(); ?></strong>
    </div>

</div>