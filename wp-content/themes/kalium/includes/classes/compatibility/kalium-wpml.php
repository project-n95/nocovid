<?php
/**
 * Kalium WordPress Theme
 *
 * WPML compatibility class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_WPML {

	/**
	 * Translatable content builder fields.
	 *
	 * @var array
	 */
	private $content_builder_fields = [
		'top_header_bar_content_left',
		'top_header_bar_content_right',
		'custom_header_content_left',
		'custom_header_content_right',
		'custom_header_content',
	];

	/**
	 * Translatable content builder option fields.
	 *
	 * @var array
	 */
	private $translatable_content_builder_option_fields = [
		'raw-text' => [
			'text',
		],
	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! kalium()->is->wpml_active() ) {
			return;
		}

		// Register translatable option fields for content builder elements in Theme Options
		add_action( 'of_save_options_after', [ $this, 'content_builder_register_strings' ] );

		// Translate option field values for content builder elements in Theme Options
		add_action( 'wp', [ $this, 'content_builder_translate_option_fields' ] );
	}

	/**
	 * Register translatable strings for content builder option entries.
	 *
	 * @return void
	 */
	public function content_builder_register_strings() {
		foreach ( $this->content_builder_fields as $field_id ) {
			$field_value = kalium_get_theme_option( $field_id );
			$content     = kalium_parse_content_builder_field( $field_value );

			foreach ( $content['entries'] as $i => $entry ) {
				$content_type = $entry['contentType'];
				$options      = $entry['options'];
				$hash         = substr( md5( $field_id . $i ), 0, 8 );
				$entry_name   = $content_type . '-' . $hash;

				foreach ( $options as $option_name => $option_value ) {
					if ( ! empty( $this->translatable_content_builder_option_fields[ $content_type ] ) && in_array( $option_name, $this->translatable_content_builder_option_fields[ $content_type ] ) ) {
						kalium_wpml_register_single_string( $entry_name, $option_value );
					}
				}
			}
		}
	}

	/**
	 * Translate content builder option entries.
	 *
	 * @return void
	 */
	public function content_builder_translate_option_fields() {
		foreach ( $this->content_builder_fields as $field_id ) {
			add_filter( 'get_data_' . $field_id, function ( $value ) use ( $field_id ) {
				static $translated_value;

				if ( ! empty( $translated_value ) ) {
					return $translated_value;
				}

				$content = json_decode( $value, true );

				if ( isset( $content['entries'] ) && is_array( $content['entries'] ) ) {
					foreach ( $content['entries'] as $i => & $entry ) {
						if ( empty( $entry['options'] ) ) {
							continue;
						}

						$content_type = $entry['contentType'];
						$options      = $entry['options'];
						$hash         = substr( md5( $field_id . $i ), 0, 8 );
						$entry_name   = $content_type . '-' . $hash;

						foreach ( $options as $option_name => $option_value ) {
							if ( ! empty( $this->translatable_content_builder_option_fields[ $content_type ] ) && in_array( $option_name, $this->translatable_content_builder_option_fields[ $content_type ] ) ) {
								$entry['options'][ $option_name ] = kalium_wpml_translate_single_string( $option_value, $entry_name );
							}
						}
					}
				}

				$translated_value = wp_json_encode( $content );

				return $translated_value;
			} );
		}
	}
}
