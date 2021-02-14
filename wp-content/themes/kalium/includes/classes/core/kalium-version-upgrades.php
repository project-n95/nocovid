<?php
/**
 * Kalium WordPress Theme
 *
 * Theme version history and upgrade hooks.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Version_Upgrades {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	public static $instance;

	/**
	 * Version upgrades entries.
	 *
	 * @var array
	 */
	private $version_upgrades = [];

	/**
	 * Class instance.
	 *
	 * @return self
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		// Assign instance
		self::$instance = $this;

		// Admin init
		add_action( 'admin_init', [ $this, '_init_version_upgrades' ] );
	}

	/**
	 * Init version upgrades.
	 *
	 * @return void
	 */
	public function _init_version_upgrades() {
		$this->version_upgrades = get_option( 'kalium_version_upgrades', [] );
		$this->process_legacy_version_upgrades();
		$this->process_version_upgrades();
		$this->run_hooks();
	}

	/**
	 * Get versions list.
	 *
	 * @return string[]
	 */
	public function get_versions_list() {
		return wp_list_pluck( $this->version_upgrades, 'version' );
	}

	/**
	 * Get previous version.
	 *
	 * @return string|null
	 */
	public function get_previous_version() {
		$current_version = kalium()->get_version();
		$versions_list   = $this->get_versions_list();

		// Sort ascending
		sort( $versions_list );

		while ( $previous_version = array_pop( $versions_list ) ) {
			if ( version_compare( $previous_version, $current_version, '<' ) ) {
				return $previous_version;
			}
		}

		return null;
	}

	/**
	 * Get data from from latest or specified version.
	 *
	 * @param string $var
	 * @param string $version_number
	 *
	 * @return mixed|null
	 */
	public function get_data( $var = null, $version_number = null ) {
		if ( ! $version_number ) {
			$version_number = kalium()->get_version();
		}

		foreach ( $this->version_upgrades as & $version_upgrade ) {
			if ( $version_number === $version_upgrade['version'] ) {
				if ( is_null( $var ) ) {
					return $version_upgrade['data'];
				}

				return kalium_get_array_key( $version_upgrade['data'], $var );
			}
		}

		return null;
	}

	/**
	 * Set data on from latest or specified version.
	 *
	 * @param string $var
	 * @param mixed  $value
	 * @param string $version_number
	 *
	 * @return void
	 */
	public function set_data( $var, $value, $version_number = null ) {
		if ( ! $version_number ) {
			$version_number = kalium()->get_version();
		}

		foreach ( $this->version_upgrades as & $version_upgrade ) {
			if ( $version_number === $version_upgrade['version'] ) {
				$version_upgrade['data'][ $var ] = $value;
			}
		}

		$this->save_version_upgrades();
	}

	/**
	 * Check if given version was installed.
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	public function version_was_installed( $version ) {
		return in_array( $version, $this->get_versions_list() );
	}

	/**
	 * Save version upgrades entries.
	 *
	 * @return void
	 */
	private function save_version_upgrades() {
		update_option( 'kalium_version_upgrades', $this->version_upgrades );
	}

	/**
	 * Process version upgrades.
	 *
	 * @return void
	 */
	private function process_version_upgrades() {
		$current_version    = kalium()->get_version();
		$installed_versions = $this->get_versions_list();

		// Register version upgrade
		if ( ! in_array( $current_version, $installed_versions ) ) {
			$version_upgrade = [
				'version' => $current_version,
				'time'    => time(),
				'data'    => [],
			];

			$this->version_upgrades[] = $version_upgrade;
			$this->save_version_upgrades();
		}
	}

	/**
	 * Process version upgrades.
	 *
	 * @return void
	 */
	private function process_legacy_version_upgrades() {
		if ( is_string( kalium()->helpers->array_first( $this->version_upgrades ) ) ) {
			$version_upgrades = [];

			foreach ( $this->version_upgrades as $previous_version ) {
				$version_upgrades[] = [
					'version' => $previous_version,
					'time'    => 0,
					'data'    => [],
				];
			}

			$this->version_upgrades = $version_upgrades;
			$this->save_version_upgrades();
		}

		// Remove pre 3.0 version upgrades
		$do_update = false;
		foreach ( $this->version_upgrades as $i => $version_upgrade ) {
			if ( version_compare( $version_upgrade['version'], '3.0', '<' ) ) {
				unset( $this->version_upgrades[ $i ] );
				$do_update = true;
			}
		}

		// Update versions list
		if ( $do_update ) {
			$this->save_version_upgrades();
		}
	}

	/**
	 * Run version upgrade hooks.
	 *
	 * @return void
	 */
	private function run_hooks() {
		$current_version  = kalium()->get_version();
		$previous_version = $this->get_previous_version();

		// Version upgrade
		if ( $previous_version ) {

			/**
			 * Run version upgrade hooks.
			 *
			 * Note that hooked functions will be executed every time until the implemented the logic to execute only once.
			 *
			 * @param self        $this
			 * @param string      $current_version
			 * @param string|null $previous_version
			 */
			do_action( 'kalium_version_upgrade', $this, $current_version, $previous_version );
		}
	}
}
