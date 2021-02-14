<?php
/**
 *    Google Map
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Atts
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );

extract( $atts );

$map_id = uniqid( 'el_' );

$map_options  = explode( ',', $map_options );
$map_controls = explode( ',', $map_controls );

$map_style = html_entity_decode( vc_value_from_safe( $map_style ), ENT_QUOTES, 'utf-8' );

// Older version of Visual Composer parameter
if ( ! empty( $atts['map_style'] ) && strpos( $map_style, '[' ) === false ) {
	$map_style = rawurldecode( base64_decode( strip_tags( $map_style ) ) );
}

$height = is_numeric( $height ) && $height > 10 ? $height : 400;

$map_locations = array();

if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $map_locations_match ) ) {

	foreach ( $map_locations_match[0] as $location ) {
		$location         = preg_replace( "/^\[[^\s]+/i", "", substr( $location, 0, - 1 ) );
		$location_details = $this->prepareAtts( shortcode_parse_atts( $location ) );

		$location_details = shortcode_atts( array(
			'marker_image'       => '',
			'retina_marker'      => '',
			'latitude'           => '0',
			'longitude'          => '0',
			'marker_title'       => '',
			'marker_description' => '',
		), $location_details );

		if ( $location_details['marker_image'] ) {
			$pin = wp_get_attachment_image_src( $location_details['marker_image'], 'original' );

			if ( $pin ) {
				$location_details['marker_image']      = $pin[0];
				$location_details['marker_image_size'] = array( $pin[1], $pin[2] );
			}
		} else {
			$location_details['marker_image']      = kalium()->assets_url( 'images/icons/map/cd-icon-location.svg' );
			$location_details['marker_image_size'] = array( 44, 44 );
		}

		// When Description is "Safe Textarea"
		$marker_description_safe = vc_value_from_safe( $location_details['marker_description'] );

		if ( strpos( $location_details['marker_description'], '#E-' ) == 0 ) {
			$location_details['marker_description'] = $marker_description_safe;
		}

		$location_details['marker_description'] = wp_kses_post( wpautop( $location_details['marker_description'] ) );

		$map_locations[] = $location_details;
	}
}

// Pan By
$map_panby = explode( ',', $map_panby );

if ( ! is_numeric( $map_panby[0] ) ) {
	$map_panby[0] = 0;
}

if ( ! isset( $map_panby[1] ) ) {
	$map_panby[1] = 0;
}

if ( ! in_array( 'pan-by', $map_options ) ) {
	$map_panby = array( 0, 0 );
}

// Element Class
$class     = $this->getExtraClass( $el_class );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class, $this->settings['base'], $atts );

$css_class = "lab-google-map cd-google-map {$css_class}";

if ( in_array( 'fullwidth', $map_options ) ) {
	$css_class .= ' full-width-container';
}

// Enqueue Google Maps
if ( is_singular() ) {
	wp_enqueue_script( 'kalium-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . kalium_get_google_api(), [], kalium()->get_version(), true );
	wp_enqueue_script( 'lab-vc-google-maps' );
}

// Map height
echo sprintf( '<style> #%s { height: %dpx; } </style>', esc_attr( $map_id ), $height );
?>
<div class="<?php echo esc_attr( $css_class ) . vc_shortcode_custom_css_class( $css, ' ' ); ?>">
    <div id="<?php echo esc_attr( $map_id ); ?>"></div>
    <div class="cd-zoom cd-zoom-in hidden"></div>
    <div class="cd-zoom cd-zoom-out hidden"></div>
</div>

<script type="text/javascript">
	var labVcMaps = labVcMaps || [];
	labVcMaps.push( {
		id: '<?php echo esc_js( $map_id ); ?>',

		locations: <?php echo json_encode( $map_locations ); ?>,

		zoom: <?php echo is_numeric( $zoom ) && $zoom > 0 ? intval( $zoom ) : 0; ?>,
		scrollwheel: <?php echo in_array( 'scroll-zoom', $map_options ) ? 'true' : 'false'; ?>,
		dropPins: <?php echo in_array( 'drop-pins', $map_options ) ? 'true' : 'false'; ?>,
		panBy: <?php echo json_encode( $map_panby ); ?>,
		tilt: <?php echo intval( in_array( $map_type, array( 'satellite', 'hybrid' ) ) ? $map_tilt : 0 ); ?>,
		heading: <?php echo intval( $map_heading ); ?>,

		mapType: '<?php echo esc_js( $map_type ) ?>',

		panControl: <?php echo in_array( 'panControl', $map_controls ) ? 'true' : 'false'; ?>,
		zoomControl: <?php echo in_array( 'zoomControl', $map_controls ) ? 'true' : 'false'; ?>,
		mapTypeControl: <?php echo in_array( 'mapTypeControl', $map_controls ) ? 'true' : 'false'; ?>,
		scaleControl: <?php echo in_array( 'scaleControl', $map_controls ) ? 'true' : 'false'; ?>,
		streetViewControl: <?php echo in_array( ' streetViewControl', $map_controls ) ? 'true' : 'false'; ?>,
		overviewMapContro: <?php echo in_array( 'overviewMapControl', $map_controls ) ? 'true' : 'false'; ?>,
		plusMinusZoom: <?php echo in_array( 'plusMinusZoom', $map_controls ) ? 'true' : 'false'; ?>,
		fullscreenControl: <?php echo in_array( 'fullscreenControl', $map_controls ) ? 'true' : 'false'; ?>,


		styles: <?php echo in_array( 'map-style', $map_options ) && $map_style ? $map_style : "''"; ?>
	} );
</script>