<?php
/**
 * ACF fallback for "get_field"
 *
 * Laborator.co
 * www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_ACF {

	/**
	 * ACF plugin is active.
	 *
	 * @var bool
	 */
	public $acf_active = false;

	/**
	 * Construct.
	 *
	 * @return void
	 */
	public function __construct() {

		// Check if ACF is installed and activated
		$this->acf_active = kalium()->is->acf_active();

		// Import ACF related config and libs
		if ( $this->acf_active ) {

			if ( ! defined( 'KALIUM_ACF_DEV' ) ) {

				// Custom Fields import
				kalium()->require_file( 'includes/acfpro-fields.php' );
			}

			// ACF libs
			kalium()->require_file( 'includes/libraries/laborator/grouped-metaboxes/grouped-metaboxes.php' );
			kalium()->require_file( 'includes/libraries/acf-revslider-field.php' );
		}
	}

	/**
	 * Get field with fallback function.
	 *
	 * @param string $field_key
	 * @param bool   $post_id
	 * @param bool   $format_value
	 *
	 * @return mixed|null
	 */
	public function get_field( $field_key, $post_id = false, $format_value = true ) {
		global $post;

		if ( $this->acf_active ) {
			return get_field( $field_key, $post_id, $format_value );
		}

		// Get raw field from post
		if ( ! $post_id && $post instanceof WP_Post ) {
			$post_id = $post->ID;
		}

		// Get from post meta
		if ( $post_id ) {
			return get_post_meta( $post_id, $field_key, true );
		}

		return null;
	}
}
