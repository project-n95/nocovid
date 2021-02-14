<?php
/**
 * Kalium WordPress Theme
 *
 * Font Sizes Generator.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Font_Sizes {

	/**
	 * Font sizes array.
	 *
	 * @var array
	 */
	private static $font_sizes = [];

	/**
	 * Initialize Font Sizes.
	 *
	 * @return void
	 */
	public function __construct() {

		// Initialize CSS font groups for admin
		add_action( 'admin_init', [ 'TypoLab_Font_Sizes', 'initialize_font_size_groups' ] );
	}

	/**
	 * Create Font Size Groups.
	 *
	 * @return void
	 */
	public static function initialize_font_size_groups() {
		self::$font_sizes = array();

		// Theme or plugins can add their font size selector groups
		do_action( 'typolab_add_font_size_groups' );

		// Custom Defined Font Sizes
		$custom_font_sizes = TypoLab::get_setting( 'custom_font_sizes', array() );

		foreach ( $custom_font_sizes as $font_size_group ) {
			self::add_font_size_group( $font_size_group['title'], $font_size_group['description'], $font_size_group['selectors'], $font_size_group['builtin'], $font_size_group['id'] );
		}
	}

	/**
	 * Add Font Size Group.
	 *
	 * @param string $group_name
	 * @param string $group_description
	 * @param array  $selectors
	 * @param bool   $builtin
	 * @param null   $id
	 *
	 * @return void
	 */
	public static function add_font_size_group( $group_name, $group_description, $selectors = array(), $builtin = true, $id = null ) {
		$font_size_group = [
			'id'          => $id ? $id : null,
			'title'       => $group_name,
			'description' => $group_description,
			'selectors'   => $selectors,
			'builtin'     => $builtin,
			'sizes'       => []
		];

		// Assign given ID
		if ( ! $id ) {
			$font_size_group['id'] = sanitize_title( $group_name );
		}

		self::$font_sizes[] = $font_size_group;
	}

	/**
	 * Get Defined Font Size Groups.
	 *
	 * @param bool $reinitialize
	 *
	 * @return array
	 */
	public static function get_font_sizes( $reinitialize = false ) {
		if ( $reinitialize ) {
			self::initialize_font_size_groups();
		}

		return self::$font_sizes;
	}

	/**
	 * Get Sizes Only.
	 *
	 * @return array
	 */
	public static function get_only_sizes() {
		return TypoLab::get_setting( 'font_sizes', [] );
	}

	/**
	 * Add Custom Font Size Group.
	 *
	 * @param array $font_size_group
	 *
	 * @return void
	 */
	public static function addCustomFontSizeGroup( $font_size_group ) {
		$id = "custom-" . mt_rand( 1000, 9999 ) . time();

		$custom_font_size_group = array_merge( [ 'id' => $id ], $font_size_group );

		$custom_font_sizes   = TypoLab::get_setting( 'custom_font_sizes', [] );
		$custom_font_sizes[] = $custom_font_size_group;

		TypoLab::set_setting( 'custom_font_sizes', $custom_font_sizes );

		// Reset Array of Defined Font Sizes
		self::initialize_font_size_groups();
	}

	/**
	 * Delete Custom Font Group.
	 *
	 * @param string $group_id
	 *
	 * @return bool
	 */
	public static function delete_custom_font_group( $group_id ) {
		$all_font_sizes    = self::get_font_sizes();
		$custom_font_sizes = [];
		$deleted           = false;

		foreach ( $all_font_sizes as $i => $font_size_group ) {
			if ( false == $font_size_group['builtin'] ) {
				if ( $group_id != $font_size_group['id'] ) {
					$custom_font_sizes[] = $font_size_group;
				} else {
					$deleted = true;
					unset( self::$font_sizes[ $i ] );
				}
			}
		}

		// Font sizes group has been deleted
		if ( $deleted ) {
			TypoLab::set_setting( 'custom_font_sizes', $custom_font_sizes );

			// Delete Any Defined Custom Font Size
			$sizes = self::get_only_sizes();

			foreach ( $sizes as $i => $size_group ) {
				if ( $group_id == $size_group['id'] ) {
					unset( $sizes[ $i ] );
				}
			}

			TypoLab::set_setting( 'font_sizes', $sizes );
		}

		return $deleted;
	}
}

new TypoLab_Font_Sizes();