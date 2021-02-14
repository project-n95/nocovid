<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - Revolution Slider class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_Revolution_Slider extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Sliders';
	}

	/**
	 * Import sliders.
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
		$backup_manager  = $content_pack->backup_manager();
		$import_instance = $content_pack->get_import_instance();

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Slider
			$slider_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if slider file exists
			if ( true === kalium()->filesystem->exists( $slider_file ) ) {

				// Import slider
				$import = new RevSliderSliderImport();
				$result = $import->import_slider( true, $slider_file );

				// Slider imported
				if ( kalium_get_array_key( $result, 'success', false ) ) {

					// Imported slider ID
					$slider_id = $result['sliderID'];

					// Backup imported slider ids
					$revslider_ids   = $backup_manager->get_backup_option( 'revslider_ids', [] );
					$revslider_ids[] = $slider_id;
					$backup_manager->update_backup_option( 'revslider_ids', $revslider_ids );

					// Mark as successful import
					$import_instance->set_import_success();
				} else {

					// Error message
					$error = kalium_get_array_key( $result, 'error', 'Slider Import Failed' );

					// Slider file doesn't exists
					$this->errors->add( 'kalium_demo_content_revslider_import_failed', $error );
				}

			} else {

				// Slider file doesn't exists
				$this->errors->add( 'kalium_demo_content_revslider_not_exists', 'Slider file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Remove imported sliders.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Required plugins are not active
		if ( ! $this->plugins_are_active() ) {
			$this->errors->add( 'kalium_demo_content_remove_plugins_not_active', sprintf( 'Required plugins are not active, <strong>%s</strong> cannot be uninstalled.', $this->get_name() ) );
			return;
		}

		// Vars
		$backup_manager = $this->get_content_pack()->backup_manager();
		$revslider_ids  = $backup_manager->get_backup_option( 'revslider_ids' );

		// Delete sliders
		if ( is_array( $revslider_ids ) ) {
			foreach ( $revslider_ids as $revslider_id ) {
				$slider = new RevSliderSlider();

				try {
					$slider->init_by_id( $revslider_id );
					$slider->delete_slider();
				} catch ( Exception $e ) {
				}
			}
		}

		// Mark as removed
		parent::do_remove();
	}
}
