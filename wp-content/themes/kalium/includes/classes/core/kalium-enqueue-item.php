<?php
/**
 * Kalium WordPress Theme
 *
 * Enqueue item entry.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Kalium_Enqueue_Item class.
 */
class Kalium_Enqueue_Item {

	/**
	 * Unmodified handle.
	 *
	 * @var string
	 */
	public $raw_handle = '';

	/**
	 * Handle.
	 *
	 * @var string
	 */
	public $handle = '';

	/**
	 * Args.
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * Constructor.
	 *
	 * @param string       $handle
	 * @param array|string $args
	 */
	public function __construct( $handle, $args = [] ) {
		$this->raw_handle = $handle;
		$this->handle     = apply_filters( 'kalium_enqueue_handle', "kalium-${handle}", $handle );
		$this->args       = $this->parse_args( $args );
	}

	/**
	 * Parse args value from string or array.
	 *
	 * @param array|string $args
	 *
	 * @return array
	 */
	public function parse_args( $args ) {
		$src     = '';
		$deps    = [];
		$version = kalium()->get_version();

		// Parse from string
		if ( is_string( $args ) ) {
			$src = $this->parse_src( $args );

			// Version
			if ( $parsed_version = $this->parse_version( $args ) ) {
				$version = $parsed_version;
			}

			// Default 'in_footer'
			$in_footer = $this->is_style( $src ) ? false : true;
		} else {
			$args = wp_parse_args( $args, [
				'src'       => null,
				'deps'      => null,
				'version'   => null,
				'in_footer' => null,
			] );

			// Source
			if ( is_string( $args['src'] ) ) {
				$src = $this->parse_src( $args['src'] );
			}

			// Dependencies
			if ( is_array( $args['deps'] ) ) {
				$deps = $args['deps'];
			} else if ( is_string( $args['deps'] ) ) {
				$deps = explode( ',', $args['deps'] );
			}

			// Version
			if ( is_string( $args['version'] ) ) {
				$version = $args['version'];
			}

			// In footer
			if ( is_bool( $args['in_footer'] ) ) {
				$in_footer = $args['in_footer'];
			} else {
				$in_footer = $this->is_style( $src ) ? false : true;
			}
		}

		return [
			'src'       => $src,
			'deps'      => $deps,
			'version'   => $version,
			'in_footer' => $in_footer,
		];
	}

	/**
	 * Check if source is CSS type.
	 *
	 * @param string $src
	 *
	 * @return bool
	 */
	public function is_style( $src = '' ) {
		if ( empty( $src ) ) {
			$src = kalium_get_array_key( $this->args, 'src' );
		}

		return wp_validate_boolean( preg_match( '/\.css$/i', $src ) );
	}

	/**
	 * Get source URL.
	 *
	 * @return string
	 */
	public function get_src() {
		return kalium_get_array_key( $this->args, 'src', '' );
	}

	/**
	 * Parse source path.
	 *
	 * @param string $src
	 *
	 * @return string
	 */
	private function parse_src( $src ) {
		$parsed_src = '';

		// Relative path
		if ( '/' === substr( $src, 0, 1 ) ) {
			$src = substr( $src, 1 );
		} else if ( 'http' !== substr( $src, 0, 4 ) ) {
			$parsed_src .= 'assets/';
		}

		// Append src
		$parsed_src .= $src;

		// If its local asset
		if ( 0 !== strpos( $parsed_src, 'http' ) ) {
			$parsed_src = kalium()->locate_file_url( $parsed_src );
		}

		return preg_replace( '/@[\d\.]+$/', '', $parsed_src );
	}

	/**
	 * Parse version from string or array.
	 *
	 * @param array|string $version
	 *
	 * @return string|null
	 */
	private function parse_version( $version ) {
		if ( is_array( $version ) && isset( $version['version'] ) ) {
			return $version['version'];
		} else if ( is_string( $version ) && preg_match( '/@([\d\.]+)$/', $version, $matches ) ) {
			return $matches[1];
		}

		return null;
	}
}
