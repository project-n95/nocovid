<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - Theme Custom CSS class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_Theme_Custom_CSS extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Custom CSS';
	}

	/**
	 * Backup current custom CSS.
	 *
	 * @return void
	 */
	public function do_backup() {

		// Vars
		$backup_manager  = $this->get_content_pack()->backup_manager();
		$custom_css_vars = [
			'laborator_custom_css'      => null,
			'laborator_custom_css_lg'   => null,
			'laborator_custom_css_md'   => null,
			'laborator_custom_css_sm'   => null,
			'laborator_custom_css_xs'   => null,
			'laborator_custom_css_less' => null,
			'laborator_custom_css_sass' => null,
		];


		// Load current custom CSS values
		foreach ( $custom_css_vars as $option_name => & $value ) {
			$value = get_option( $option_name );
		}

		// Save backup option
		$backup_manager->set_backup_option_once( 'custom_css', $custom_css_vars );
	}

	/**
	 * Import custom CSS.
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

			// Custom CSS
			$custom_css_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if custom CSS file exists
			if ( true === kalium()->filesystem->exists( $custom_css_file ) ) {
				$custom_css = maybe_unserialize( kalium()->filesystem->get_contents( $custom_css_file ) );

				// Save Custom CSS
				if ( is_array( $custom_css ) ) {
					foreach ( $custom_css as $var_name => $value ) {
						update_option( $var_name, $value );
					}

					// Mark as successful import
					$import_instance->set_import_success();
				} else {
					$this->errors->add( 'kalium_demo_content_custom_css_not_valid', 'Custom CSS import file is not valid!' );
				}

			} else {

				// Custom CSS file doesn't exists
				$this->errors->add( 'kalium_demo_content_custom_css_not_exists', 'Custom CSS file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Restore previous custom CSS.
	 */
	public function do_remove() {

		// Vars
		$backup_manager = $this->get_content_pack()->backup_manager();
		$custom_css     = $backup_manager->get_backup_option( 'custom_css' );

		// Restore custom CSS vars
		if ( is_array( $custom_css ) ) {
			foreach ( $custom_css as $option_name => $value ) {
				if ( get_option( $option_name ) ) {
					update_option( $option_name, $value );
				} else {
					delete_option( $option_name );
				}
			}
		}

		// Mark as removed
		parent::do_remove();
	}
}
