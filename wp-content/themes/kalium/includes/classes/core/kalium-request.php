<?php
/**
 * Kalium Request class
 *
 * Laborator.co
 * www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Request {

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Retrieve URL parameters as class instance property.
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function __get( $var ) {
		return $this->get( $var, null, $_GET );
	}

	/**
	 * Check if URL, or form input exists.
	 *
	 * @param string $var
	 * @param string $input_source
	 *
	 * @return bool
	 */
	public function has( $var, $input_source = 'get' ) {

		// Input source to check
		$check = &$_GET;

		// Supported input sources
		switch ( $input_source ) {

			// $_POST
			case 'post':
				$check = &$_POST;
				break;

			// $_REQUEST
			case 'request':
				$check = &$_REQUEST;
				break;

			// $_FILES
			case 'file':
				$check = &$_FILES;
				break;
		}

		return isset( $check[ $var ] );
	}

	/**
	 * Get form input.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function input( $var, $default = null ) {
		return $this->get( $var, $default, $_POST );
	}

	/**
	 * Get request parameter with primary check for camelCase var name.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function xhr_input( $var, $default = null ) {
		return $this->xhr_get( $var, $default, $_POST );
	}

	/**
	 * Get query string parameter.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function query( $var, $default = null ) {
		return $this->get( $var, $default, $_GET );
	}

	/**
	 * Get query string parameter with primary check for camelCase var name.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function xhr_query( $var, $default = null ) {
		return $this->xhr_get( $var, $default, $_GET );
	}

	/**
	 * Get request parameter.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function request( $var, $default = null ) {
		return $this->get( $var, $default, $_REQUEST );
	}

	/**
	 * Get request parameter with primary check for camelCase var name.
	 *
	 * @param string $var
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function xhr_request( $var, $default = null ) {
		return $this->xhr_get( $var, $default, $_REQUEST );
	}

	/**
	 * Get all parameters based on input source.
	 *
	 * @param string $input_source
	 *
	 * @return array
	 */
	public function all( $input_source = 'get' ) {
		$inputs = [];

		// $_GET inputs
		if ( 'get' === $input_source ) {
			$inputs = &$_GET;
		} // $_POST inputs
		else if ( 'post' === $input_source ) {
			$inputs = &$_POST;
		} // $_FILES inputs
		else if ( 'files' === $input_source ) {
			$inputs = &$_FILES;
		}


		return $inputs;
	}

	/**
	 * Get server var.
	 *
	 * @param string $var
	 *
	 * @return string|null
	 */
	public function server_var( $var ) {
		return $this->get( strtoupper( $var ), null, $_SERVER );
	}

	/**
	 * Get request method.
	 *
	 * @return string
	 */
	public function get_method() {
		return strtoupper( $this->server_var( 'REQUEST_METHOD' ) );
	}

	/**
	 * Check if request method matches.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_method( $type ) {
		return $this->get_method() === strtoupper( $type );
	}

	/**
	 * Get input variable from request.
	 *
	 * @param string $var
	 * @param null   $default
	 * @param array  $input_source
	 *
	 * @return mixed|null
	 */
	private function get( $var, $default = null, &$input_source = [] ) {

		// Default input source
		if ( ! is_array( $input_source ) ) {
			$input_source = &$_REQUEST;
		}

		// Request value
		if ( isset( $input_source[ $var ] ) ) {
			return $input_source[ $var ];
		}

		// Default value
		return $default;
	}

	/**
	 * Get input in XHR style with camelcase primary check then with dashes.
	 *
	 * @param string $var
	 * @param null   $default
	 * @param array  $input_source
	 *
	 * @return mixed|null
	 */
	private function xhr_get( $var, $default = null, &$input_source = [] ) {
		$camelcase_var = kalium()->helpers->dashes_to_camelcase( $var );
		$camelcase_var_value = $this->get( $camelcase_var, $default, $input_source );;

		if ( false === is_null( $camelcase_var_value ) ) {
			return $camelcase_var_value;
		}

		return $this->get( $var, $default, $input_source );
	}
}
