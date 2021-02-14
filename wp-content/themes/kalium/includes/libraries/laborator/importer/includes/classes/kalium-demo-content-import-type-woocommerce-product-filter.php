<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - WooCommerce Product Filter class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_WooCommerce_Product_Filter extends Kalium_Demo_Content_Import_Type {

	/**
	 * Product attributes to map with existing terms.
	 *
	 * @var array
	 */
	public $product_attributes;

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Product Filter Options';
	}

	/**
	 * Backup current product filter options.
	 *
	 * @return void
	 */
	public function do_backup() {
		global $wpdb;

		// Vars
		$backup_manager         = $this->get_content_pack()->backup_manager();
		$product_filter_options = [];

		// Load product filter option names
		$sql     = $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", sprintf( "%%%s%%", $wpdb->esc_like( '_prdctfltr' ) ) );
		$options = $wpdb->get_col( $sql );

		// Remove widgets from product filter
		if ( in_array( 'widget_prdctfltr', $options ) ) {
			unset( $options[ array_search( 'widget_prdctfltr', $options ) ] );
		}

		// Load product filter option values
		foreach ( $options as $option ) {
			$product_filter_options[ $option ] = get_option( $option );
		}

		// Save backup option
		$backup_manager->set_backup_option_once( 'product_filter_options', $product_filter_options );
	}

	/**
	 * Import Product Filter options.
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
		$content_pack       = $this->get_content_pack();
		$import_manager     = $content_pack->import_manager();
		$import_instance    = $content_pack->get_import_instance();
		$product_attributes = kalium_get_array_key( $this->get_options(), 'product_attributes' );

		if ( ! is_null( $product_attributes ) ) {
			if ( $product_attributes = json_decode( $product_attributes, true ) ) {
				$this->product_attributes = $product_attributes;
			}
		}

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Product Filter options
			$product_filter_options_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// Check if product filter options file exists
			if ( true === kalium()->filesystem->exists( $product_filter_options_file ) ) {
				$product_filter_options = json_decode( kalium()->filesystem->get_contents( $product_filter_options_file ), true );

				// Import product filter options
				if ( is_array( $product_filter_options ) ) {

					// Match attachments with existing ones
					$this->match_attachments( $product_filter_options );

					// Match term IDs with existing ones
					if ( is_array( $this->product_attributes ) ) {
						$this->match_term_ids( $product_filter_options );
					}

					// Set options
					foreach ( $product_filter_options as $option_name => $option_value ) {
						update_option( $option_name, $option_value, false );
					}

					// Mark as successful import
					$import_instance->set_import_success();
				} else {
					$this->errors->add( 'kalium_demo_content_woocommerce_product_filter_not_valid', 'Product Filter import file is not valid!' );
				}
			} else {

				// Product Filter options file doesn't exists
				$this->errors->add( 'kalium_demo_content_woocommerce_product_filter_not_exists', 'Product Filter options file doesn\'t exists!' );
			}
		}

		// Add errors to import instance
		if ( $this->errors->has_errors() ) {
			$import_instance->add_error( $this->errors );
		}
	}

	/**
	 * Restore previous product filter options.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Vars
		$backup_manager                  = $this->get_content_pack()->backup_manager();
		$previous_product_filter_options = $backup_manager->get_backup_option( 'product_filter_options' );

		// Restore product filter options
		if ( is_array( $previous_product_filter_options ) ) {
			foreach ( $previous_product_filter_options as $option_name => $value ) {
				update_option( $option_name, $value );
			}
		}

		// Mark as removed
		parent::do_remove();
	}

	/**
	 * Match images with existing attachments.
	 *
	 * @param array $array
	 *
	 * @return void
	 */
	private function match_attachments( &$array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => &$value ) {
				if ( is_string( $value ) && preg_match( '/(?<file_path>[\d]+\/[\d]+\/[\w\-_\.]+\.(png|jpe?g|gif))$/', $value, $matches ) ) {
					if ( $attachment_id = Kalium_Demo_Content_Import_Type_WordPress_Import::get_attachment_id_from_url( $matches['file_path'] ) ) {
						$attachment_url = wp_get_attachment_image_src( $attachment_id, 'original' );
						$value = $attachment_url[0];
					}
				} // Look deeper
				else if ( is_array( $value ) ) {
					$this->match_attachments( $value );
				}
			}
		}
	}

	/**
	 * Match term IDs with existing WooCommerce terms.
	 *
	 * @param array $array
	 *
	 * @return void
	 */
	private function match_term_ids( &$array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => &$value ) {

				// Match terms ids
				if ( 'terms' === $key ) {
					foreach ( $value as &$term ) {
						$this->match_term_id( $term );
					}
				} // Match selected array
				else if ( 'selected' === $key && is_array( $value ) ) {
					foreach ( $value as & $term_id ) {
						if ( is_numeric( $term_id ) ) {
							$term_id = $this->get_corresponding_term_id( $term_id );
						}
					}
				} // Look deeper
				else if ( is_array( $value ) ) {
					$this->match_term_ids( $value );
				}
			}
		}
	}

	/**
	 * Match term ID with existing term.
	 *
	 * @param array $term
	 *
	 * @return void
	 */
	private function match_term_id( &$term ) {
		$term_id = kalium_get_array_key( $term, 'id', null );

		if ( ! is_null( $term_id ) ) {
			$term['id'] = $term['slug'] = $this->get_corresponding_term_id( $term_id );
		}
	}

	/**
	 * Get corresponding term id for old term id.
	 *
	 * @param int|string $old_term_id
	 *
	 * @return int
	 */
	private function get_corresponding_term_id( $old_term_id ) {
		if ( is_numeric( $old_term_id ) ) {
			$old_term_id = intval( $old_term_id );

			// Find the taxonomy and slug
			$taxonomy = $slug = '';

			foreach ( $this->product_attributes as $pa_term ) {
				if ( $pa_term['id'] === $old_term_id ) {
					$taxonomy = $pa_term['tax'];
					$slug     = $pa_term['slug'];
				}
			}

			// Matched term
			if ( ! empty( $taxonomy ) && ! empty( $slug ) ) {

				// Replace term ID
				if ( $existing_term = get_term_by( 'slug', $slug, $taxonomy ) ) {
					return $existing_term->term_id;
				}
			}
		}

		return $old_term_id;
	}
}
