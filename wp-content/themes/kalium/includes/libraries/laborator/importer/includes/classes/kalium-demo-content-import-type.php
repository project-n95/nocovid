<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Type class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Import_Type {

	/**
	 * Demo content source provider.
	 *
	 * @var string
	 */
	public static $source_provider = 'https://api.laborator.co/downloads/kalium/demo-content/%1$s/%2$s';

	/**
	 * Import type ID.
	 *
	 * @var string
	 */
	protected $import_id;

	/**
	 * Content pack type.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Custom import name.
	 */
	protected $name;

	/**
	 * Content pack sources.
	 *
	 * @var array
	 */
	protected $sources = [];

	/**
	 * Required plugins.
	 *
	 * @var array
	 */
	protected $required_plugins = [];

	/**
	 * Content pack options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Content pack reference.
	 *
	 * @var Kalium_Demo_Content_Pack
	 */
	protected $content_pack;

	/**
	 * Checkbox state.
	 *
	 * @var bool
	 */
	protected $is_checked;

	/**
	 * Checkbox status.
	 *
	 * @var bool
	 */
	protected $is_disabled;

	/**
	 * User submitted args values.
	 *
	 * @var array
	 */
	protected $args_values = [];

	/**
	 * Errors container.
	 *
	 * @var WP_Error
	 */
	protected $errors;

	/**
	 * Constructor.
	 *
	 * @param string                   $import_id
	 * @param array                    $args
	 * @param Kalium_Demo_Content_Pack $content_pack
	 *
	 * @return void
	 */
	public function __construct( $import_id, $args, $content_pack ) {

		// Args
		$args = wp_parse_args( $args, [
			'name'     => '',
			'type'     => '',
			'src'      => '',
			'requires' => '',
			'options'  => [],
			'checked'  => true,
			'disabled' => false,
		] );

		// Import id
		$this->import_id = $import_id;

		// Set content pack reference
		$this->content_pack = $content_pack;

		// Import name
		$this->name = $args['name'];

		// Content type
		$this->type = $args['type'];

		// Content pack options
		$this->options = $args['options'];

		// Checkbox state
		$this->is_checked = $args['checked'];

		// Checkbox status
		$this->is_disabled = $args['disabled'];

		// Errors
		$this->errors = new WP_Error();

		// Register sources
		$this->register_sources( $args['src'] );

		// Required plugins
		$this->register_required_plugins( $args['requires'] );
	}

	/**
	 * Get content pack associated with the import type.
	 *
	 * @return Kalium_Demo_Content_Pack
	 */
	public function get_content_pack() {
		return $this->content_pack;
	}

	/**
	 * Get import ID.
	 *
	 * @return string
	 */
	public function get_import_id() {
		return $this->import_id;
	}

	/**
	 * Import name to display.
	 *
	 * @return string
	 */
	public function get_name() {

		// Import name
		if ( ! empty( $this->name ) ) {
			return $this->name;
		}

		return $this->get_import_id();
	}

	/**
	 * Get import type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get source URL.
	 *
	 * @param string $file_name
	 *
	 * @return string
	 */
	public function get_source_url( $file_name ) {
		return sprintf( self::$source_provider, $this->get_content_pack()->get_id(), $file_name );
	}

	/**
	 * Get sources list.
	 *
	 * @return array
	 */
	public function get_sources() {
		return $this->sources;
	}

	/**
	 * Returns slugs of required plugins.
	 *
	 * @return array
	 */
	public function get_required_plugins() {
		return $this->required_plugins;
	}

	/**
	 * Get content pack options.
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get content type fields to display below the checkbox.
	 *
	 * @return array
	 */
	public function get_args_fields() {
		return [];
	}

	/**
	 * Set args values for args fields from user input.
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function set_args_values( $args ) {
		if ( is_array( $args ) ) {
			$this->args_values = $args;
		}
	}

	/**
	 * Get errors instance to check whether there are error reports.
	 *
	 * @return WP_Error
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Check if all required plugins are activated.
	 *
	 * @return bool
	 */
	public function plugins_are_active() {
		foreach ( $this->get_required_plugins() as $plugin_slug ) {
			$plugin_basename = kalium()->helpers->get_plugin_basename( $plugin_slug );

			if ( false === kalium()->is->plugin_active( $plugin_basename ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Getter and setter for checkbox state.
	 *
	 * @param bool $new_state
	 *
	 * @return bool
	 */
	public function is_checked( $new_state = null ) {
		if ( ! is_null( $new_state ) ) {
			$this->is_checked = wp_validate_boolean( $new_state );
		}

		return $this->is_checked;
	}

	/**
	 * Getter and setter for checkbox status.
	 *
	 * @param bool $new_state
	 *
	 * @return bool
	 */
	public function is_disabled( $new_state = null ) {
		if ( ! is_null( $new_state ) ) {
			$this->is_disabled = wp_validate_boolean( $new_state );
		}

		return $this->is_disabled;
	}

	/**
	 * Check if current content type is imported.
     *
     * @return bool
	 */
    public function is_imported() {
		$imported_content_type = $this->get_content_pack()->get_import_instance()->get_imported_content_type();
		return isset( $imported_content_type[ $this->get_import_id() ] );
    }

	/**
	 * Download content package from sources.
	 *
	 * Downloads are saved in wp-content/uploads folder.
	 *
	 * @return void
	 */
	public function do_download() {

		// Content pack
		$content_pack = $this->get_content_pack();

		// Download resources
		$download = $content_pack->import_manager()->download_content_pack_resources( $this );

		// Import instance
		$import_instance = $content_pack->get_import_instance();

		// Download failed
		if ( is_wp_error( $download ) ) {

			// Report errors to current fetch() request
			$this->errors = $download;

			// Assign the error to import instance as well
			$import_instance->add_error( $download );
		} else {
			// Download succeeded
			$import_instance->set_download_success();
		}
	}

	/**
	 * Create necessary backups before importing content pack.
	 *
	 * @return void
	 */
	public function do_backup() {
	}

	/**
	 * Start import of the content package.
	 *
	 * @return void
	 */
	public function do_import() {

		// Check if required plugins are active
		if ( ! $this->plugins_are_active() ) {
			$this->errors->add( 'kalium_demo_content_import_plugins_not_active', sprintf( 'Required plugins are not active, <strong>%s</strong> cannot be imported.', $this->get_name() ) );
		}
	}

	/**
	 * Hooks and callbacks to execute after import is finished.
	 *
	 * @return void
	 */
	public function do_complete() {

		// Import instance
		$content_pack    = $this->get_content_pack();
		$import_instance = $content_pack->get_import_instance();
		$do_download     = $import_instance->get_task_args( 'download' );
		$do_import       = $import_instance->get_task_args( 'import' );

		// Mark complete if download and import were successful
		if ( kalium_get_array_key( $do_download, 'success' ) && kalium_get_array_key( $do_import, 'success' ) ) {
			$import_instance->set_successful( true );
		}
	}

	/**
	 * Actions to execute when removing the current content import type.
	 *
	 * @return void
	 */
	public function do_remove() {

		// Import instance
		$import_instance = $this->get_content_pack()->get_import_instance();

		// Clear task args values
		$import_instance->clear_task_args();

		// Mark as removed
		$import_instance->set_task_arg_value( 'remove', 'success', true );

		// Mark as not completed
		$import_instance->set_successful( false );
	}

	/**
	 * Render content type import checkbox.
	 *
	 * @return void
	 */
	public function render_import_field() {

		// Import ID
		$import_id = $this->get_import_id();

		// Input classes
		$input_classes = [
			'import-field',
		];

		if ( $this->is_disabled() ) {
			$input_classes[] = 'disabled';
		}

		?>
        <div class="kalium-demos__content-pack-view-imports-checkbox">
            <input type="checkbox" name="imports[<?php echo esc_attr( $import_id ); ?>][value]" <?php kalium_class_attr( $input_classes ); ?> id="import_<?php echo esc_html( $import_id ); ?>" value="<?php echo esc_attr( $import_id ); ?>" <?php
			checked( $this->is_checked() );
			disabled( $this->is_disabled() ); ?>>
        </div>
        <label class="kalium-demos__content-pack-view-imports-label" for="import_<?php echo esc_html( $import_id ); ?>">
			<?php echo esc_html( $this->get_name() ); ?>
        </label>
		<?php
	}

	/**
	 * Render content type import args fields.
	 *
	 * @return void
	 */
	public function render_import_args_fields() {

		// Args fields
		$args_fields   = $this->get_args_fields();

		// Args fields
		if ( is_array( $args_fields ) && ! empty( $args_fields ) ) :

			// Args fields wrapper start
			echo '<div class="kalium-demos__content-pack-view-imports-args-fields">';

			// Render args fields
			foreach ( $args_fields as $field ) :

				// Field vars
				$type = $field['type'];
				$name  = $field['name'];
				$value = $field['value'];
				$title = $field['title'];

				$args_field_classes = [
					'kalium-demos__content-pack-view-imports-args-field',
					'kalium-demos__content-pack-view-imports-args-field--' . $type,
				];
				?>
                <div <?php kalium_class_attr( $args_field_classes ); ?>>

					<?php
					// Input classes
					$input_classes = [
						'import-arg-field',
					];

					if ( $this->is_disabled() ) {
						$input_classes[] = 'disabled';
					}

					// Checkbox field type
					if ( 'checkbox' === $field['type'] ) :
						?>
                        <div class="kalium-demos__content-pack-view-imports-args-field-checkbox" data-field-name="<?php echo esc_attr( $name ); ?>">
                            <input type="checkbox" name="imports[<?php echo esc_attr( $this->get_import_id() ); ?>][<?php echo esc_attr( $name ); ?>]" <?php kalium_class_attr( $input_classes );; ?> id="import_<?php echo esc_attr( $name . '_' . $value ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php
							checked( $this->is_checked() );
							?>>
                        </div>
                        <label for="import_<?php echo esc_attr( $name . '_' . $value ); ?>" class="kalium-demos__content-pack-view-imports-args-field-label">
							<?php echo esc_html( $title ); ?>
                        </label>
					<?php
					endif;
					?>

                </div>
			<?php

			endforeach;

			// Args fields wrapper end
			echo '</div>';

		endif;
	}

	/**
	 * Register sources.
	 *
	 * @param string|array $source
	 *
	 * @return void
	 */
	private function register_sources( $source ) {

		// Convert to array, that allows multiple sources
		if ( is_string( $source ) ) {
			$source = array_filter( explode( ',', $source ) );
		}

		// Only if this is a valid array
		if ( is_array( $source ) ) {

			foreach ( $source as $file_name ) {

				// Wrap $source_url with license key parameter
				$source_url = Kalium_Demo_Content_Importer::instance()->source_url( $this->get_source_url( $file_name ) );

				$this->sources[] = [
					'name' => $file_name,
					'url'  => $source_url,
				];
			}
		}
	}

	/**
	 * Register required plugins.
	 *
	 * @param string|array $required_plugins
	 *
	 * @return void
	 */
	private function register_required_plugins( $required_plugins ) {

		// Convert to array, that allows multiple plugin slugs
		if ( is_string( $required_plugins ) ) {
			$required_plugins = array_filter( explode( ',', $required_plugins ) );
		}

		// Only if this is a valid array
		if ( is_array( $required_plugins ) ) {

			foreach ( $required_plugins as $plugin_slug ) {
				$this->required_plugins[] = $plugin_slug;
			}
		}
	}
}
