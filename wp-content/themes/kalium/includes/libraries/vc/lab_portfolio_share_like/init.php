<?php
/**
 *	Portfolio Share and Like button
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_portfolio_share_like/like-social-sharing.svg' );

if ( is_admin() && ( $post_id = kalium()->request->query( 'post' ) ) ) {
	$wp_post = get_post( $post_id );
	
	if ( $wp_post && $wp_post->post_type != 'portfolio' ) { 
		return;
	}
}

vc_map( array(
	'base'             => 'lab_portfolio_share_like',
	'name'             => 'Like + Share',
	"description"      => "Portfolio item social sharing links",
	'category'         => array( 'Laborator', 'Portfolio' ),
	'icon'             => $lab_vc_element_icon,
	'params' => array(
		array(
			'type'           => 'dropdown',
			'heading'        => 'Layout',
			'param_name'     => 'layout',
			'std'            => 'default',
			'value'          => array(
				'Plain text'    => 'default',
				'Rounded icons' => 'rounded',
			),
			'admin_label' => true,
			'description' => 'Select layout of social sharing links.'
		),
		array(
			'type'           => 'dropdown',
			'heading'        => 'Alignment',
			'param_name'     => 'alignment',
			'std'            => 'center',
			'value'          => array(
				'Left'      => 'left',
				'Center'    => 'center',
				'Right'     => 'right',
			),
			'admin_label' => true,
			'description' => 'Set alignment of social media links inside the column.'
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

class WPBakeryShortCode_Lab_Portfolio_Share_Like extends WPBakeryShortCode {}