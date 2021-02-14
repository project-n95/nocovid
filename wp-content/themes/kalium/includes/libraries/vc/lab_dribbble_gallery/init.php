<?php
/**
 *	Dribbble Gallery
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_dribbble_gallery/dribbble.svg' );

$dribbble_api_warning = '<br><br>
<div class="wpb_element_wrapper">
	<div class="wpb_element_wrapper vc_message_box vc_message_box-square vc_message_box-solid vc_color-info" style="padding: 0.5em;">
		Dribbble API v2 requires Client ID, Client Secret and Auth Token in order to retrieve user shots. 
		<br>
		In order to generate Access Token you must create an Application and authorize it to get auth code, there is no automatic way of doing this by simply providing the Client ID and Client Secret.
		<br>
		<a href="https://developer.dribbble.com/" target="_blank" style="color: #fff;">https://developer.dribbble.com/</a>
		<br>
		Learn more how to <a href="https://medium.com/@nithin_94885/dribbble-shots-in-your-website-v2-api-5945a355d106" target="_blank" style="color: #fff;">create a Dribbble application &raquo;</a>
	</div>
</div>';

$dribbble_api_warning_2 = '<br><br>
<div class="wpb_element_wrapper">
	<div class="wpb_element_wrapper vc_message_box vc_message_box-square vc_message_box-solid vc_color-info" style="padding: 0.5em;">
		To generate Auth Code follow these steps:
		<ol>
			<li>
				Open in new tab the below URL and replace {CLIENT_ID} with your Client ID:<br>
				<strong>https://dribbble.com/oauth/authorize?client_id={CLIENT_ID}</strong>
				<br>
				<a href="https://d.pr/i/7BRhZD" target="_blank" style="color: #fff;">See example</a>
			</li>
			<li>
				Then click Authorize button as shown <a href="https://d.pr/i/KHKBq2" target="_blank" style="color: #fff">in this image</a>.
			</li>
			<li>
				After that you will be redirected to your defined callback URL, there you copy the Auth Code in URL argument.
				<br>
				<a href="https://d.pr/i/teME1Q" target="_blank" style="color: #fff;">See how &raquo;</a>
			</li>
		</ol> 
		
		If for any reason you don\'t see Dribbble shots please check your Client ID and Client Secret keys and/or repeat the steps above.
	</div>
</div>';

vc_map( array(
	'base'             => 'lab_dribbble_gallery',
	'name'             => 'Dribbble Gallery',
	"description"      => "Profile shots",
	'category'         => array( 'Laborator', 'Portfolio' ),
	'icon'             => $lab_vc_element_icon,
	'params' => array(
		array(
			'type'           => 'textfield',
			'heading'        => 'API Client ID',
			'param_name'     => 'client_id',
			'description'    => 'The client ID you received from Dribbble when you registered.' . $dribbble_api_warning
		),
		array(
			'type'           => 'textfield',
			'heading'        => 'API Client Secret',
			'param_name'     => 'client_secret',
			'description'    => 'The client secret you received from Dribbble when you registered.'
		),
		array(
			'type'           => 'textfield',
			'heading'        => 'API Auth Code',
			'param_name'     => 'auth_code',
			'description'    => 'Dribbble API requires this information in order to work properly. To create an application <a href="http://developer.dribbble.com/" target="_blank">click here</a>.' . $dribbble_api_warning_2,
		),
		array(
			'type'           => 'textfield',
			'heading'        => 'Shots Count',
			'param_name'     => 'count',
			'value'     	 => '9',
			'description'    => 'Number of shots to retrieve. (Max: 12)'
		),
		array(
			'type'           => 'dropdown',
			'heading'        => 'Columns',
			'admin_label'    => true,
			'param_name'     => 'columns',
			'std'            => 'three',
			'value'          => array(
				'3 Items per Row'    => 'three',
				'4 Items per Row'    => 'four',
			),
			'description' => 'Number of columns to show dribbble shots.'
		),
		array(
			'type'           => 'vc_link',
			'heading'        => 'More Link',
			'param_name'     => 'more_link',
			'value'          => '',
			'description'	 => 'This will show "More" button in the end of portfolio items.'
		),
		array(
			'type'           => 'textfield',
			'heading'        => 'Extra class name',
			'param_name'     => 'el_class',
			'description'    => 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.'
		),
		array(
			'type'       => 'css_editor',
			'heading'    => 'Css',
			'param_name' => 'css',
			'group'      => 'Design options'
		)
	)
) );

class WPBakeryShortCode_Lab_Dribbble_Gallery extends WPBakeryShortCode {

	/**
	 * Get access token.
	 */
	public static function get_access_token( $client_id, $client_secret, $code ) {
		$access_token = null;
		$current_post_id = get_queried_object_id();
		$token_meta_key = '_dribble_access_token_' . md5( $client_id . $client_secret . $code );

		if ( ! is_numeric( $current_post_id ) ) {
			return $access_token;
		}

		if ( $saved_access_token = get_post_meta( $current_post_id, $token_meta_key, true ) ) {
			$access_token = $saved_access_token;
		} else {

			// Get access token
			$request = wp_remote_post(
				sprintf(
					'https://dribbble.com/oauth/token?client_id=%s&client_secret=%s&code=%s',
					$client_id,
					$client_secret,
					$code
				)
			);

			if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
				$response     = json_decode( wp_remote_retrieve_body( $request ) );
				$access_token = $response->access_token;
				update_post_meta( $current_post_id, $token_meta_key, $access_token );
			}
		}

		return $access_token;
	}

	/**
	 * Get User Shots
	 */
	public static function get_user_shots( $atts ) {
		// Attributes
		$atts = shortcode_atts( array(
			'count' => '',
			'client_id' => '',
			'client_secret' => '',
			'auth_code' => '',
		), $atts );

		// Counter
		static $count = 0;
		$count++;

		// Vars
		$shots_meta_key = '_dribbble_user_shots_' . md5( $atts['client_secret'] . $atts['auth_code'] . $count );
		$access_token = self::get_access_token( $atts['client_id'], $atts['client_secret'], $atts['auth_code'] );
		$transient_life = DAY_IN_SECONDS;

		// Get shots
		$shots = get_transient( $shots_meta_key );

		if ( $access_token && false === $shots ) {
			$request = wp_remote_get( 'https://api.dribbble.com/v2/user/shots', array(
				'headers' => array(
					'Authorization' => "Bearer {$access_token}",
				),
			) );

			if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
				$shots = json_decode( wp_remote_retrieve_body( $request ) );
			} else {
				$shots = new WP_Error( 'dribbble_bad_credentials', "Dribbble error: Bad credentials provided." );
			}

			set_transient( $shots_meta_key, $shots, $transient_life );
 		} else if ( empty( $access_token ) ) {
			$shots = new WP_Error( 'dribbble_no_access_token', "Dribbble error: No access token." );
		}

		return $shots;
	}
}