<?php
/**
 *	Heading Title
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_heading/heading.svg' );

vc_map( array(
	'base'             => 'lab_heading',
	'name'             => 'Heading',
	"description"      => "Title and description",
	'category'         => 'Laborator',
	'icon'             => $lab_vc_element_icon,
	'params' => array(
		array(
			'type'           => 'dropdown',
			'heading'        => 'Title Tag',
			'param_name'     => 'title_tag',
			'admin_label'    => true,
			'std'			 => 'H2',
			'value'          => array(
				'H1', 'H2', 'H3', 'H4', 'H5', 'H6'
			),
			'description' 	=> 'Set heading title container tag for SEO purpose.'
		),
		array(
			'type'           => 'textfield',
			'heading'        => 'Title',
			'param_name'     => 'title',
			'admin_label'    => true,
			'value'          => 'Heading title'
		),
		array(
			'type'       => 'textarea',
			'heading'    => 'Content',
			'param_name' => 'content',
			'value'      => 'Enter your description about the heading title here.'
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

class WPBakeryShortCode_Lab_Heading extends WPBakeryShortCode {}