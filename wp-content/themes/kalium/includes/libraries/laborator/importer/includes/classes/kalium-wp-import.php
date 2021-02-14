<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium WP Import class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

// Load importers
define( 'WP_LOAD_IMPORTERS', true );

// Load WordPress Importer
include_once dirname( __FILE__ ) . '/../plugins/wordpress-importer/wordpress-importer.php';

/**
 * Kalium_WP_Import class.
 */
class Kalium_WP_Import extends WP_Import {

	/**
	 * Import instance ID meta key.
	 */
	const IMPORT_INSTANCE_ID_META_KEY = '_content_pack_import_id';

	/**
	 * Import instance ID.
	 *
	 * @var string
	 */
	public $import_instance_id;

	/**
	 * Post types to import.
	 *
	 * @var array
	 */
	public $import_post_types = [];

	/**
	 * Post types to exclude.
	 *
	 * @var array
	 */
	public $exclude_post_types = [];

	/**
	 * Constructor.
	 *
	 * @param string     $import_instance_id
	 * @param bool       $fetch_attachments
	 * @param array|null $filter_post_types
	 *
	 * @return void
	 */
	public function __construct( $import_instance_id, $fetch_attachments = true, $filter_post_types = null ) {
		parent::__construct();

		// Import instance ID
		$this->import_instance_id = $import_instance_id;

		// Import ID
		$this->id = $this->import_instance_id;

		// Set fetch attachments
		$this->fetch_attachments = $fetch_attachments;

		// If attachments are excluded, remove from post types array
		if ( ! $fetch_attachments ) {
			$this->exclude_post_types[] = 'attachment';
		}

		// Post types to import
		if ( ! is_null( $filter_post_types ) && is_array( $filter_post_types ) ) {

			// Post types to include
			$this->import_post_types = $filter_post_types;

			// Filter post types
			add_action( 'import_start', [ $this, '_filter_post_types' ] );
		}

		// Set '_content_pack_import_id' meta for posts, terms and comment terms with $import_instance_id
		add_filter( 'wp_import_post_meta', [ $this, '_set_wp_import_instance_id_meta' ] );
		add_filter( 'wp_import_term_meta', [ $this, '_set_wp_import_instance_id_meta' ] );
		add_filter( 'wp_import_post_comments', [ $this, '_set_wp_import_instance_id_comment_meta' ] );

		// Attribute imported content to the current user
		add_action( 'import_start', [ $this, '_clear_authors' ] );

		// WooCommerce importer compatibility
		add_action( 'import_start', [ $this, '_woocommerce_post_importer_compatibility' ] );
	}

	/**
	 * Filter post types.
	 *
	 * @return void
	 */
	public function _filter_post_types() {
		$exclude_terms = [];

		/**
		 * Term exists function.
		 *
		 * @param array $term {
		 *
		 * @type string $name
		 * @type string $slug
		 * @type string $domain
		 * @type string $term_taxonomy
		 * }
		 *
		 * @return bool
		 */
		$term_exists = function ( $term ) use ( & $exclude_terms ) {
			foreach ( $exclude_terms as $exclude_term ) {

				if ( isset( $term['term_taxonomy'] ) && $term['term_taxonomy'] === $exclude_term['domain'] && $term['slug'] === $exclude_term['slug'] ) {
					return true;
				} elseif ( isset( $term['domain'] ) && $term['domain'] === $exclude_term['domain'] && $term['slug'] === $exclude_term['slug'] ) {
					return true;
				}
			}

			return false;
		};

		// Exclude posts
		foreach ( $this->posts as $i => $post ) {
			$exclude = ! in_array( $post['post_type'], $this->import_post_types ) || in_array( $post['post_type'], $this->exclude_post_types );

			if ( $exclude ) {
				$terms = kalium_get_array_key( $post, 'terms', [] );

				// Exclude from posts
				unset( $this->posts[ $i ] );

				// Exclude terms
				foreach ( $terms as $term ) {

					// Add to excluded terms list
					if ( ! $term_exists( $term ) ) {
						$exclude_terms[] = $term;
					}
				}
			}
		}

		// Remove from excluded terms that appear in filtered posts array
		foreach ( $this->posts as $i => $post ) {
			$terms = kalium_get_array_key( $post, 'terms', [] );

			foreach ( $terms as $term ) {
				if ( $term_exists( $term ) ) {
					foreach ( $exclude_terms as $j => $exclude_term ) {
						if ( $term['domain'] === $exclude_term['domain'] && $term['slug'] === $exclude_term['slug'] ) {
							unset( $exclude_terms[ $j ] );
						}
					}
				}
			}
		}

		// Exclude $this->terms
		foreach ( $this->terms as $i => $term ) {
			if ( $term_exists( $term ) ) {
				unset( $this->terms[ $i ] );
			}
		}

		// Exclude $this->categories
		foreach ( $this->categories as $i => $term ) {
			if ( $term_exists( $term ) ) {
				unset( $this->categories[ $i ] );
			}
		}

		// Exclude $this->tags
		foreach ( $this->tags as $i => $term ) {
			if ( $term_exists( $term ) ) {
				unset( $this->tags[ $i ] );
			}
		}
	}

	/**
	 * Assign import instance ID for post (or terms) meta.
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	public function _set_wp_import_instance_id_meta( $meta ) {
		$meta[] = [
			'key'   => self::IMPORT_INSTANCE_ID_META_KEY,
			'value' => $this->import_instance_id,
		];

		return $meta;
	}

	/**
	 * Assign import instance ID for comments.
	 *
	 * @param array $comments
	 *
	 * @return array
	 */
	public function _set_wp_import_instance_id_comment_meta( $comments ) {
		foreach ( $comments as & $comment ) {
			$comment['commentmeta'] = $this->_set_wp_import_instance_id_meta( $comment['commentmeta'] );
		}

		return $comments;
	}

	/**
	 * Clear authors from import file.
	 *
	 * @return void
	 */
	public function _clear_authors() {
		$this->authors = [];
	}

	/**
	 * When running the WP XML importer, ensure attributes exist.
	 *
	 * WordPress import should work - however, it fails to import custom product attribute taxonomies.
	 * This code grabs the file before it is imported and ensures the taxonomies are created.
	 *
	 * Borrowed from WooCommerce (4.0.1)
	 * ./woocommerce/includes/admin/class-wc-admin-importers.php:147
	 */
	public function _woocommerce_post_importer_compatibility() {

		// Only when WooCommerce plugin is active
		if ( ! kalium()->is->woocommerce_active() ) {
			return;
		}

		// Provide import data from the current instance
		$import_data = [
			'posts' => $this->posts,
		];

		// $id          = absint( $_POST['import_id'] ); // PHPCS: input var ok.
		// $file        = get_attached_file( $id );
		// $parser      = new WXR_Parser();
		// $import_data = $parser->parse( $file );

		if ( isset( $import_data['posts'] ) && ! empty( $import_data['posts'] ) ) {
			foreach ( $import_data['posts'] as $post ) {
				if ( 'product' === $post['post_type'] && ! empty( $post['terms'] ) ) {
					foreach ( $post['terms'] as $term ) {
						if ( strstr( $term['domain'], 'pa_' ) ) {
							if ( ! taxonomy_exists( $term['domain'] ) ) {
								$attribute_name = wc_attribute_taxonomy_slug( $term['domain'] );

								// Create the taxonomy.
								if ( ! in_array( $attribute_name, wc_get_attribute_taxonomies(), true ) ) {
									wc_create_attribute(
										[
											'name'         => $attribute_name,
											'slug'         => $attribute_name,
											'type'         => 'select',
											'order_by'     => 'menu_order',
											'has_archives' => false,
										]
									);
								}

								// Register the taxonomy now so that the import works!
								register_taxonomy(
									$term['domain'],
									apply_filters( 'woocommerce_taxonomy_objects_' . $term['domain'], [ 'product' ] ),
									apply_filters(
										'woocommerce_taxonomy_args_' . $term['domain'],
										[
											'hierarchical' => true,
											'show_ui'      => false,
											'query_var'    => true,
											'rewrite'      => false,
										]
									)
								);
							}
						}
					}
				}
			}
		}
	}
}
