<?php
/**
 *    Inline hook value return
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_WP_Hook_Value {

	/**
	 * Return value.
	 *
	 * @var mixed
	 */
	public $value = '';

	/**
	 * Add array value.
	 *
	 * @var mixed
	 */
	public $array_value = '';

	/**
	 * Set array key for the value.
	 *
	 * @var string
	 */
	public $array_key = '';

	/**
	 * Call user function.
	 *
	 * @var string
	 */
	public $function_name = '';

	/**
	 * User function arguments.
	 *
	 * @var array
	 */
	public $function_args = [];

	/**
	 * Constructor.
	 *
	 * @param mixed $value
	 */
	public function __construct( $value = '' ) {
		if ( $value ) {
			$this->value = $value;
		}
	}

	/**
	 * Return value function generator for the hooks.
	 *
	 * @return mixed
	 */
	public function return_value() {
		return $this->value;
	}

	/**
	 * Echo value function generator for the hooks.
	 *
	 * @return void
	 */
	public function echo_value() {
		echo $this->value;
	}

	/**
	 * Concat a string value.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function concat_string_value( $value ) {
		return $value . $this->value;
	}

	/**
	 * Merge array value.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function merge_array_value( $array ) {
		if ( ! empty( $this->array_value ) ) {
			if ( $this->array_key ) {
				$array[ $this->array_key ] = $this->array_value;
			} else {
				$array[] = $this->array_value;
			}
		}

		return $array;
	}

	/**
	 * Merge two arrays.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function merge_arrays( $array ) {
		if ( ! empty( $this->array ) && is_array( $array ) ) {
			$array = array_merge( $array, $this->array );
		}

		return $array;
	}

	/**
	 * Execute user function.
	 *
	 * @return void
	 */
	public function call_user_function() {
		if ( ! empty( $this->function_name ) ) {
			call_user_func_array( $this->function_name, $this->function_args );
		}
	}
}
