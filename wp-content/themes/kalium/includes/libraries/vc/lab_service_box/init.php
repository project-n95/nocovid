<?php
/**
 *	Featured Tab
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_service_box/services.svg' );

// Service Box (parent of icon box content and vc icon)
vc_map( array(
	"base"                     => "lab_service_box",
	"name"                     => "Service Box",
	"description"    		   => "Description with icon",
	"category"                 => 'Laborator',
	"content_element"          => true,
	"show_settings_on_create"  => false,
	"icon"                     => $lab_vc_element_icon,
	"as_parent"                => array('only' => 'vc_icon,lab_service_box_content'),
	"params"                   => array(
		array(
			"type"           => "textfield",
			"heading"        => "Extra class name",
			"param_name"     => "el_class",
			"description"    => "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file."
		),
		array(
            'type' => 'css_editor',
            'heading' => 'Css',
            'param_name' => 'css',
            'group' => 'Design options',
        ),
	),
	"js_view" => 'VcColumnView',
	'default_content' => '[vc_icon][lab_service_box_content title="Title here" description="I am text block. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo."]'
) );


# Box Content (child of Service Box)
vc_map( array(
	"base"             => "lab_service_box_content",
	"name"             => "Service Content",
	"description"      => "Describe your service",
	"category"         => 'Laborator',
	"content_element"  => true,
	"icon"			   => $lab_vc_element_icon,
	"as_child"         => array('only' => 'lab_service_box'),
	"params"           => array(
		array(
			'type'           => 'textfield',
			'heading'        => 'Title',
			'param_name'     => 'title',
			'admin_label'	 => true,
			'description'    => 'Title of the widget.',
		),
		array(
			'type'           => 'textarea',
			'heading'        => 'Description',
			'param_name'     => 'description',
			'description'    => 'Description about the service or the item you provide.',
		),
		array(
			'type'           => 'dropdown',
			'heading'        => 'Text alignment',
			'param_name'     => 'text_alignment',
			'std'            => 'left',
			'value'          => array(
				'Left'      => 'left',
				'Center'    => 'center',
				'Right'     => 'right',
			),
			'description' => 'Set text alignment for title and description.'
		),
		array(
			'type'           => 'vc_link',
			'heading'        => 'Link',
			'param_name'     => 'link',
			'description'    => 'Make the title clickable (Optional).',
		),
		array(
			"type"           => "textfield",
			"heading"        => "Extra class name",
			"param_name"     => "el_class",
			"description"    => "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file."
		),
		array(
            'type' => 'css_editor',
            'heading' => 'Css',
            'param_name' => 'css',
            'group' => 'Design options',
        ),
	)
) );


class WPBakeryShortCode_Lab_Service_Box extends WPBakeryShortCodesContainer {}
class WPBakeryShortCode_Lab_Service_Box_Content extends WPBakeryShortCode {}