<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type - WordPress Import class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type_WordPress_Import extends Kalium_Demo_Content_Import_Type {

	/**
	 * Tries to convert an attachment URL into a post ID.
	 *
	 * @param string $url
	 *
	 * @return int|null
	 */
	public static function get_attachment_id_from_url( $url ) {
		static $post_ids, $cached_urls = [];
		global $wpdb;

		// Return already cached url
		if ( isset( $cached_urls[ $url ] ) ) {
			return $cached_urls[ $url ];
		}

		// Process file path in URL
		$dir  = wp_get_upload_dir();
		$path = $url;

		$site_url   = parse_url( $dir['url'] );
		$image_path = parse_url( $path );

		// Force the protocols to match if needed.
		if ( isset( $image_path['scheme'] ) && ( $image_path['scheme'] !== $site_url['scheme'] ) ) {
			$path = str_replace( $image_path['scheme'], $site_url['scheme'], $path );
		}

		if ( 0 === strpos( $path, $dir['baseurl'] . '/' ) ) {
			$path = substr( $path, strlen( $dir['baseurl'] . '/' ) );
		}

		// Query current import instance ID
		if ( ! isset( $post_ids ) ) {
			$post_ids_arr = esc_sql( Kalium_Demo_Content_Helpers::get_post_ids() );
			$post_ids     = implode( ',', $post_ids_arr );

			if ( empty( $post_ids ) ) {
				$post_ids = '-1';
			}
		}

		// Query attachment name based on current query
		$sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE post_id IN ({$post_ids}) AND meta_key = '_wp_attached_file' AND meta_value LIKE %s", '%' . $wpdb->esc_like( $path ) );

		// Matched attachment
		$attachment_id = $wpdb->get_var( $sql );

		if ( is_numeric( $attachment_id ) ) {
			$attachment_id = (int) $attachment_id;
		}

		// Cache url
		$cached_urls[ $url ] = $attachment_id;

		return $attachment_id;
	}

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {

		// Import type name
		if ( ! empty( $this->name ) ) {
			return $this->name;
		}

		return 'WordPress Content (All)';
	}

	/**
	 * Args fields for WordPress Import type.
	 *
	 * @return array
	 */
	public function get_args_fields() {
		$args_fields = [];

		// Post types that can be importer separately
		$post_types = kalium_get_array_key( $this->get_options(), 'post_types', [] );

		if ( is_array( $post_types ) ) {

			foreach ( $post_types as $post_type => $title ) {

				// Args field
				$args_fields[] = [
					'type'  => 'checkbox',
					'name'  => 'post_types',
					'title' => $title,
					'value' => $post_type,
				];
			}
		}

		return $args_fields;
	}

	/**
	 * Backup before import.
	 *
	 * @return void
	 */
	public function do_backup() {

		// Vars
		$backup_manager = $this->get_content_pack()->backup_manager();
		$options        = $this->get_options();

		// Front page
		if ( isset( $options['front_page'] ) ) {
			$backup_manager->set_backup_option_once( 'page_on_front', get_option( 'page_on_front' ) );
		}

		// Posts page
		if ( isset( $options['posts_page'] ) ) {
			$backup_manager->set_backup_option_once( 'page_for_posts', get_option( 'page_for_posts' ) );
		}

		// Update show on front option
		if ( isset( $options['front_page'] ) || isset( $options['posts_page'] ) ) {
			$backup_manager->set_backup_option_once( 'show_on_front', get_option( 'show_on_front' ) );
		}

		// Permalink structure
		$backup_manager->set_backup_option_once( 'permalink_structure', get_option( 'permalink_structure' ) );

		// Menus
		if ( ! empty( $options['menus'] ) ) {
			$nav_menu_locations        = get_theme_mod( 'nav_menu_locations' );
			$backup_nav_menu_locations = [];

			foreach ( $options['menus'] as $menu_location => $menu_title ) {
				if ( isset( $nav_menu_locations[ $menu_location ] ) ) {
					$backup_nav_menu_locations[ $menu_location ] = $nav_menu_locations[ $menu_location ];
				}
			}

			// Save backup option
			$backup_manager->set_backup_option_once( 'menus', $backup_nav_menu_locations );
		}

		// WooCommerce options
		$woocommerce = kalium_get_array_key( $options, 'woocommerce', [] );

		if ( ! empty( $woocommerce ) && kalium()->is->woocommerce_active() ) {

			// WooCommerce pages
			if ( $pages = kalium_get_array_key( $woocommerce, 'pages' ) ) {
				$woocommerce_pages = [];

				foreach ( $pages as $page_name => $page_title ) {
					$option_name  = "woocommerce_{$page_name}_page_id";
					$option_value = get_option( $option_name, null );

					if ( ! is_null( $option_value ) ) {
						$woocommerce_pages[ $option_name ] = $option_value;
					}
				}

				// Save backup option
				if ( ! empty( $woocommerce_pages ) ) {
					$backup_manager->set_backup_option_once( 'woocommerce_pages', $woocommerce_pages );
				}
			}

			// WooCommerce thumbnail sizes
			if ( $thumbnails = kalium_get_array_key( $woocommerce, 'thumbnails' ) ) {
				$woocommerce_thumbnails = [];

				foreach ( $thumbnails as $thumbnail_name => $thumbnail_value ) {
					$option_name  = "woocommerce_thumbnail_{$thumbnail_name}";
					$option_value = get_option( $option_name, null );

					if ( ! is_null( $option_name ) ) {
						$woocommerce_thumbnails[ $option_name ] = $option_value;
					}
				}

				// Save backup option
				if ( ! empty( $woocommerce_thumbnails ) ) {
					$backup_manager->set_backup_option_once( 'woocommerce_thumbnails', $woocommerce_thumbnails );
				}
			}

			// WooCommerce options
			if ( $woo_options = kalium_get_array_key( $woocommerce, 'options' ) ) {
				$woocommerce_options = [];

				foreach ( $woo_options as $option_name => $option_value ) {
					$current_value = get_option( $option_name, null );

					if ( ! is_null( $current_value ) ) {
						$woocommerce_options[ $option_name ] = $current_value;
					}
				}

				// Save backup option
				if ( ! empty( $woocommerce_options ) ) {
					$backup_manager->set_backup_option_once( 'woocommerce_options', $woocommerce_options );
				}
			}

			// WooCommerce attribute taxonomies
			if ( ! empty( $woocommerce['attribute_taxonomies'] ) ) {
				$current_attribute_taxonomies = wc_get_attribute_taxonomies();
				$attribute_taxonomies         = [];

				foreach ( $current_attribute_taxonomies as $attribute_taxonomy ) {
					$attribute_taxonomies[] = (array) $attribute_taxonomy;
				}

				// Save backup option
				if ( ! empty( $attribute_taxonomies ) ) {
					$backup_manager->set_backup_option_once( 'woocommerce_attribute_taxonomies', $attribute_taxonomies );
				}
			}
		}

		// Deactivate WP_Importer plugin
		if ( kalium()->is->plugin_active( 'wordpress-importer/wordpress-importer.php' ) ) {
			deactivate_plugins( 'wordpress-importer/wordpress-importer.php' );
		}
	}

	/**
	 * Import WordPress XML file.
	 *
	 * @return void
	 */
	public function do_import() {

		// Execute parent do_import
		parent::do_import();

		// Do not run if there are errors reported
		if ( $this->errors->has_errors() ) {
			return;
		}

		// Include WP_Importer
		kalium()->require_file( __DIR__ . '/kalium-wp-import.php' );

		// Vars
		$content_pack       = $this->get_content_pack();
		$import_manager     = $content_pack->import_manager();
		$import_instance    = $content_pack->get_import_instance();
		$import_instance_id = $import_instance->get_id();
		$post_types         = kalium_get_array_key( $this->args_values, 'post_types', [] );
		$fetch_attachments  = in_array( 'attachment', $post_types );

		// Post types to import
		$import_post_types = null;

		if ( false === $this->is_checked() ) {
			$import_post_types = $post_types;
		}

		// Disable creation of attachment thumbnails
		if ( apply_filters( 'kalium_demo_disable_attachment_thumbnails_for_wp_import', true ) ) {
			$this->disable_attachment_thumbnails();
		}

		// Import process end actions
		add_action( 'import_end', function () use ( $import_instance ) {
			$import_instance->set_import_success();
		} );

		// Loop through each source
		foreach ( $this->get_sources() as $source ) {

			// Import file
			$import_file = $import_manager->get_content_pack_import_source_path( $source['name'] );

			// WP_Import instance
			$kalium_import = new Kalium_WP_Import( $import_instance_id, $fetch_attachments, $import_post_types );

			// Check if import file exists
			if ( true === kalium()->filesystem->exists( $import_file ) ) {

				// Start import
				$kalium_import->import( $import_file );
			} else {

				// Import file doesn't exists
				$this->errors->add( 'kalium_demo_content_import_file_not_exists', 'Import file doesn\'t exists!' );
			}
		}

	}

	/**
	 * Hooks and callbacks to execute after import finished.
	 *
	 * @return void
	 */
	public function do_complete() {

		// Options
		$options = $this->get_options();

		// Front page
		if ( isset( $options['front_page'] ) && ( $front_page = Kalium_Demo_Content_Helpers::get_page_by_title( null, $options['front_page'] ) ) ) {
			update_option( 'page_on_front', $front_page->ID, 'yes' );
		}

		// Posts page
		if ( isset( $options['posts_page'] ) && ( $posts_page = Kalium_Demo_Content_Helpers::get_page_by_title( null, $options['posts_page'] ) ) ) {
			update_option( 'page_for_posts', $posts_page->ID, 'yes' );
		}

		// Update show on front option
		if ( ( isset( $front_page ) && $front_page ) || ( isset( $posts_page ) && $posts_page ) ) {
			update_option( 'show_on_front', 'page', 'yes' );
		}

		// Menus
		$menus = kalium_get_array_key( $options, 'menus', [] );

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu_location => $menu_title ) {
				$menu = Kalium_Demo_Content_Helpers::get_term_by( null, 'name', $menu_title, 'nav_menu' );

				if ( $menu instanceof WP_Term ) {
					$nav_menu_locations                   = get_theme_mod( 'nav_menu_locations', [] );
					$nav_menu_locations[ $menu_location ] = $menu->term_id;
					set_theme_mod( 'nav_menu_locations', $nav_menu_locations );
				}
			}
		}

		// WooCommerce options
		$woocommerce = kalium_get_array_key( $options, 'woocommerce', [] );

		if ( ! empty( $woocommerce ) && kalium()->is->woocommerce_active() ) {

			// WooCommerce pages
			if ( $pages = kalium_get_array_key( $woocommerce, 'pages' ) ) {
				foreach ( $pages as $page_name => $page_title ) {
					if ( $page = Kalium_Demo_Content_Helpers::get_page_by_title( null, $page_title ) ) {
						update_option( "woocommerce_{$page_name}_page_id", $page->ID, 'yes' );
					}
				}
			}

			// WooCommerce thumbnail sizes
			if ( $thumbnails = kalium_get_array_key( $woocommerce, 'thumbnails' ) ) {
				foreach ( $thumbnails as $thumbnail_name => $thumbnail_value ) {
					update_option( "woocommerce_thumbnail_{$thumbnail_name}", $thumbnail_value, 'yes' );
				}
			}

			// WooCommerce options
			if ( $woocommerce_options = kalium_get_array_key( $woocommerce, 'options' ) ) {
				foreach ( $woocommerce_options as $option_name => $option_value ) {
					update_option( $option_name, $option_value, 'yes' );
				}
			}

			// Map and update WooCommerce taxonomy data
			if ( $taxonomy_data = kalium_get_array_key( $woocommerce, 'taxonomy_data' ) ) {
				$taxonomy_data = json_decode( $taxonomy_data, true );
				$this->set_woocommerce_taxonomy_data( $taxonomy_data );
			}

			// Map and update WooCommerce attribute taxonomies
			if ( $attribute_taxonomies = kalium_get_array_key( $woocommerce, 'attribute_taxonomies' ) ) {
				$attribute_taxonomies = json_decode( $attribute_taxonomies, true );
				$this->set_woocommerce_attribute_taxonomies( $attribute_taxonomies );
			}
		}

		// Set pretty permalinks
		update_option( 'permalink_structure', '/%postname%/', 'yes' );

		// Flush rewrite rules
		flush_rewrite_rules( true );

		// Mark as successful task
		parent::do_complete();
	}

	/**
	 * Remove imported content from XML file and revert other options.
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
		$content_pack       = $this->get_content_pack();
		$backup_manager     = $content_pack->backup_manager();
		$backup_options     = $backup_manager->get_backup_options();
		$import_instance    = $content_pack->get_import_instance();
		$import_instance_id = $import_instance->get_id();
		$options            = $this->get_options();

		// Delete posts
		$post_ids = Kalium_Demo_Content_Helpers::get_post_ids( $import_instance_id );

		foreach ( $post_ids as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		// Delete terms
		$term_ids = Kalium_Demo_Content_Helpers::get_term_ids( $import_instance_id );

		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id );

			if ( $term ) {
				wp_delete_term( $term_id, $term->taxonomy );
			}
		}

		/**
		 * Revert options.
		 */

		// Front page
		if ( isset( $backup_options['page_on_front'] ) ) {
			$page_on_front = $backup_options['page_on_front'];

			if ( get_post( $page_on_front ) ) {
				update_option( 'page_on_front', $page_on_front, 'yes' );
			}
		}

		// Posts page
		if ( isset( $backup_options['page_for_posts'] ) ) {
			$page_for_posts = $backup_options['page_for_posts'];

			if ( get_post( $page_for_posts ) ) {
				update_option( 'page_for_posts', $page_for_posts, 'yes' );
			}
		}

		// Update show on front option
		if ( isset( $backup_options['show_on_front'] ) ) {
			update_option( 'show_on_front', $backup_options['show_on_front'] );
		}

		// Permalink structure
		if ( isset( $backup_options['permalink_structure'] ) ) {
			update_option( 'permalink_structure', $backup_options['permalink_structure'] );
		}

		// Menus
		if ( ! empty( $backup_options['menus'] ) ) {
			$nav_menu_locations = get_theme_mod( 'nav_menu_locations' );

			foreach ( $backup_options['menus'] as $menu_location => $nav_menu_id ) {

				// Replace menu if it exists
				if ( get_term( $nav_menu_id ) ) {
					$nav_menu_locations[ $menu_location ] = $nav_menu_id;
				} else {
					unset( $nav_menu_locations[ $menu_location ] );
				}
			}

			// Revert nav menu locations
			set_theme_mod( 'nav_menu_locations', $nav_menu_locations );
		}

		// WooCommerce options
		$woocommerce = kalium_get_array_key( $options, 'woocommerce', [] );

		if ( ! empty( $woocommerce ) && kalium()->is->woocommerce_active() ) {

			// Pages
			if ( $pages = kalium_get_array_key( $backup_options, 'woocommerce_pages' ) ) {
				foreach ( $pages as $option_name => $option_value ) {

					if ( $option_value && get_post( $option_value ) ) {
						update_option( $option_name, $option_value );
					} else {
						update_option( $option_name, '' );
					}
				}
			}

			// Thumbnails
			if ( $thumbnails = kalium_get_array_key( $backup_options, 'woocommerce_thumbnails' ) ) {
				foreach ( $thumbnails as $option_name => $option_value ) {
					update_option( $option_name, $option_value, 'yes' );
				}
			}

			// WooCommerce options
			if ( $woo_options = kalium_get_array_key( $backup_options, 'woocommerce_options' ) ) {
				foreach ( $woo_options as $option_name => $option_value ) {
					update_option( $option_name, $option_value, 'yes' );
				}
			}

			// WooCommerce attribute taxonomies
			if ( $attribute_taxonomies = kalium_get_array_key( $woocommerce, 'attribute_taxonomies' ) ) {
				$imported_attribute_taxonomies = json_decode( $attribute_taxonomies, true );
				$previous_attribute_taxonomies = kalium_get_array_key( $backup_options, 'woocommerce_attribute_taxonomies', [] );

				if ( is_array( $imported_attribute_taxonomies ) ) {
					foreach ( wc_get_attribute_taxonomies() as $attribute_taxonomy ) {

						// Check if current attribute taxonomy is imported
						foreach ( $imported_attribute_taxonomies as $imported_attribute_taxonomy ) {

							// Matched attribute
							if (
								$attribute_taxonomy->attribute_name === $imported_attribute_taxonomy['attribute_name'] ||
								$attribute_taxonomy->attribute_label === $imported_attribute_taxonomy['attribute_label']
							) {

								// Check if this attribute was previously imported
								$previously_imported = false;

								foreach ( $previous_attribute_taxonomies as $previous_attribute_taxonomy ) {
									if (
										$imported_attribute_taxonomy['attribute_name'] === $previous_attribute_taxonomy['attribute_name'] ||
										$imported_attribute_taxonomy['attribute_label'] === $previous_attribute_taxonomy['attribute_label']
									) {
										$previously_imported = true;
										break;
									}
								}

								// If not previously imported, delete it
								if ( ! $previously_imported ) {
									wc_delete_attribute( $attribute_taxonomy->attribute_id );
								}
							}
						}
					}
				}
			}
		}

		// Flush rewrite rules
		flush_rewrite_rules( true );

		// Mark as removed
		parent::do_remove();
	}

	/**
	 * Map and update WooCommerce taxonomy data.
	 *
	 * @param array $taxonomy_entries
	 *
	 * @return void
	 */
	private function set_woocommerce_taxonomy_data( $taxonomy_entries ) {
		foreach ( $taxonomy_entries as $taxonomy_entry ) {
			if ( $matched_term = Kalium_Demo_Content_Helpers::get_term_by( null, 'slug', $taxonomy_entry['slug'], $taxonomy_entry['taxonomy'] ) ) {
				$term_id  = $matched_term->term_id;
				$taxonomy = $matched_term->taxonomy;

				// Data to modify
				$thumbnail = kalium_get_array_key( $taxonomy_entry, 'thumbnail' );

				// Update thumbnail
				if ( $thumbnail && ( $attachment_id = self::get_attachment_id_from_url( $thumbnail ) ) ) {
					update_term_meta( $term_id, 'thumbnail_id', $attachment_id );
				}
			}
		}
	}

	/**
	 * Map and update WooCommerce attribute taxonomies.
	 *
	 * @param array $attribute_taxonomies
	 *
	 * @return void
	 */
	private function set_woocommerce_attribute_taxonomies( $attribute_taxonomies ) {
		$current_attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach ( $current_attribute_taxonomies as $current_attribute_taxonomy ) {
			foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
				if ( $attribute_taxonomy['attribute_name'] === $current_attribute_taxonomy->attribute_name ) {
					wc_update_attribute( $current_attribute_taxonomy->attribute_id, [
						'name' => $attribute_taxonomy['attribute_label'],
					] );
					break;
				}
			}
		}
	}

	/**
	 * Disable creation of attachment thumbnails for faster execution of import process.
	 *
	 * @return void
	 */
	private function disable_attachment_thumbnails() {
		global $_wp_additional_image_sizes;

		$image_sizes = [
			'blog-thumb-1',
			'blog-thumb-2',
			'blog-thumb-3',
			'blog-single-1',
			'portfolio-single-img-1',
			'portfolio-single-img-2',
			'portfolio-single-img-3',
			'portfolio-single-img-4',
			'portfolio-img-1',
			'portfolio-img-2',
			'portfolio-img-3',
		];

		foreach ( $image_sizes as $image_size ) {
			if ( isset( $_wp_additional_image_sizes[ $image_size ] ) ) {
				unset( $_wp_additional_image_sizes[ $image_size ] );
			}
		}
	}
}
