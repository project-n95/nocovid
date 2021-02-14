<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - Install Child Theme class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_Install_Child_Theme extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Child Theme';
	}

	/**
	 * Backup before installing/activating the theme.
	 *
	 * @return void
	 */
	public function do_backup() {
		$backup_manager = $this->get_content_pack()->backup_manager();

		// Set current active theme as previous theme
		$backup_manager->set_backup_option_once( 'previous_theme', wp_get_theme()->get_stylesheet() );
	}

	/**
	 * Install child theme and activate it.
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

		// Include dependency files
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		// Vars
		$content_pack     = $this->get_content_pack();
		$import_manager   = $content_pack->import_manager();
		$import_instance  = $content_pack->get_import_instance();
		$options          = $this->get_options();
		$installed_themes = wp_get_themes();

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Child theme
			$child_theme_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if child theme exists
			if ( true === kalium()->filesystem->exists( $child_theme_file ) ) {

				// Theme name
				$theme_name = kalium_get_array_key( $options, 'name' );

				// If theme is already installed
				if ( ! empty( $installed_themes[ $theme_name ] ) ) {
					$result = true;
				} // Install child theme
				else {

					// Upgrader skin
					$upgrader_skin = new Theme_Upgrader_Skin( [
						'title' => 'Installing child theme...',
						'url'   => 'https://laborator.co/',
						'theme' => $theme_name,
					] );

					// Init upgrader/installer
					$upgrader = new Theme_Upgrader( $upgrader_skin );

					// Install theme
					$result = $upgrader->install( $child_theme_file );
				}

				// Theme installed
				if ( true === $result ) {
					$theme = wp_get_theme( $theme_name );

					// Check theme
					if ( ! $theme->exists() || ! $theme->is_allowed() ) {
						$this->errors->add( 'kalium_demo_content_child_theme_not_exists', 'The requested theme does not exist!' );
					} else {

						// Activate theme
						switch_theme( $theme->get_stylesheet() );

						// Disable redirects after theme switch
						update_option( 'theme_switched', false );

						// Mark as successful theme installation
						$import_instance->set_import_success();
					}

				} else if ( is_wp_error( $result ) ) {
					$this->errors = $result;
				} else {
					$this->errors->add( 'kalium_demo_content_child_theme_cannot_install', 'Child theme could\'t be installed, it might be already installed!' );
				}
			} else {

				// Import file doesn't exists
				$this->errors->add( 'kalium_demo_content_child_theme_not_exists', 'Child theme file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Restore previous activated theme.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Vars
		$backup_manager   = $this->get_content_pack()->backup_manager();
		$previous_theme   = $backup_manager->get_backup_option( 'previous_theme' );
		$installed_themes = wp_get_themes();

		// Activate previous theme
		if ( $previous_theme && isset( $installed_themes[ $previous_theme ] ) ) {

			// Activate theme
			switch_theme( $installed_themes[ $previous_theme ]->get_stylesheet() );

			// Disable redirects after theme switch
			update_option( 'theme_switched', false );
		}

		// Mark as removed
		parent::do_remove();
	}
}
