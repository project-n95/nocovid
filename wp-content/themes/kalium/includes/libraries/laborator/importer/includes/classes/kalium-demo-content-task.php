<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Task handler class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Task implements JsonSerializable {

	/**
	 * Task type.
	 *
	 * @var string
	 */
	protected $task_type;

	/**
	 * Task args.
	 *
	 * @var array
	 */
	protected $task_args = [];

	/**
	 * Task status.
	 *
	 * @var bool
	 */
	protected $task_completed = false;

	/**
	 * Constructor.
	 *
	 * @param string $task_type
	 * @param array  $task_args
	 */
	public function __construct( $task_type = '', $task_args = [] ) {

		// Task type
		$this->task_type = $task_type;

		// Task args
		if ( is_array( $task_args ) ) {
			$this->task_args = $task_args;
		}
	}

	/**
	 * Mark task as complete or incomplete.
	 *
	 * @param bool $new_status
	 *
	 * @return void
	 */
	public function mark_complete( $new_status ) {
		$this->task_completed = boolval( $new_status );
	}

	/**
	 * Serialize for JSON transport.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'type'      => $this->task_type,
			'args'      => $this->task_args,
			'completed' => $this->task_completed,
		];
	}
}