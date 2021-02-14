<?php
/**
 * Kalium WordPress Theme
 *
 * Demo Content Pack class.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Demo_Content_Pack {

	/**
	 * Minimum recommended memory limit in megabytes.
	 *
	 * @var int
	 */
	const MINIMUM_MEMORY_LIMIT = 128;

	/**
	 * Minimum recommended max execution time.
	 *
	 * @var int
	 */
	const MINIMUM_EXECUTION_TIME = 90;

	/**
	 * Content pack ID.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Thumbnail URL.
	 *
	 * @var string
	 */
	public $thumbnail;

	/**
	 * Imports.
	 *
	 * @var Kalium_Demo_Content_Import_Type[]
	 */
	public $imports = [];

	/**
	 * Preview URL.
	 *
	 * @var string
	 */
	public $preview_url;

	/**
	 * Import instance.
	 *
	 * @var Kalium_Demo_Import_Instance
	 */
	protected $import_instance;

	/**
	 * Import manager.
	 *
	 * @var Kalium_Demo_Import_Manager
	 */
	protected $import_manager;

	/**
	 * Backup manager.
	 *
	 * @var Kalium_Demo_Backup_Manager
	 */
	protected $backup_manager;

	/**
	 * Import class mappings.
	 *
	 * @var array
	 */
	private $import_class_mapping = [
		'wordpress-import'      => 'Kalium_Demo_Content_Import_Type_WordPress_Import',
		'theme-options'         => 'Kalium_Demo_Content_Import_Type_Theme_Options',
		'revolution-slider'     => 'Kalium_Demo_Content_Import_Type_Revolution_Slider',
		'theme-custom-css'      => 'Kalium_Demo_Content_Import_Type_Theme_Custom_CSS',
		'wordpress-widgets'     => 'Kalium_Demo_Content_Import_Type_WordPress_Widgets',
		'typography-typolab'    => 'Kalium_Demo_Content_Import_Type_Typography',
		'install-child-theme'   => 'Kalium_Demo_Content_Import_Type_Install_Child_Theme',
		'woocommerce-prdctfltr' => 'Kalium_Demo_Content_Import_Type_WooCommerce_Product_Filter',
	];

	/**
	 * Plugin slugs mapping with their basename dir/plugin.php
	 *
	 * @var array
	 */
	private $plugin_slugs_mapping = [
		'advanced-custom-fields-pro'    => [
			'name'     => 'Advanced Custom Fields Pro',
			'basename' => 'advanced-custom-fields-pro/acf.php',
		],
		'js_composer'                   => [
			'name'     => 'WPBakery Page Builder',
			'basename' => 'js_composer/js_composer.php',
		],
		'portfolio-post-type'           => [
			'name'     => 'Portfolio Post Type',
			'basename' => 'portfolio-post-type/portfolio-post-type.php',
		],
		'revslider'                     => [
			'name'     => 'Slider Revolution',
			'basename' => 'revslider/revslider.php',
		],
		'woocommerce'                   => [
			'name'     => 'WooCommerce',
			'basename' => 'woocommerce/woocommerce.php',
		],
		'prdctfltr'                     => [
			'name'     => 'Product Filter for WooCommerce',
			'basename' => 'prdctfltr/prdctfltr.php',
		],
		'ninja-forms'                   => [
			'name'     => 'Ninja Forms',
			'basename' => 'ninja-forms/ninja-forms.php',
		],
		'mailchimp-for-wp'              => [
			'name'     => 'Mailchimp for WordPress',
			'basename' => 'mailchimp-for-wp/mailchimp-for-wp.php',
		],
		'bookingcom-official-searchbox' => [
			'name'     => 'Booking.com Official Search Box',
			'basename' => 'bookingcom-official-searchbox/booking-official-searchbox.php',
		],
	];

	/**
	 * Create instance.
	 *
	 * @param array $content_pack_entry
	 *
	 * @return self
	 */
	public static function create_instance( $content_pack_entry = [] ) {
		return new self( $content_pack_entry );
	}

	/**
	 * Constructor.
	 *
	 * @param array $content_pack_entry
	 *
	 * @return void
	 */
	public function __construct( $content_pack_entry = [] ) {

		// Content pack entry
		$content_pack_entry = wp_parse_args( $content_pack_entry, [
			'id'     => '',
			'name'   => '',
			'thumb'  => '',
			'import' => [],
			'url'    => '',
		] );

		// Set ID
		$this->id = $content_pack_entry['id'];

		// Name
		$this->name = $content_pack_entry['name'];

		// Thumbnail
		$this->thumbnail = sprintf( 'https://kaliumthemecom-laborator.netdna-ssl.com/images/demo-content/%s.jpg', $this->id );

		// Preview URL
		$this->preview_url = $content_pack_entry['url'];

		// Setup imports
		$this->setup_imports( $content_pack_entry['import'] );

		// Import instance
		$this->import_instance = new Kalium_Demo_Import_Instance( $this );

		// AJAX handler
		add_action( "kalium_demo_content_import_{$this->get_id()}", [ $this, '_ajax_handler' ], 10, 2 );
	}

	/**
	 * Get content pack ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get content pack name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get thumbnail URL.
	 *
	 * @return string
	 */
	public function get_thumbnail_url() {
		return $this->thumbnail;
	}

	/**
	 * Get imports.
	 *
	 * @return Kalium_Demo_Content_Import_Type[]
	 */
	public function get_imports() {
		return $this->imports;
	}

	/**
	 * Get import by ID.
	 *
	 * @param string $import_id
	 *
	 * @return Kalium_Demo_Content_Import_Type|null
	 */
	public function get_import_by_id( $import_id ) {
		foreach ( $this->get_imports() as $import ) {
			if ( $import->get_import_id() === $import_id ) {
				return $import;
			}
		}

		return null;
	}

	/**
	 * Get demo preview URL.
	 *
	 * @return string
	 */
	public function get_preview_url() {
		return $this->preview_url;
	}

	/**
	 * Get required plugins.
	 *
	 * @return array
	 */
	public function get_required_plugins() {
		$required_plugins = [];

		// Loop through imports to get required plugins
		foreach ( $this->get_imports() as $import ) {

			foreach ( $import->get_required_plugins() as $plugin_slug ) {

				if ( ! isset( $required_plugins[ $plugin_slug ] ) ) {
					$required_plugins[ $plugin_slug ] = $this->plugin_slugs_mapping[ $plugin_slug ];
				}
			}
		}

		return $required_plugins;
	}

	/**
	 * Demo content link to the import page.
	 *
	 * @return string
	 */
	public function get_link() {
		return admin_url( sprintf( 'admin-ajax.php?action=kalium_demos_import_content_pack&content-pack=%1$s', $this->get_id() ) );
	}

	/**
	 * Get import instance.
	 *
	 * @return Kalium_Demo_Import_Instance
	 */
	public function get_import_instance() {
		return $this->import_instance;
	}

	/**
	 * Get import type class name.
	 *
	 * @param string $import_type
	 *
	 * @return string
	 */
	public function get_import_type_class( $import_type ) {

		if ( isset( $this->import_class_mapping[ $import_type ] ) ) {
			return $this->import_class_mapping[ $import_type ];
		}

		return 'Kalium_Demo_Content_Import_Type';
	}

	/**
	 * Check if current demo is installed.
	 *
	 * @return bool
	 */
	public function is_installed() {
		$import_instance = $this->get_import_instance();

		return $import_instance->is_installed();
	}

	/**
	 * Setup imports related to package.
	 *
	 * @param array $imports
	 *
	 * @return void
	 */
	public function setup_imports( $imports ) {
		foreach ( $imports as $import_id => $import ) {

			// Create based on mapped class name
			$import_type_class = $this->get_import_type_class( $import['type'] );

			// Create instance and add to $this->imports[]
			$this->imports[] = new $import_type_class( $import_id, $import, $this );
		}
	}

	/**
	 * Getter and setter for Import Manager reference.
	 *
	 * @param Kalium_Demo_Import_Manager $new_instance
	 *
	 * @return Kalium_Demo_Import_Manager
	 */
	public function import_manager( $new_instance = null ) {

		// Set new import manager
		if ( ! is_null( $new_instance ) && $new_instance instanceof Kalium_Demo_Import_Manager ) {
			$this->import_manager = $new_instance;
		}

		return $this->import_manager;
	}

	/**
	 * Getter and setter for Backup Manager reference.
	 *
	 * @param Kalium_Demo_Backup_Manager $new_instance
	 *
	 * @return Kalium_Demo_Backup_Manager
	 */
	public function backup_manager( $new_instance = null ) {

		// Set new import manager
		if ( ! is_null( $new_instance ) && $new_instance instanceof Kalium_Demo_Backup_Manager ) {
			$this->backup_manager = $new_instance;
		}

		return $this->backup_manager;
	}

	/**
	 * Handler for AJAX requests of this content import instance.
	 *
	 * Ensured to serve only to authenticated and valid user levels.
	 *
	 * @param Kalium_Demo_Import_Manager $import_manager
	 * @param Kalium_Demo_Backup_Manager $backup_manager
	 *
	 * @return void
	 */
	public function _ajax_handler( $import_manager, $backup_manager ) {

		// Action
		switch ( kalium()->request->xhr_request( 'content-pack-action' ) ) {

			// Check server limits
			case 'check-server-limits':
				$this->import_action_check_server_limits();
				break;

			// Get tasks list
			case 'tasks-list':
				$this->import_action_tasks_list();
				break;

			// Get uninstall tasks list
			case 'uninstall-tasks-list':
				$this->import_action_uninstall_tasks_list();
				break;

			// Install plugin task
			case 'plugin-install':
				$plugin_slug = kalium()->request->xhr_request( 'slug' );
				$tgmpa_nonce = kalium()->request->xhr_request( 'tgmpa-nonce' );
				$installed   = wp_validate_boolean( kalium()->request->xhr_request( 'installed' ) );

				// TGMPA action
				$tgmpa_action     = $installed ? 'activate' : 'install';
				$tgmpa_action_var = 'tgmpa-' . $tgmpa_action;

				// Setup TGMPA vars
				$_GET['plugin']            = $plugin_slug;
				$_GET['tgmpa-nonce']       = $tgmpa_nonce;
				$_GET[ $tgmpa_action_var ] = $tgmpa_action . '-plugin';

				// Install or activate plugin
				kalium()->theme_plugins->_plugins_install_ajax();
				break;

			// Import content task
			case 'content-import':

				// Vars
				$import_id     = kalium()->request->xhr_request( 'import-id' );
				$import_action = kalium()->request->xhr_request( 'import-action' );
				$checked       = wp_validate_boolean( kalium()->request->xhr_request( 'checked' ) );
				$args          = (array) json_decode( stripslashes( kalium()->request->xhr_request( 'args' ) ) );

				// Response
				$response = [
					'success' => false,
				];

				// if the import type exists
				if ( $import = $this->get_import_by_id( $import_id ) ) {

					// Import instance
					$import_instance = $this->get_import_instance();

					// Set current import id in import instance
					$import_instance->set_import_id( $import_id );

					// Set checked status of import field
					$import->is_checked( $checked );

					// Set args values
					$import->set_args_values( $args );

					// Initialize Filesystem
					kalium()->filesystem->initialize();

					// Import action
					switch ( $import_action ) {

						// Do download
						case 'do_download':

							// Clear errors for the current import type
							$import_instance->clear_errors();

							// Run import download method
							$import->do_download();
							break;

						// Do backup
						case 'do_backup':
							$import->do_backup();
							break;

						// Do import
						case 'do_import':

							// Run import method
							$import->do_import();
							break;

						// Do import
						case 'do_complete':

							// Run import complete method
							$import->do_complete();
							break;

						// Do remove
						case 'do_remove':
							$import->do_remove();
							break;
					}

					// Set result
					$errors = $import->get_errors();

					if ( $errors->has_errors() ) {
						$errors->success = false;
						$response        = $errors;
					} else {
						$response['success'] = true;
					}
				}

				// Send response as text/html type because content import type may produce content/errors which cannot be handled with string buffer
				die( sprintf( '<script type="text/template" class="kalium-demo-content-import-response">%s</script>', wp_json_encode( $response ) ) );
				break;

			// Set content pack import state
			case 'set-install-state':

				// Vars
				$import_instance  = $this->get_import_instance();
				$imported_content = $import_instance->get_imported_content_type();
				$imports          = $this->get_imports();
				$min_imports      = 3 / 4; // when most of content is imported

				// Install state
				$install_state = count( $imported_content ) > count( $imports ) * $min_imports;

				// Uninstalled
				if ( ! $install_state ) {
					$backup_manager->delete_backup_options();
				}

				// Set install state
				$import_instance->set_install_state( $install_state );

				// Response
				wp_send_json( [
					'installed' => $install_state,
				] );
				break;
		}
	}

	/**
	 * Get tasks list to import this demo content pack.
	 *
	 * @return Kalium_Demo_Content_Task[]
	 */
	private function get_tasks_list() {

		// Tasks list
		$tasks = [];

		// Required plugins tasks
		foreach ( $this->get_required_plugins() as $plugin_slug => $plugin ) {

			// Plugin data for task import
			$plugin_data = array_merge( $plugin, [
				'slug'      => $plugin_slug,
				'installed' => kalium()->is->plugin_installed( $plugin['basename'] ),
			] );

			// Install/activate nonce for TGMPA
			$plugin_data['tgmpa-nonce'] = wp_create_nonce( 'tgmpa-' . ( $plugin_data['installed'] ? 'activate' : 'install' ) );

			// Task instance
			$task = new Kalium_Demo_Content_Task( 'plugin-install', $plugin_data );

			// Set as completed if plugin is active
			if ( kalium()->is->plugin_active( $plugin['basename'] ) ) {
				$task->mark_complete( true );
			}

			// Add task
			$tasks[] = $task;
		}

		// Import type tasks
		foreach ( $this->get_imports() as $import ) {

			// Task instance
			$task = new Kalium_Demo_Content_Task( 'content-import', [
				'import_id'   => $import->get_import_id(),
				'import_type' => $import->get_type(),
				'import_name' => $import->get_name(),
			] );

			$tasks[] = $task;
		}

		return $tasks;
	}

	/**
	 * Get tasks list to uninstall demo content type entries.
	 *
	 * @return Kalium_Demo_Content_Task[]
	 */
	private function get_uninstall_tasks_list() {

		// Tasks list
		$tasks = [];

		// Get imported content types
		foreach ( $this->get_import_instance()->get_imported_content_type() as $content_type ) {

			// Task instance
			$task = new Kalium_Demo_Content_Task( 'content-import', [
				'uninstall'   => true,
				'import_id'   => $content_type['id'],
				'import_type' => $content_type['type'],
				'import_name' => $content_type['name'],
			] );

			$tasks[] = $task;
		}

		return $tasks;
	}

	/**
	 * Import action: Check server limits.
	 *
	 * @return void
	 */
	private function import_action_check_server_limits() {

		// Init System Status vars
		Laborator_System_Status::init_vars();

		// Send JSON response
		wp_send_json( [

			// Current server vars
			'filesystem_method'          => get_filesystem_method(),
			'wp_memory_limit'            => Laborator_System_Status::get_var( 'wp_memory_limit' ),
			'max_execution_time'         => Laborator_System_Status::get_var( 'max_execution_time' ),
			'domdocument'                => Laborator_System_Status::get_var( 'domdocument' ),
			'remote_get'                 => wp_remote_get( kalium()->theme_license->get_api_server_url(), [ 'timeout' => 10 ] ),

			// Recommended values
			'recommended_memory_limit'   => self::MINIMUM_MEMORY_LIMIT,
			'recommended_execution_time' => self::MINIMUM_EXECUTION_TIME,
		] );
	}

	/**
	 * Import action: Get Import tasks list in AJAX request.
	 *
	 * @return void
	 */
	private function import_action_tasks_list() {

		// Tasks list to execute
		$tasks = $this->get_tasks_list();

		// Send JSON response
		wp_send_json( $tasks );
	}

	/**
	 * Import action: Get import uninstall task list in AJAX request.
	 */
	private function import_action_uninstall_tasks_list() {

		// Tasks list to execute
		$tasks = $this->get_uninstall_tasks_list();

		// Send JSON response
		wp_send_json( $tasks );
	}
}
