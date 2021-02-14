<?php
/**
 * Kalium WordPress Theme
 *
 * Structured Data.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Structured_Data {

	/**
	 * Structured data holder.
	 *
	 * @var array
	 */
	private $data_entries = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', [ $this, 'output_structured_data' ], 10 );
	}

	/**
	 * Gets data from structured data holder.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data_entries;
	}

	/**
	 * Sets data to structured data holder.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function set_data( $data ) {
		if ( ! isset( $data['@type'] ) || ! preg_match( '|^[a-zA-Z]{1,20}$|', $data['@type'] ) ) {
			return false;
		}

		$this->data_entries[] = $data;

		return true;
	}

	/**
	 * Reset data entries.
	 *
	 * @return void
	 */
	public function reset_data() {
		unset( $this->data_entries );
		$this->data_entries = [];
	}

	/**
	 * Structures and returns data.
	 *
	 * @return array
	 */
	public function get_structured_data() {
		$data = [];

		// Group values of same type of structured data.
		foreach ( $this->get_data() as $value ) {
			$data[ strtolower( $value['@type'] ) ][] = $value;
		}

		// Wrap the multiple values of each type inside a graph... Then add context to each type.
		foreach ( $data as $type => $value ) {
			$data[ $type ] = count( $value ) > 1 ? [ '@graph' => $value ] : $value[0];
			$data[ $type ] = apply_filters( 'kalium_structured_data_context', [ '@context' => 'https://schema.org/' ], $data, $type, $value ) + $data[ $type ];
		}

		// Convert to array values
		$data = array_values( $data );

		if ( ! empty( $data ) ) {
			if ( 1 < count( $data ) ) {
				$data = apply_filters( 'kalium_structured_data_context', [ '@context' => 'https://schema.org/' ], $data, '', '' ) + [ '@graph' => $data ];
			} else {
				$data = $data[0];
			}
		}

		return $data;
	}

	/**
	 * Get data for current page.
	 *
	 * @return void
	 */
	public function get_data_for_page() {
		$this->generate_organization_data();

		/**
		 * Hook: kalium_structured_data_for_page.
		 */
		do_action( 'kalium_structured_data_for_page' );
	}

	/**
	 * Output sanitized structured data.
	 *
	 * @return void
	 */
	public function output_structured_data() {
		$this->get_data_for_page();

		if ( $data = $this->get_structured_data() ) {
			echo '<script type="application/ld+json">' . kalium()->helpers->esc_json( wp_json_encode( $data ), true ) . '</script>';
		}
	}

	/**
	 * Generate Organization data.
	 *
	 * @return void
	 */
	public function generate_organization_data() {
		$markup = [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url(),
		];

		// Logo image
		if ( kalium_get_theme_option( 'use_uploaded_logo' ) && ( $logo_image = wp_get_attachment_image_src( kalium_get_theme_option( 'custom_logo_image' ), 'full' ) ) ) {
			$markup['logo'] = $logo_image[0];
		}

		// Set organization data
		$this->set_data( apply_filters( 'kalium_structured_data_organization', $markup ) );
	}
}
