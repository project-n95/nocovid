<?php
/**
 * Kalium WordPress Theme
 *
 * WooCommerce products implementation for Kalium.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_WooCommerce_Products extends WC_Shortcode_Products {

	/**
	 * Initialize shortcode.
	 *
	 * @param array  $attributes
	 * @param string $type
	 */
	public function __construct( $attributes = [], $type = 'products' ) {
		$this->type       = $type;
		$this->attributes = $this->parse_attributes( $attributes );
		$this->query_args = $this->parse_query_args();
	}

	/**
	 * Query products.
	 *
	 * @param array $query_args
	 *
	 * @return array
	 */
	public function query_products( $query_args ) {
		$this->query_args = wp_parse_args( $query_args, $this->query_args );

		return $this->query_args;
	}
}