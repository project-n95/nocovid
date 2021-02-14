<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Backup Manager class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Backup_Manager {

	/**
	 * Backup option data.
	 *
	 * @var string
	 */
	const BACKUP_DATA_OPTION_NAME = 'kalium_demos_backup_data';

	/**
	 * Instance of this class.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Content pack reference.
	 *
	 * @var Kalium_Demo_Content_Pack
	 */
	private $content_pack;

	/**
	 * Instance creator and getter.
	 *
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return self
	 */
	public static function get_instance( $content_pack = null ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		// Set content pack in use
		if ( $content_pack instanceof Kalium_Demo_Content_Pack ) {
			self::$instance->set_content_pack( $content_pack );
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Get content pack.
	 *
	 * @return Kalium_Demo_Content_Pack|null
	 */
	public function get_content_pack() {
		return $this->content_pack;
	}

	/**
	 * Set content pack currently in use.
	 *
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return void
	 */
	public function set_content_pack( $content_pack ) {
		if ( $content_pack instanceof Kalium_Demo_Content_Pack ) {
			$this->content_pack = $content_pack;
		} else {
			kalium_doing_it_wrong( __FUNCTION__, 'Backup manager requires an instance of `Kalium_Demo_Content_Pack` as content pack!', '3.0' );
		}
	}

	/**
	 * Get import manager instance from the current content pack.
	 *
	 * @return Kalium_Demo_Import_Manager|null
	 */
	public function get_import_manager() {
		if ( ! isset( $this->content_pack ) ) {
			return null;
		}

		return $this->get_content_pack()->import_manager();
	}

	/**
	 * Get option data from database.
	 *
	 * @return array
	 */
	public function get_option() {
		return get_option( self::BACKUP_DATA_OPTION_NAME, [] );
	}

	/**
	 * Get option key for the current instance.
	 *
	 * @return string
	 */
	public function get_option_key() {
		if ( ( $import_manager = $this->get_import_manager() ) && ( $import_instance = $import_manager->get_import_instance() ) ) {
			return $import_instance->get_id();
		}

		return 'global';
	}

	/**
	 * Get backup options from current content pack and import instance ID.
	 *
	 * @return array
	 */
	public function get_backup_options() {
		$option          = $this->get_option();
		$content_pack_id = $this->get_content_pack()->get_id();
		$option_key      = $this->get_option_key();
		$options         = [];

		// Get global value of backup options
		if ( isset( $option['global'] ) ) {
			$options = array_merge( $options, $option['global'] );
		}

		// Get local value of backup options
		if ( isset( $option[ $content_pack_id ], $option[ $content_pack_id ][ $option_key ] ) ) {
			$options = array_merge( $options, $option[ $content_pack_id ][ $option_key ] );
		}

		return $options;
	}

	/**
	 * Delete backup options from current content pack and import instance ID.
	 *
	 * @return bool
	 */
	public function delete_backup_options() {
		$option          = $this->get_option();
		$content_pack_id = $this->get_content_pack()->get_id();
		$option_key      = $this->get_option_key();

		if ( isset( $option[ $content_pack_id ], $option[ $content_pack_id ][ $option_key ] ) ) {
			unset( $option[ $content_pack_id ][ $option_key ] );

			// Update option value
			update_option( self::BACKUP_DATA_OPTION_NAME, $option );

			return true;
		}

		return false;
	}

	/**
	 * Get backup option from current content pack and import instance ID or from global scope.
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get_backup_option( $name, $default = null ) {

		// Check if option exists to return it
		if ( $this->has_backup_option( $name ) ) {
			$option          = $this->get_option();
			$content_pack_id = $this->get_content_pack()->get_id();
			$option_key      = $this->get_option_key();

			// Get local value of backup option
			if ( isset( $option[ $content_pack_id ], $option[ $content_pack_id ][ $option_key ], $option[ $content_pack_id ][ $option_key ][ $name ] ) ) {
				return $option[ $content_pack_id ][ $option_key ][ $name ];
			}

			// Get global value of backup option
			if ( isset( $option['global'][ $name ] ) ) {
				return $option['global'][ $name ];
			}
		}

		return $default;
	}

	/**
	 * Update (or set) backup option for current content pack and import instance ID or from global scope.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param bool   $global_scope
	 *
	 * @return bool Returns true if option is updated, false if option is newly added.
	 */
	public function update_backup_option( $name, $value = null, $global_scope = false ) {
		$option          = $this->get_option();
		$content_pack_id = $this->get_content_pack()->get_id();
		$option_key      = $this->get_option_key();

		// Global scope
		if ( $global_scope ) {

			// Global options store array
			if ( ! isset( $option['global'] ) ) {
				$option['global'] = [];
			}

			// Whether option is updated or set as new
			$updated = isset( $option['global'][ $name ] );

			// Set option value
			$option['global'][ $name ] = $value;
		} else {

			// Define content pack ID to store backup data
			if ( ! isset( $option[ $content_pack_id ] ) ) {
				$option[ $content_pack_id ] = [];
			}

			// Define option key for current import instance
			if ( ! isset( $option[ $content_pack_id ][ $option_key ] ) ) {
				$option[ $content_pack_id ][ $option_key ] = [];
			}

			// Whether option is updated or set as new
			$updated = isset( $option[ $content_pack_id ][ $option_key ][ $name ] );

			// Set option value
			$option[ $content_pack_id ][ $option_key ][ $name ] = $value;
		}

		// Save option
		update_option( self::BACKUP_DATA_OPTION_NAME, $option );

		return $updated;
	}

	/**
	 * Check if backup option exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has_backup_option( $name ) {
		$option          = $this->get_option();
		$content_pack_id = $this->get_content_pack()->get_id();
		$option_key      = $this->get_option_key();

		// Check if option exists locally
		if ( isset( $option[ $content_pack_id ], $option[ $content_pack_id ][ $option_key ], $option[ $content_pack_id ][ $option_key ][ $name ] ) ) {
			return true;
		}

		// Check if option exists globally
		if ( isset( $option['global'], $option['global'][ $name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete backup option from current content pack and import instance ID or from global scope.
	 *
	 * @param string $name
	 * @param bool   $global_scope
	 *
	 * @return bool Returns true if option is deleted.
	 */
	public function delete_backup_option( $name, $global_scope = false ) {

		// Check if option already exists
		if ( $this->has_backup_option( $name ) ) {
			$option          = $this->get_option();
			$content_pack_id = $this->get_content_pack()->get_id();
			$option_key      = $this->get_option_key();

			// Unset global backup option
			if ( $global_scope ) {
				unset( $option['global'][ $name ] );

			} // Unset local backup option
			else {
				unset( $option[ $content_pack_id ][ $option_key ][ $name ] );
			}

			// Update option value
			update_option( self::BACKUP_DATA_OPTION_NAME, $option );

			return true;
		}

		return false;
	}

	/**
	 * Set backup option once.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param bool   $global_scope
	 *
	 * @return bool Returns true if option is added, false if option not added.
	 */
	public function set_backup_option_once( $name, $value = null, $global_scope = false ) {
		$option_exists = $this->has_backup_option( $name );

		if ( ! $option_exists ) {
			$this->update_backup_option( $name, $value, $global_scope );

			return true;
		}

		return false;
	}
}
