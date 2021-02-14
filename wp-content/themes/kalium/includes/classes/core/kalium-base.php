<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium base class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Kalium base class.
 */
abstract class Kalium_Base {

	/**
	 * Theme version.
	 *
	 * @var string
	 */
	const VERSION = '3.1.3';

	/**
	 * Get current theme version.
	 *
	 * @param bool $strip_dots
	 *
	 * @return string
	 */
	public function get_version( $strip_dots = false ) {
		if ( $strip_dots ) {
			return str_replace( '.', '', self::VERSION );
		}

		return self::VERSION;
	}

	/**
	 * Locate file in theme directory.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function locate_file( $relative_path = '' ) {
		$theme_relative_path = sprintf( '%s/%s', get_template_directory(), ltrim( $relative_path, '/' ) );

		if ( file_exists( $theme_relative_path ) ) {
			return $theme_relative_path;
		}

		return $relative_path;
	}

	/**
	 * Assets path.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function assets_path( $relative_path = '' ) {
		return $this->locate_file( sprintf( 'assets/%s', ltrim( $relative_path, '/' ) ) );
	}

	/**
	 * Locate url in theme directory.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function locate_file_url( $relative_path = '' ) {
		return sprintf( '%s/%s', get_template_directory_uri(), ltrim( $relative_path, '/' ) );
	}

	/**
	 * Assets url.
	 *
	 * @param string $relative_path
	 *
	 * @return string
	 */
	public function assets_url( $relative_path = '' ) {
		return $this->locate_file_url( sprintf( 'assets/%s', ltrim( $relative_path, '/' ) ) );
	}

	/**
	 * Require file once from theme base directory.
	 *
	 * @param string $relative_path
	 * @param array  $variables
	 *
	 * @return void
	 */
	public function require_file( $relative_path, $variables = [] ) {
		if ( is_array( $variables ) ) {
			extract( $variables );
		}

		require_once $this->locate_file( $relative_path );
	}

	/**
	 * Get relative theme directory.
	 *
	 * @param string $prepend
	 *
	 * @return string
	 */
	public function get_theme_dir( $prepend = '' ) {
		return str_replace( ABSPATH, $prepend, $this->locate_file() );
	}
}
