<?php
/**
 * Kalium WordPress Theme
 *
 * Kalium Demo Content helpers.
 *
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Helpers {

	/**
	 * Get post IDs for import instance.
	 *
	 * @param string $import_instance_id
	 *
	 * @return int[]
	 * @global wpdb  $wpdb
	 */
	public static function get_post_ids( $import_instance_id = null ) {
		global $wpdb;

		// Get current instance ID
		if ( is_null( $import_instance_id ) ) {
			$import_instance_id = Kalium_Demo_Import_Manager::get_instance()->get_import_instance()->get_id();
		}

		return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE '_content_pack_import_id' = meta_key AND %s = meta_value", $import_instance_id ) );
	}

	/**
	 * Get term IDs for import instance.
	 *
	 * @param string $import_instance_id
	 *
	 * @return int[]
	 * @global wpdb  $wpdb
	 */
	public static function get_term_ids( $import_instance_id = null ) {
		global $wpdb;

		// Get current instance ID
		if ( is_null( $import_instance_id ) ) {
			$import_instance_id = Kalium_Demo_Import_Manager::get_instance()->get_import_instance()->get_id();
		}

		return $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT term_id FROM $wpdb->termmeta WHERE '_content_pack_import_id' = meta_key AND %s = meta_value", $import_instance_id ) );
	}

	/**
	 * Get post by title based on import instance ID.
	 *
	 * @param string       $import_instance_id
	 * @param string       $page_title
	 * @param string       $output
	 * @param string|array $post_type
	 *
	 * @return WP_Post|array|null
	 * @global wpdb        $wpdb
	 */
	public static function get_page_by_title( $import_instance_id, $page_title, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;

		// Import instance post IDs
		$post_ids        = esc_sql( self::get_post_ids( $import_instance_id ) );
		$post_ids_string = implode( ',', $post_ids );

		if ( empty( $post_ids_string ) ) {
			$post_ids_string = '-1';
		}

		if ( is_array( $post_type ) ) {
			$post_type           = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";

			$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type IN ($post_type_in_string) AND ID IN ($post_ids_string)", $page_title );
		} else {
			$sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s AND ID IN ($post_ids_string)", $page_title, $post_type );
		}

		$page = $wpdb->get_var( $sql );

		if ( $page ) {
			return get_post( $page, $output );
		}

		return null;
	}

	/**
	 * Get term by title based on import instance ID.
	 *
	 * @param string     $import_instance_id
	 * @param string     $field
	 * @param string|int $value
	 * @param string     $taxonomy
	 * @param string     $output
	 * @param string     $filter
	 *
	 * @return WP_Term|null
	 */
	public static function get_term_by( $import_instance_id, $field, $value, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) {
		global $wpdb;

		// Import instance post IDs
		$term_ids        = esc_sql( self::get_term_ids( $import_instance_id ) );
		$term_ids_string = implode( ',', $term_ids );

		if ( empty( $term_ids_string ) ) {
			$term_ids_string = '-1';
		}

		// Field
		$fields = [
			'id'   => 'term_id',
			'slug' => 'slug',
			'name' => 'name',
		];

		// Default field to get
		$table_field = $fields['id'];

		if ( isset( $fields[ $field ] ) ) {
			$table_field = $fields[ $field ];
		}

		$sql = $wpdb->prepare( "SELECT DISTINCT term_id FROM $wpdb->terms LEFT JOIN $wpdb->term_taxonomy USING(term_id) WHERE {$table_field} = %s AND taxonomy = %s AND term_id IN ({$term_ids_string})", $value, $taxonomy );

		// Matched term ID
		if ( $term_id = $wpdb->get_var( $sql ) ) {
			return get_term( $term_id, $taxonomy, $output, $filter );
		}

		return null;
	}
}
