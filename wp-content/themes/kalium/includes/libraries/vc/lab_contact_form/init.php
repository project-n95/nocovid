<?php
/**
 *    Portable Contact Form
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_contact_form/contact.svg' );

vc_map( array(
	'base'        => 'lab_contact_form',
	'name'        => 'Contact Form',
	"description" => "Insert AJAX form",
	'category'    => 'Laborator',
	'icon'        => $lab_vc_element_icon,
	'params'      => array(
		array(
			'type'       => 'textfield',
			'heading'    => 'Name field title',
			'param_name' => 'name_title',
			'value'      => 'Name:'
		),
		array(
			'type'       => 'textfield',
			'heading'    => 'Email field title',
			'param_name' => 'email_title',
			'value'      => 'Email:'
		),
		array(
			'type'       => 'textfield',
			'heading'    => 'Message field title',
			'param_name' => 'message_title',
			'value'      => 'Message:'
		),
		array(
			'type'       => 'checkbox',
			'heading'    => 'Subject field',
			'param_name' => 'show_subject_field',
			'std'        => 'no',
			'value'      => array(
				'Show subject field' => 'yes',
			),
		),
		array(
			'type'       => 'textfield',
			'heading'    => 'Subject field title',
			'param_name' => 'subject_title',
			'value'      => 'Subject:',
			'dependency' => array(
				'element' => 'show_subject_field',
				'value'   => array( 'yes' )
			),
		),
		array(
			'type'       => 'textfield',
			'heading'    => 'Submit button title',
			'param_name' => 'submit_title',
			'value'      => 'Send Message'
		),
		array(
			'type'       => 'textfield',
			'heading'    => 'Success message',
			'param_name' => 'submit_success',
			'value'      => 'Thank you #, message sent!'
		),
		array(
			'type'        => 'checkbox',
			'heading'     => 'Show error alerts',
			'param_name'  => 'alert_errors',
			'std'         => 'no',
			'value'       => array(
				'Yes' => 'yes',
			),
			'description' => 'Show JavaScript alert message when required field is not filled.'
		),
		array(
			'type'       => 'checkbox',
			'heading'    => 'Use subject field as email subject',
			'param_name' => 'subject_field_as_email_subject',
			'value'      => array(
				'Yes' => 'yes',
			),
			'dependency' => array(
				'element' => 'show_subject_field',
				'value'   => array( 'yes' )
			),
		),
		array(
			'type'        => 'textfield',
			'heading'     => 'Receiver',
			'description' => 'Enter an email to receive contact form messages. If empty default admin email will be used (' . get_option( 'admin_email' ) . ')',
			'param_name'  => 'email_receiver'
		),
		array(
			'type'        => 'checkbox',
			'heading'     => 'Enable reCAPTCHA',
			'param_name'  => 'enable_recaptcha',
			'value'       => array(
				'Yes' => 'yes',
			),
			'description' => 'In order to use reCAPTCHA you must install and configure <a href="' . admin_url( 'plugin-install.php?s=reCaptcha+by+BestWebSoft&tab=search&type=term' ) . '" target="_blank">reCaptcha by BestWebSoft</a> plugin.'
		),
		array(
			'type'        => 'exploded_textarea_safe',
			'heading'     => 'Privacy policy',
			'description' => 'Optionally add some text about your site privacy policy to show when submitting the form. You can include links as well.',
			'param_name'  => 'privacy_policy_text'
		),
		array(
			'type'        => 'textfield',
			'heading'     => 'Extra class name',
			'param_name'  => 'el_class',
			'description' => 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.'
		),
		array(
			'type'       => 'css_editor',
			'heading'    => 'Css',
			'param_name' => 'css',
			'group'      => 'Design options'
		)
	)
) );

class WPBakeryShortCode_Lab_Contact_Form extends WPBakeryShortCode {
}

// Contact form request processing
function kalium_vc_contact_form_request() {
	$success       = false;
	$response_data = array();

	// Form options
	$form_options = kalium()->request->input( 'form_options' );
	$uniqid       = kalium_get_array_key( $form_options, 'uniqid' );

	// Form fields
	$form_fields = array(
		'name'    => kalium()->request->input( 'name' ),
		'email'   => kalium()->request->input( 'email' ),
		'subject' => kalium()->request->input( 'subject' ),
		'message' => kalium()->request->input( 'message' ),
	);

	// Form validity checker
	$hash                = kalium_get_array_key( $form_options, 'hash' );
	$form_hash           = wp_hash( $uniqid );
	$form_hash_recaptcha = wp_hash( "{$uniqid}-recaptcha" );

	// Check captcha verification
	$success = $form_hash == $hash;

	if ( $form_hash_recaptcha === $hash ) {
		global $gglcptch_options;

		// Recaptcha v3
		if ( 'v3' === kalium_get_array_key( $gglcptch_options, 'recaptcha_version' ) ) {
			$args = [
				'body'      => [
					'secret'   => $gglcptch_options['private_key'],
					'response' => kalium()->request->input( 'recaptchav3token' ),
					'remoteip' => filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ),
				],
				'sslverify' => false,
			];

			$recaptcha_req  = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $args );
			$recaptcha_resp = (array) json_decode( wp_remote_retrieve_body( $recaptcha_req ) );
			$success        = kalium_validate_boolean( $recaptcha_resp['success'] ) && (float) $recaptcha_resp['score'] >= (float) $gglcptch_options['score_v3'];
		} // Recaptcha v2
		else {
			$success = $form_hash_recaptcha === $hash && apply_filters( 'gglcptch_verify_recaptcha', true, 'string' );
		}

		if ( ! $success ) {
			$response_data['errors'] = __( 'Captcha verification failed, please try again!', 'kalium' );
		}
	} else if ( ! $success ) {
		$response_data['errors'] = 'Invalid Form Hash';
	}

	// Form verification passed
	if ( $success ) {

		// Newline
		$newline = "\n\n";

		// Page if
		$page_id = kalium_get_array_key( $form_options, 'page_id' );

		// Receiver
		$receiver  = kalium_get_array_key( $form_options, 'receiver' );
		$receiver  = kalium_unicode_translate_chars( $receiver );
		$receivers = explode( ',', $receiver );

		// Multiple receiver emails
		if ( count( $receivers ) > 1 && ! empty( array_filter( array_map( 'is_email', $receivers ) ) ) ) {
			$receiver = $receivers;
		} else if ( ! is_email( $receiver ) ) { // Validate single receiver
			$receiver = get_option( 'admin_email' );
		}

		// Subject field
		if ( ! wp_validate_boolean( kalium_get_array_key( $form_options, 'has_subject' ) ) ) {
			unset( $form_fields['subject'] );
		}

		// Email subject
		$email_subject = sprintf( _x( '[%s] New Contact Form message has been received.', 'contact form subject', 'kalium' ), get_bloginfo( 'name' ) );

		if ( wp_validate_boolean( kalium_get_array_key( $form_options, 'use_subject' ) ) ) {
			$email_subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $form_fields['subject'] );
		}

		// Email body
		$email_body = _x( 'You have received new contact form message.', 'contact form', 'kalium' );
		$email_body .= $newline . $newline;
		$email_body .= _x( '----- Message Details -----', 'contact form', 'kalium' );
		$email_body .= $newline;

		foreach ( $form_fields as $field_id => $field_value ) {
			$field_title = trim( kalium_get_array_key( $form_options, "{$field_id}_title" ), ':' );
			$field_value = trim( $field_value );

			if ( 'message' == $field_id ) {
				$field_value = $newline . $field_value;
			}

			$email_body .= sprintf( '%s: %s', $field_title, empty( $field_value ) ? '/' : $field_value );
			$email_body .= $newline;
		}

		$email_body .= str_repeat( '-', 27 );
		$email_body .= $newline . $newline;
		$email_body .= sprintf( _x( 'This message has been sent from IP: %s', 'contact form', 'kalium' ), $_SERVER['REMOTE_ADDR'] );
		$email_body .= $newline;
		$email_body .= sprintf( _x( 'Site URL: %s', 'contact form', 'kalium' ), home_url() );

		// Strip slashes
		$email_body = stripslashes( $email_body );

		// Filter email subject and body
		$email_subject = apply_filters( 'kalium_contact_form_subject', html_entity_decode( $email_subject ), $form_fields, $form_options );
		$email_body    = apply_filters( 'kalium_contact_form_message_body', $email_body, $form_fields, $form_options );

		// Headers
		$email_headers   = array();
		$email_headers[] = "Reply-To: {$form_fields['name']} <{$form_fields['email']}>";

		$email_headers = apply_filters( 'kalium_contact_form_mail_headers', $email_headers );

		// Send email
		$wp_mail_response        = wp_mail( $receiver, $email_subject, $email_body, $email_headers );
		$response_data['status'] = $wp_mail_response;

		// Execute actions after email are sent
		$email_sent_action_args = array(
			'receiver'         => $receiver,
			'headers'          => $email_headers,
			'subject'          => $email_subject,
			'message'          => $email_body,
			'fields'           => $form_fields,
			'opts'             => $form_options,
			'wp_mail_response' => $wp_mail_response,
		);

		do_action( 'kalium_contact_form_email_sent', $email_sent_action_args );
	}

	// Send response
	if ( $success ) {
		wp_send_json_success( $response_data );
	} else {
		wp_send_json_error( $response_data );
	}
}

add_action( 'wp_ajax_kalium_vc_contact_form_request', 'kalium_vc_contact_form_request' );
add_action( 'wp_ajax_nopriv_kalium_vc_contact_form_request', 'kalium_vc_contact_form_request' );

/**
 * Replace unicode character values to real chars.
 */
function _filter_unicode_translate_chars( $string ) {
	return chr( $string['ord'] );
}

/**
 * Transplate unicode chars with chr
 */
function kalium_unicode_translate_chars( $string ) {
	return preg_replace_callback( '/(&#(?<ord>[0-9]+);)/', '_filter_unicode_translate_chars', $string );
}
