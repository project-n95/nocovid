<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - Typography class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_Typography extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Typography';
	}

	/**
	 * Backup current font sizes.
	 *
	 * @return void
	 */
	public function do_backup() {

		// Vars
		$backup_manager    = $this->get_content_pack()->backup_manager();
		$font_sizes        = TypoLab::get_setting( 'font_sizes' );
		$custom_font_sizes = TypoLab::get_setting( 'custom_font_sizes' );

		// Save backup option
		$backup_manager->set_backup_option_once( 'typolab_font_sizes', $font_sizes );
		$backup_manager->set_backup_option_once( 'typolab_custom_font_sizes', $custom_font_sizes );
	}

	/**
	 * Import typography and font sizes.
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

		// TypoLab_Font_Export_Import class
		kalium()->require_file( 'includes/libraries/laborator/typolab/inc/classes/typolab-font-export-import.php' );

		// Vars
		$content_pack    = $this->get_content_pack();
		$import_manager  = $content_pack->import_manager();
		$backup_manager  = $content_pack->backup_manager();
		$import_instance = $content_pack->get_import_instance();

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Typography options
			$typography_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Font import/export instance
			$font_importer = new TypoLab_Font_Export_Import();

			// Check if typography file exists
			if ( true === kalium()->filesystem->exists( $typography_file ) ) {
				$typography = kalium()->filesystem->get_contents( $typography_file );
				$font_importer->import( $typography );

				// Imported font IDs
				$imported_font_ids = $font_importer->get_imported_font_ids();

				// Backup imported font ids
				$backup_font_ids = $backup_manager->get_backup_option( 'typolab_fonts', [] );
				$backup_font_ids = array_merge( $backup_font_ids, $imported_font_ids );
				$backup_manager->update_backup_option( 'typolab_fonts', $backup_font_ids );

				// Mark as successful import
				$import_instance->set_import_success();
			} else {

				// Theme options file doesn't exists
				$this->errors->add( 'kalium_demo_content_typolab_not_exists', 'Typography file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Remove installed fonts and restore previous font sizes.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Vars
		$backup_manager    = $this->get_content_pack()->backup_manager();
		$installed_fonts   = $backup_manager->get_backup_option( 'typolab_fonts' );
		$font_sizes        = $backup_manager->get_backup_option( 'typolab_font_sizes' );
		$custom_font_sizes = $backup_manager->get_backup_option( 'typolab_custom_font_sizes' );

		// Remove installed fonts
		if ( is_array( $installed_fonts ) ) {
			foreach ( $installed_fonts as $font_id ) {
				TypoLab::delete_font( $font_id );
			}
		}

		// Restore font sizes
		if ( empty( $font_sizes ) ) {
			$font_sizes = [];
		}

		TypoLab::set_setting( 'font_sizes', $font_sizes );

		// Restore custom font sizes
		if ( empty( $custom_font_sizes ) ) {
			$custom_font_sizes = [];
		}

		TypoLab::set_setting( 'typolab_custom_font_sizes', $custom_font_sizes );

		// Mark as removed
		parent::do_remove();
	}
}
