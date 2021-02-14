<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.4
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

?>
<?php // start: modified by Arlind ?>
<div class="checkout-form-option<?php when_match( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ), 'checkout-form-option--only' ); ?>">

    <div class="checkout-form-option--header">
        <?php wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' ), 'notice' ); ?>
    </div>

    <div class="checkout-form-option--content" id="checkout-coupon-form-container">
		<?php // end: modified by Arlind ?>

        <form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:none">

            <p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'woocommerce' ); ?></p>

            <p class="form-row form-row-first">
                <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
            </p>

            <p class="form-row form-row-last">
                <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
            </p>

            <div class="clear"></div>
        </form>

	    <?php // start: modified by Arlind ?>
    </div>

</div>
<?php // end: modified by Arlind ?>

