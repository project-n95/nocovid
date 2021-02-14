<?php
/**
 *	Laborator Social Networks
 *	
 *	Laborator.co
 *	www.laborator.co 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Element Information
$lab_vc_element_icon = kalium()->locate_file_url( 'includes/libraries/vc/lab_vc_social_networks/social-networks.svg' );

vc_map( array(
	'base'             => 'lab_vc_social_networks',
	'name'             => 'Social Networks',
	"description"      => "Social network links",
	'category'         => 'Laborator',
	'icon'             => $lab_vc_element_icon,
	'params' => array(
		array(
			'type'       => 'dropdown',
			'heading'    => 'Display Type',
			'param_name' => 'display_type',
			'std'		 => 'no',
			'value'      => array(
				'Rounded icons'  => 'rounded-icons',
				'Text only'      => 'text-only',
				'Icon + text'    => 'icon-text',
			),
			'description' => 'Select style of social network links.',
			'admin_label' => true,
		),
		array(
			'type'       => 'dropdown',
			'heading'    => 'Colored',
			'param_name' => 'colored',
			'std'		 => 'no',
			'value'      => array(
				'Colored text'                  => 'text',
				'Colored text on hover'         => 'text-hover',
				'Colored background'            => 'bg',
				'Colored background on hover'   => 'bg-hover',
				'None'							=> 'none'
			),
			'description' => 'Use colored social networks.',
			'admin_label' => true,
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

class WPBakeryShortCode_Lab_VC_Social_Networks extends WPBakeryShortCode {
	
	public function content( $atts, $content = null ) {
		// Atts
		$defaults = array(
			'display_type'   => '',
			'el_class'       => '',
			'colored'		 => '',
			'css'            => ''
		);
		
		$atts = vc_shortcode_attribute_parse( $defaults, $atts );
		
		extract( $atts );

		// Element Class
		$class = $this->getExtraClass( $el_class );
		
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );		
		$css_class = "lab-vc-social-networks {$css_class} display-type-{$display_type}";
		
		$colored_attr = '';
		
		switch ( $colored ) {
			case 'text':
				$colored_attr = 'colored';
				break;
				
			case 'text-hover':
				$colored_attr = 'colored="hover"';
				break;
				
			case 'bg':
				$colored_attr = 'colored-bg';
				break;
				
			case 'bg-hover':
				$colored_attr = 'colored-bg="hover"';
				break;
		}

		ob_start();
		
		?>
		<div class="<?php echo esc_attr( $css_class ) . vc_shortcode_custom_css_class( $css, ' ' ); ?>">
		<?php echo do_shortcode( '[lab_social_networks' . when_match( $display_type == 'rounded-icons', 'rounded', '', false ) . when_match( ! empty( $colored_attr ), $colored_attr, '', false ) . ']' ); ?>
		</div>
		<?php
		
		$output = ob_get_clean();
		
		return $output;
	}
}
