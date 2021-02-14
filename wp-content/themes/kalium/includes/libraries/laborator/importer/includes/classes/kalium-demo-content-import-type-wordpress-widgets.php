<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - WordPress Widgets class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_WordPress_Widgets extends Kalium_Demo_Content_Import_Type {

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Widgets';
	}

	/**
	 * Deactivate Widget Importer Exporter plugin if its active.
	 *
	 * @return void
	 */
	public function do_backup() {

		// Deactivate Widget_Importer_Exporter plugin
		if ( kalium()->is->plugin_active( 'widget-importer-exporter/widget-importer-exporter.php' ) ) {
			deactivate_plugins( 'widget-importer-exporter/widget-importer-exporter.php' );
		}
	}

	/**
	 * Import WordPress widgets.
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

		// Include Widget_Importer_Exporter
		if ( ! class_exists( 'Widget_Importer_Exporter' ) ) {
			kalium()->require_file( __DIR__ . '/../plugins/widget-importer-exporter/widget-importer-exporter.php' );
		}

		// Vars
		$content_pack    = $this->get_content_pack();
		$import_manager  = $content_pack->import_manager();
		$backup_manager  = $content_pack->backup_manager();
		$import_instance = $content_pack->get_import_instance();

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// WordPress Widgets
			$widgets_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if widgets file exists
			if ( true === kalium()->filesystem->exists( $widgets_file ) ) {
				global $wie_import_results;

				// Import WordPress Widgets
				wie_process_import_file( $widgets_file );

				// Imported widgets
				if ( is_array( $wie_import_results ) ) {
					$widgets = $backup_manager->get_backup_option( 'widgets', [] );

					foreach ( $wie_import_results as $sidebar_id => $sidebar ) {
						foreach ( $sidebar['widgets'] as $widget_id => $widget ) {
							$message_type = kalium_get_array_key( $widget, 'message_type' );

							// Imported widget instance successfully
							if ( 'success' === $message_type ) {

								// Mark as imported widget [$sidebar_id, $widget_id]
								if ( ! isset( $widgets[ $sidebar_id ] ) ) {
									$widgets[ $sidebar_id ] = [];
								}

								// Add widget ID once
								if ( ! in_array( $widget_id, $widgets[ $sidebar_id ] ) ) {
									$widgets[ $sidebar_id ][] = $widget_id;
								}
							}
						}
					}

					// Save to backup options
					$backup_manager->update_backup_option( 'widgets', $widgets );
				}

				// Mark as successful import
				$import_instance->set_import_success();
			} else {

				// Widgets file doesn't exists
				$this->errors->add( 'kalium_demo_content_wordpress_widgets_not_exists', 'Widgets file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Adjust widgets settings and map menu ids.
	 *
	 * @return void
	 */
	public function do_complete() {

		// Widgets options
		$options      = $this->get_options();
		$widgets_data = kalium_get_array_key( $options, 'data' );

		// Setup widgets data
		if ( $widgets_data = json_decode( $widgets_data, true ) ) {

			// Nav menus
			$widget_nav_menu       = get_option( 'widget_nav_menu' );
			$widget_nav_menu_data  = kalium_get_array_key( $widgets_data, 'widget_nav_menu', [] );
			$widget_nav_menu_terms = [];

			// Get matching term ids
			foreach ( $widget_nav_menu_data as $widget_instance ) {
				$term = Kalium_Demo_Content_Helpers::get_term_by( null, 'name', $widget_instance['name'], 'nav_menu' );

				if ( $term instanceof WP_Term ) {
					$widget_nav_menu_terms[ $widget_instance['term_id'] ] = $term->term_id;
				}
			}

			// Update nav menu values
			if ( ! empty( $widget_nav_menu_terms ) ) {
				foreach ( $widget_nav_menu as & $widget_instance ) {
					$nav_menu = kalium_get_array_key( $widget_instance, 'nav_menu' );

					if ( isset( $widget_nav_menu_terms[ $nav_menu ] ) ) {
						$widget_instance['nav_menu'] = $widget_nav_menu_terms[ $nav_menu ];
					}
				}

				// Save widget nav menu
				update_option( 'widget_nav_menu', $widget_nav_menu );
			}
		}

		// Mark as successful task
		parent::do_complete();
	}

	/**
	 * Remove imported widgets.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Vars
		$backup_manager = $this->get_content_pack()->backup_manager();
		$widgets        = $backup_manager->get_backup_option( 'widgets' );

		// Delete imported widgets
		if ( is_array( $widgets ) ) {
			foreach ( $widgets as $sidebar_id => $widget_ids ) {
				foreacH ( $widget_ids as $widget_id ) {
					$this->delete_widget( $widget_id );
				}
			}
		}


		// Mark as removed
		parent::do_remove();
	}

	/**
	 * Delete widget by ID.
	 *
	 * @param string $widget_id
	 *
	 * @return void
	 */
	private function delete_widget( $widget_id ) {

		// Vars
		$sidebars_widgets = wp_get_sidebars_widgets();
		$pieces           = explode( '-', $widget_id );
		$multi_number     = array_pop( $pieces ) - 1; // Subtract by -1 to match index with one generated by Widget_Importer_Exporter
		$id_base          = implode( '-', $pieces );
		$widget           = get_option( 'widget_' . $id_base );

		// Unset widget instance
		unset( $widget[ $multi_number ] );

		// Save widget instances
		update_option( 'widget_' . $id_base, $widget );

		// Unset widget instance id from sidebar
		foreach ( $sidebars_widgets as $sidebar_id => $widget_instance_ids ) {
			foreach ( $widget_instance_ids as $key => $widget_instance_id ) {
				if ( $widget_instance_id === "{$id_base}-{$multi_number}" ) {
					unset( $sidebars_widgets[ $sidebar_id ][ $key ] );
				}
			}
		}

		// Save sidebar widgets
		wp_set_sidebars_widgets( $sidebars_widgets );
	}
}
