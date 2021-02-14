<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - Theme Options class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_Theme_Options extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Theme Options';
	}

	/**
	 * Backup current theme options.
	 *
	 * @return void
	 */
	public function do_backup() {

		// Vars
		$import_manager       = $this->get_content_pack()->import_manager();
		$backup_manager       = $this->get_content_pack()->backup_manager();
		$theme_options_backup = [];

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Theme options
			$theme_options_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if theme options file exists
			if ( true === kalium()->filesystem->exists( $theme_options_file ) ) {
				$theme_options = maybe_unserialize( kalium()->filesystem->get_contents( $theme_options_file ) );

				// Register theme options
				if ( is_array( $theme_options ) ) {
					foreach ( $theme_options as $theme_option => $value ) {
						$theme_options_backup[ $theme_option ] = null;
					}
				}
			}
		}

		// Store current values
		foreach ( $theme_options_backup as $theme_option => & $value ) {
			$value = get_theme_mod( $theme_option );
		}

		// Save backup option
		$backup_manager->set_backup_option_once( 'theme_options', $theme_options_backup );
	}

	/**
	 * Import theme options.
	 *
	 * @return void
	 */
	public function do_import() {

		// Execute parent do_import
		parent::do_import();

		// Do not run if there are errors reported or option is unchecked
		if ( $this->errors->has_errors() || ! $this->is_checked() ) {
			return;
		}

		// Vars
		$content_pack    = $this->get_content_pack();
		$import_manager  = $content_pack->import_manager();
		$import_instance = $content_pack->get_import_instance();

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Theme options
			$theme_options_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if theme options file exists
			if ( true === kalium()->filesystem->exists( $theme_options_file ) ) {
				$theme_options_export = kalium()->filesystem->get_contents( $theme_options_file );

				// Check for base64 encoded string
				if ( ! is_serialized( $theme_options_export ) ) {
					$theme_options_export = base64_decode( $theme_options_export );
				}

				// Unserialize the string
				$theme_options = maybe_unserialize( $theme_options_export );

				// Register theme options
				if ( is_array( $theme_options ) ) {

					// Set theme mods
					foreach ( $theme_options as $option_name => $option_value ) {
						set_theme_mod( $option_name, $option_value );
					}

					// Flush rewrite rules
					flush_rewrite_rules( true );

					// Delete skin file (this will require to regenerate it)
					kalium()->filesystem->delete( kalium_get_custom_skin_file_path( true ) );

					// Mark as successful import
					$import_instance->set_import_success();
				} else {
					$this->errors->add( 'kalium_demo_content_theme_options_not_valid', 'Theme options import file is not valid!' );
				}

			} else {

				// Theme options file doesn't exists
				$this->errors->add( 'kalium_demo_content_theme_options_not_exists', 'Theme options file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Restore previous theme options.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Vars
		$backup_manager         = $this->get_content_pack()->backup_manager();
		$previous_theme_options = $backup_manager->get_backup_option( 'theme_options' );

		// Restore theme options
		if ( is_array( $previous_theme_options ) ) {
			foreach ( $previous_theme_options as $theme_option => $value ) {
				set_theme_mod( $theme_option, $value );
			}
		}

		// Flush rewrite rules
		flush_rewrite_rules( true );

		// Mark as removed
		parent::do_remove();
	}
}
