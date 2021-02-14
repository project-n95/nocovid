<?php
/**
 *	Contact Form
 *
 *	Laborator.co
 *	www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
if ( function_exists( 'vc_map_get_attributes' ) ) {
	$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
}

extract( $atts );

// Privacy policy text
$privacy_policy_text = vc_value_from_safe( $privacy_policy_text );

// Form id
$uniqid = uniqid( 'el_' );

// Enable recaptcha
$enable_recaptcha = 'yes' === $enable_recaptcha;

// Form options
$form_options = array(
	'uniqid' => $uniqid,
	'name_title' => $name_title,
	'email_title' => $email_title,
	'subject_title' => $subject_title,
	'message_title' => $message_title,
	'has_subject' => 'yes' == $show_subject_field,
	'use_subject' => $show_subject_field && $subject_field_as_email_subject,
	'receiver' => antispambot( $email_receiver ),
);

// Hash
$form_options['hash'] = wp_hash( $enable_recaptcha ? "{$uniqid}-recaptcha" : $uniqid );

// Element Class
$class = $this->getExtraClass( $el_class );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, "lab-contact-form contact-form {$class}", $this->settings['base'], $atts );
$css_class .= vc_shortcode_custom_css_class( $css, ' ' );

// Parse form options in JS
kalium_define_js_variable( 'contact_form', $form_options, $uniqid );
?>
<div class="<?php echo esc_attr( $css_class ); ?>">

	<form action="" method="post" class="contact-form" id="<?php echo esc_attr( $uniqid ); ?>" data-alerts="<?php echo $alert_errors == 'yes' ? 1 : 0; ?>" data-alerts-msg="<?php echo esc_attr( __( 'Please fill "%" field.', 'kalium' ) ); ?>" data-privacy-error-msg="<?php echo esc_attr( __( 'You must check privacy policy checkbox in order to submit the form.', 'kalium' ) ); ?>" novalidate>

		<div class="row">

    		<div class="col-sm-6">
				<div class="form-group labeled-input-row">
					<?php if ( $name_title ) : ?>
					<label for="<?php echo "{$uniqid}_name"; ?>"><?php echo esc_html( $name_title ); ?></label>
					<?php endif; ?>
					<input name="name" id="<?php echo "{$uniqid}_name"; ?>" type="text" placeholder="" data-label="<?php echo esc_attr( trim( $name_title, ':?.' ) ); ?>">
				</div>
    		</div>

			<div class="col-sm-6">
				<div class="form-group labeled-input-row">
					<?php if ( $email_title ) : ?>
					<label for="<?php echo "{$uniqid}_email"; ?>"><?php echo esc_html( $email_title ); ?></label>
					<?php endif; ?>
					<input name="email" id="<?php echo "{$uniqid}_email"; ?>" type="email" placeholder="" data-label="<?php echo esc_attr( trim( $email_title, ':?.' ) ); ?>">
				</div>
			</div>

			<?php if ( $show_subject_field == 'yes' ) : ?>
	    		<div class="col-sm-12">
					<div class="form-group labeled-input-row">
						<?php if ( $subject_title ) : ?>
						<label for="<?php echo "{$uniqid}_subject"; ?>"><?php echo esc_html( $subject_title ); ?></label>
						<?php endif; ?>
						<input name="subject" id="<?php echo "{$uniqid}_subject"; ?>"<?php echo apply_filters(  'kalium_contact_form_subject_field_required', false ) ? ' class="is-required"' : ''; ?> type="text" placeholder="" data-label="<?php echo esc_attr( trim( $subject_title, ':?.' ) ); ?>">
					</div>
	    		</div>
			<?php endif; ?>

			<div class="col-sm-12">
				<div class="form-group labeled-textarea-row">
					<?php if ( $message_title ) : ?>
					<label for="<?php echo "{$uniqid}_message"; ?>"><?php echo esc_html( $message_title ); ?></label>
					<?php endif; ?>
					<textarea name="message" id="<?php echo "{$uniqid}_message"; ?>" placeholder="" data-label="<?php echo esc_attr( trim( $message_title, ':?.' ) ); ?>"></textarea>
				</div>
			</div>

		</div><!-- row -->

		<?php if ( $enable_recaptcha ) : ?>
			<div class="form-group contact-form-recaptcha">

				<?php
				    echo apply_filters( 'gglcptch_display_recaptcha', '' );
                ?>

			</div>
		<?php endif; ?>

		<?php if ( $privacy_policy_text ) : ?>
			<div class="form-group contact-form-privacy-policy">

				<label>
					<input type="checkbox" name="privacy_policy_check" />
					<span><?php echo $privacy_policy_text; ?></span>
				</label>

			</div>
		<?php endif; ?>

		<button type="submit" name="send" class="button">
			<span class="pre-submit"><?php echo esc_html( $submit_title ); ?></span>
			<span class="success-msg"><?php echo strip_tags( $submit_success, '<strong><span><em>' ); ?> <i class="flaticon-verification24"></i></span>
			<span class="loading-bar">
				<span></span>
			</span>
		</button>

	</form>

</div>