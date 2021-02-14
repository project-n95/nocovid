<?php
/**
 * Kalium WordPress Theme
 *
 * Font Export/Import Manager.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class TypoLab_Font_Export_Import {

	/**
	 * Imported font IDs.
	 *
	 * @var array
	 */
	public $imported_font_ids = [];

	/**
	 * Export fonts and settings.
	 *
	 * @param bool $font_faces
	 * @param bool $font_sizes
	 * @param bool $font_settings
	 *
	 * @return array
	 */
	public function export( $font_faces = true, $font_sizes = true, $font_settings = false ) {
		$export_object = [];

		// Export Font Faces
		if ( $font_faces ) {
			$font_faces_list            = TypoLab::get_fonts( true );
			$export_object['fontFaces'] = $font_faces_list;
		}

		// Export Font Sizes
		if ( $font_sizes ) {
			$font_sizes        = TypoLab::get_setting( 'font_sizes' );
			$custom_font_sizes = TypoLab::get_setting( 'custom_font_sizes' );

			$export_object['fontSizes']                         = [];
			$export_object['fontSizes']['sizes']                = $font_sizes;
			$export_object['fontSizes']['customFontSizeGroups'] = $custom_font_sizes;
		}

		// Export Font Settings
		if ( $font_settings ) {
			$font_settings = TypoLab::get_font_settings();

			$export_settings_vars = [
				'font_placement',
				'font_combining'
			];

			$export_object['fontSettings'] = [];

			foreach ( $export_settings_vars as $settings_var ) {
				if ( isset( $font_settings[ $settings_var ] ) ) {
					$export_object['fontSettings'][ $settings_var ] = $font_settings[ $settings_var ];
				}
			}
		}

		return $export_object;
	}

	/**
	 * Import fonts and settings.
	 *
	 * @param array|string $font_settings
	 *
	 * @return bool|WP_Error
	 */
	public function import( $font_settings ) {
		$results = [];

		// Unserialize font settings
		if ( is_string( $font_settings ) ) {
			$font_settings = unserialize( base64_decode( $font_settings ) );
		}

		// If serialization is not successful
		if ( ! is_array( $font_settings ) ) {
			return new WP_Error( 'typolab_invalid_import_type', 'Could not parse font import settings, make sure import code is valid!' );
		}

		// Import Font Faces
		if ( isset( $font_settings['fontFaces'] ) ) {
			$results[] = $this->import_font_faces( $font_settings['fontFaces'] );
		}

		// Import Font Sizes
		if ( isset( $font_settings['fontSizes'] ) ) {
			$results[] = $this->import_font_sizes( $font_settings['fontSizes'] );
		}

		// Import Font Sizes
		if ( isset( $font_settings['fontSettings'] ) ) {
			$results[] = $this->import_font_settings( $font_settings['fontSettings'] );
		}

		return count( array_filter( $results ) ) > 0;
	}

	/**
	 * Import font faces.
	 *
	 * @param array $import_fonts
	 *
	 * @return bool
	 */
	public function import_font_faces( $import_fonts ) {
		$fonts = TypoLab::get_setting( 'registered_fonts', [] );

		foreach ( $import_fonts as $font ) {
			$old_id  = $font['id'];
			$new_id  = 'font-' . TypoLab::new_id();
			$options = kalium_get_array_key( $font, 'options' );

			// Update font ID selector
			if ( ! empty( $font['options']['selectors'] ) ) {
				foreach ( $font['options']['selectors'] as & $selector ) {
					$selector['selector'] = str_replace( ".{$old_id}", ".{$new_id}", $selector['selector'] );
				}
			}

			// Download font for "uploaded-font" source
			if ( 'uploaded-font' === $font['source'] ) {
				TypoLab_Uploaded_Font::download_and_replace_font_files( $font );
			} // Install Premium font
			else if ( 'premium' === $font['source'] ) {
				TypoLab_Premium_Fonts::install_font( $font['family'], $font );
			} // Install Font Squirrel font
			else if ( 'font-squirrel' === $font['source'] ) {
				TypoLab_Font_Squirrel::install_font( $font['family'], $font );
			}

			// Set new ID
			$font['id'] = $new_id;

			// Add to fonts list
			$fonts[] = $font;

			// Imported font ids
			$this->imported_font_ids[] = $new_id;
		}

		TypoLab::set_setting( 'registered_fonts', $fonts );

		return true;
	}

	/**
	 * Import font sizes and custom font size groups.
	 *
	 * @param array $font_sizes
	 *
	 * @return bool
	 */
	public function import_font_sizes( $font_sizes ) {
		$results = [];

		$current_sizes              = TypoLab_Font_Sizes::get_only_sizes();
		$current_custom_size_groups = TypoLab::get_setting( 'custom_font_sizes', [] );

		// Import Font Sizes
		if ( ! empty( $font_sizes['sizes'] ) ) {
			$new_font_sizes = [];

			foreach ( $font_sizes['sizes'] as $i => $size ) {
				$exists = false;

				// Check existing font size if already exists
				foreach ( $current_sizes as $j => $current_size ) {
					// Replace existing font size
					if ( $size['id'] === $current_size['id'] ) {
						$exists              = true;
						$current_sizes[ $j ] = $size;
					}
				}

				// Create new font size parameters
				if ( ! $exists ) {
					$new_font_sizes[] = $size;
				}
			}

			// Save new Font Sizes
			$font_sizes_concatenated = array_merge( $current_sizes, $new_font_sizes );
			TypoLab::set_setting( 'font_sizes', $font_sizes_concatenated );

			// This step was successful
			$results[] = true;
		}

		// Import Custom Font Size Groups
		if ( ! empty( $font_sizes['customFontSizeGroups'] ) ) {
			$new_font_size_groups = [];

			foreach ( $font_sizes['customFontSizeGroups'] as $i => $font_size_group ) {
				$exists = false;

				// Check current custom font size group if already exists
				foreach ( $current_custom_size_groups as $j => $current_font_size_group ) {

					// Replace existing custom font size group
					if ( $font_size_group['id'] === $current_font_size_group['id'] ) {
						$exists                           = true;
						$current_custom_size_groups[ $j ] = $font_size_group;
						break;
					}
				}

				// Create new font size group
				if ( ! $exists ) {
					$new_font_size_groups[] = $font_size_group;
				}
			}


			// Save new custom Font Size Groups
			$custom_font_sizes_concatenated = array_merge( $current_custom_size_groups, $new_font_size_groups );
			TypoLab::set_setting( 'custom_font_sizes', $custom_font_sizes_concatenated );

			// This step was successful
			$results[] = true;
		}

		return true;
	}

	/**
	 * Import Font Settings
	 *
	 * @param $import_font_settings
	 *
	 * @return bool
	 */
	public function import_font_settings( $import_font_settings ) {

		if ( is_array( $import_font_settings ) ) {
			$font_settings_concatenated = array_merge( TypoLab::get_font_settings(), $import_font_settings );
			TypoLab::set_setting( 'font_settings', $font_settings_concatenated );

			return true;
		}

		return false;
	}

	/**
	 * Get imported font ids.
	 *
	 * @return array
	 */
	public function get_imported_font_ids() {
		return $this->imported_font_ids;
	}
}