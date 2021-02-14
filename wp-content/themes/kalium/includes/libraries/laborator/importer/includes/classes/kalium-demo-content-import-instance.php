<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Import Instance class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Kalium_Demo_Import_Instance class.
 */
class Kalium_Demo_Import_Instance {

	/**
	 * Option holder for Import Instances.
	 *
	 * @var string
	 */
	const IMPORT_INSTANCES_OPTION_NAME = 'kalium_demos_import_instances';

	/**
	 * Import instance ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Current working import ID to use with import action tasks.
	 *
	 * @var string
	 */
	private $import_id;

	/**
	 * First created timestamp.
	 *
	 * @var int
	 */
	private $timestamp;

	/**
	 * Install state of content pack.
	 *
	 * @var bool
	 */
	private $installed;

	/**
	 * Imports list.
	 *
	 * @var array
	 */
	private $imports = [];

	/**
	 * Content pack instance.
	 *
	 * @var Kalium_Demo_Content_Pack
	 */
	private $content_pack;

	/**
	 * Constructor.
	 *
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return void
	 */
	public function __construct( $content_pack ) {

		// Assign content pack
		$this->content_pack = $content_pack;

		// Initialize value
		$this->initialize_value();
	}

	/**
	 * Get import instance ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get imported content type.
	 *
	 * @return array
	 */
	public function get_imported_content_type() {
		$imported_content_entries = [];

		foreach ( $this->imports as $import_id => $imported_content ) {

			// Only imported content type
			if ( $imported_content['success'] ) {
				if ( $import = $this->content_pack->get_import_by_id( $import_id ) ) {
					$imported_content_entries[ $import_id ] = [
						'id'   => $import->get_import_id(),
						'name' => $import->get_name(),
						'type' => $import->get_type(),
					];
				}
			}
		}

		return $imported_content_entries;
	}

	/**
	 * Check if content type is installed.
	 *
	 * @return bool
	 */
	public function is_installed() {
		return $this->installed;
	}

	/**
	 * Set install state of import instance.
	 *
	 * @param bool|string $new_state
	 *
	 * @return void
	 */
	public function set_install_state( $new_state ) {
		$this->installed = wp_validate_boolean( $new_state );
		$this->update_option();
	}

	/**
	 * Point current working import ID.
	 *
	 * @param string $import_id
	 *
	 * @return void
	 */
	public function set_import_id( $import_id ) {
		$this->import_id = $import_id;
	}

	/**
	 * Import task action: Clear errors.
	 *
	 * @return void
	 */
	public function clear_errors() {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return;
		}

		$this->imports[ $this->import_id ]['errors'] = [];

		$this->update_option();
	}

	/**
	 * Import task action: Clear task args.
	 *
	 * @return void
	 */
	public function clear_task_args() {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return;
		}

		$this->imports[ $this->import_id ]['tasks'] = [];

		$this->update_option();
	}

	/**
	 * Import task action: Add error.
	 *
	 * @param string|int|WP_Error $code
	 * @param string              $message
	 * @param mixed               $data
	 *
	 * @return void
	 */
	public function add_error( $code = '', $message = '', $data = '' ) {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return;
		}

		// Extract WP_Error to function args vars
		if ( $code instanceof WP_Error ) {
			$message = $code->get_error_message();
			$data    = $code->get_error_data();
			$code    = $code->get_error_code();
		}

		$this->imports[ $this->import_id ]['errors'][] = [
			'code'    => $code,
			'message' => $message,
			'data'    => $data,
		];

		$this->update_option();
	}

	/**
	 * Import task action: Set task success status.
	 *
	 * @param bool $completed
	 */
	public function set_successful( $completed ) {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return;
		}

		$this->imports[ $this->import_id ]['success'] = boolval( $completed );

		$this->update_option();;
	}

	/**
	 * Import task action: Get task args values.
	 *
	 * @param string $task_id
	 *
	 * @return array|false
	 */
	public function get_task_args( $task_id ) {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return false;
		}

		if ( isset( $this->imports[ $this->import_id ]['tasks'][ $task_id ] ) ) {
			return $this->imports[ $this->import_id ]['tasks'][ $task_id ];
		}

		return false;
	}

	/**
	 * Import task action: Set task arg value.
	 *
	 * @param string $task_id
	 * @param string $arg_name
	 * @param mixed  $arg_value
	 *
	 * @return void
	 */
	public function set_task_arg_value( $task_id, $arg_name, $arg_value ) {
		if ( ! isset( $this->imports[ $this->import_id ] ) ) {
			return;
		}

		if ( empty( $this->imports[ $this->import_id ]['tasks'][ $task_id ] ) ) {
			$this->imports[ $this->import_id ]['tasks'][ $task_id ] = [];
		}

		// Set arg value for given task ID
		if ( ! is_null( $arg_value ) ) {
			$this->imports[ $this->import_id ]['tasks'][ $task_id ][ $arg_name ] = $arg_value;
		} else if ( isset( $this->imports[ $this->import_id ]['tasks'][ $task_id ][ $arg_name ] ) ) {
			unset( $this->imports[ $this->import_id ]['tasks'][ $task_id ][ $arg_name ] );
		}

		$this->update_option();
	}

	/**
	 * Import task action: Set download success arg value.
	 *
	 * @return void
	 */
	public function set_download_success() {
		$this->set_task_arg_value( 'download', 'success', true );
	}

	/**
	 * Import task action: Set download success arg value.
	 *
	 * @return void
	 */
	public function set_import_success() {
		$this->set_task_arg_value( 'import', 'success', true );
	}

	/**
	 * Return import instance value as array.
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'id'        => $this->id,
			'timestamp' => $this->timestamp,
			'installed' => $this->installed,
			'imports'   => $this->imports,
		];
	}

	/**
	 * Initialize instance value.
	 *
	 * @return void
	 */
	private function initialize_value() {

		// Import instances from options
		$import_instances = $this->get_option();
		$content_pack     = $this->content_pack;
		$content_pack_id  = $content_pack->get_id();

		// New instance
		if ( ! isset( $import_instances[ $content_pack_id ] ) ) {

			// Create import instance ID
			$this->id = $this->generate_id();

			// Timestamp
			$this->timestamp = time();

			// Register current imports of content pack
			foreach ( $content_pack->get_imports() as $import ) {
				$this->imports[ $import->get_import_id() ] = [
					'type'    => $import->get_type(),
					'success' => false,
					'errors'  => [],
					'tasks'   => [],
				];
			}

			// Save import instance
			$this->update_option();
		} // Update existing instance
		else {

			// Import instance
			$import_instance = kalium_get_array_key( $import_instances, $content_pack_id );

			// Init id
			$this->id = kalium_get_array_key( $import_instance, 'id' );

			// Init creation time
			$this->timestamp = kalium_get_array_key( $import_instance, 'timestamp' );

			// Set installed state
			$this->installed = kalium_get_array_key( $import_instance, 'installed', false );

			// Set import values
			foreach ( $content_pack->get_imports() as $import ) {

				// Import ID
				$import_id = $import->get_import_id();

				// Set existing value from option
				if ( isset( $import_instance['imports'], $import_instance['imports'][ $import_id ] ) ) {
					$this->imports[ $import_id ] = $import_instance['imports'][ $import_id ];
				} else {

					// If import is new entry
					$this->imports[ $import_id ] = [
						'type'    => $import->get_type(),
						'success' => false,
						'errors'  => [],
						'tasks'   => [],
					];

					// Save import instance
					$this->update_option();
				}
			}
		}
	}

	/**
	 * Generate import instance ID.
	 *
	 * @return string
	 */
	private function generate_id() {
		return sprintf( '%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
	}

	/**
	 * Get option value from database.
	 *
	 * @return array
	 */
	private function get_option() {
		return get_option( self::IMPORT_INSTANCES_OPTION_NAME, [] );
	}

	/**
	 * Update option value.
	 *
	 * @return void
	 */
	private function update_option() {

		// Import instances from database option
		$import_instances = $this->get_option();
		$content_pack_id  = $this->content_pack->get_id();

		// Create instance entry
		if ( ! isset( $import_instances[ $content_pack_id ] ) ) {
			$import_instances[ $content_pack_id ] = [];
		}

		// Apply instance data to import instance
		$import_instances[ $content_pack_id ] = array_merge( $import_instances[ $content_pack_id ], $this->to_array() );

		// Update option value
		update_option( self::IMPORT_INSTANCES_OPTION_NAME, $import_instances );
	}
}
