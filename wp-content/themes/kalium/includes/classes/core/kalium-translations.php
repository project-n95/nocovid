<?php
/**
 *    Kalium WordPress Theme
 *
 *    Laborator.co
 *    www.laborator.co
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

class Kalium_Translations {

	/**
	 * Translations repo.
	 *
	 * @var string
	 */
	private $repository = 'https://api.github.com/repos/arl1nd/Kalium-Translations';

	/**
	 * If there is a Kalium translation being loaded.
	 *
	 * @var bool
	 */
	private $has_translation = false;

	/**
	 * Translation update data.
	 *
	 * @var array
	 */
	private $translation_data;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// After setup theme
		add_action( 'after_setup_theme', [ $this, '_after_setup_theme' ] );

		// Check if there are existing translations installed
		add_action( 'load_textdomain', [ $this, '_load_text_domain' ], 100, 2 );
	}

	/**
	 * Retrieve translation updates and set translation hooks.
	 */
	public function _after_setup_theme() {

		// Retrieve translation updates
		$this->retrieve_translation_updates();

		// Check for translation updates
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'check_translation_updates' ], 100 );
		add_filter( 'pre_set_transient_update_themes', [ $this, 'check_translation_updates' ], 100 );

		// Delete translations cache after update
		add_action( 'upgrader_process_complete', [ $this, 'clean_translations_updates' ], 10, 2 );
	}

	/**
	 * Retrieve translation updates and cache them for a while
	 */
	private function retrieve_translation_updates() {
		global $pagenow;

		if ( ! is_admin() ) {
			return;
		}

		$translation_data = get_option( 'kalium_upgrader_translations', [
			'last_check'             => 0,
			'available_translations' => [],
			'translation_updates'    => [],
		] );

		$check_interval = 86400; // 1 day

		// Force check updates (lower interval)
		if ( 'update-core.php' == $pagenow && kalium()->request->has( 'force-check' ) ) {
			$check_interval = 300; // 5 minutes
			delete_site_transient( 'update_themes' );
		}

		// Check for updates
		if ( $translation_data['last_check'] < ( time() - $check_interval ) ) {
			$translation_data['available_translations'] = $translation_data['translation_updates'] = [];

			// Make sure to load parent theme text domain when
			if ( is_child_theme() ) {
				load_theme_textdomain( 'kalium', kalium()->locate_file( 'languages' ) );
			}

			// Current locale
			$locale = get_locale();

			// If translation doesn't exists
			if ( false === $this->has_translation ) {
				$translation = $this->get_remote_translation( $locale );

				if ( $translation ) {
					$translation_data['available_translations'][ $locale ] = [
						'type'       => 'theme',
						'slug'       => 'kalium',
						'language'   => $locale,
						'package'    => $translation->download_url,
						'version'    => kalium()->get_version(),
						'autoupdate' => 0
					];
				}
			} // Check for translation updates
			else {
				global $l10n;

				$kalium_l10n = kalium_get_array_key( $l10n, 'kalium' );

				if ( $kalium_l10n && ! empty( $kalium_l10n->headers['X-Translation-Version'] ) ) {
					$current_translation_version = $kalium_l10n->headers['X-Translation-Version'];
					$remote_translation_version  = $this->get_remote_translation_version( $locale );

					if ( $remote_translation_version && version_compare( $remote_translation_version, $current_translation_version, '>' ) ) {

						$translation_data['translation_updates'][ $locale ] = [
							'type'       => 'theme',
							'slug'       => 'kalium',
							'language'   => $locale,
							'package'    => sprintf( 'https://raw.githubusercontent.com/arl1nd/Kalium-Translations/master/%s.zip', $locale ),
							'version'    => $remote_translation_version,
							'autoupdate' => 0
						];
					}
				}
			}

			// Set last checked
			$translation_data['last_check'] = time();

			// Update data
			update_option( 'kalium_upgrader_translations', $translation_data );
		}

		// Set translation update data
		$this->translation_data = $translation_data;
	}

	/**
	 * Clean translation updates
	 *
	 * @param $upgrader
	 * @param $data
	 */
	public function clean_translations_updates( $upgrader, $data ) {

		if ( ! empty( $data['type'] ) && 'translation' === $data['type'] ) {

			$translation_data = get_option( 'kalium_upgrader_translations', [
				'last_check'             => 0,
				'available_translations' => [],
				'translation_updates'    => [],
			] );

			$translation_data['available_translations'] = $translation_data['translation_updates'] = [];

			update_option( 'kalium_upgrader_translations', $translation_data );
		}
	}

	/**
	 * Load text domain
	 *
	 * @param string $domain
	 * @param string $mofile
	 */
	public function _load_text_domain( $domain, $mofile ) {
		if ( 'kalium' === $domain && file_exists( $mofile ) && false !== strpos( $mofile, 'wp-content/languages/themes' ) ) {
			$this->has_translation = true;
		}
	}

	/**
	 * Check for translation updates.
	 *
	 * @param $transient
	 *
	 * @return mixed
	 */
	public function check_translation_updates( $transient ) {

		// Translation updates
		$translation_data    = $this->translation_data;
		$translation_updates = [];

		foreach ( array( 'available_translations', 'translation_updates' ) as $translation_type ) {
			if ( ! empty( $translation_data[ $translation_type ] ) ) {
				$translation_updates = array_merge( $translation_updates, $translation_data[ $translation_type ] );
			}
		}

		if ( ! empty( $translation_updates ) ) {

			foreach ( $translation_updates as $translation_update ) {
				if ( version_compare( $translation_update['version'], kalium()->get_version(), '<=' ) ) {
					$transient->translations[] = $translation_update;
				}
			}
		}

		return $transient;
	}

	/**
	 * Get avaialble translation for current locale
	 *
	 * @param $locale
	 *
	 * @return mixed|null (object) $response (name, path, sha, size, url, html_url, git_url, download_url, type, content, encoding, _links)
	 */
	private function get_remote_translation( $locale ) {
		$contents_url = sprintf( '%s/contents/%s.zip', $this->repository, esc_attr( $locale ) );

		$request      = wp_remote_get( $contents_url );
		$request_body = wp_remote_retrieve_body( $request );

		if ( ! is_wp_error( $request ) ) {
			$response = json_decode( $request_body );

			if ( ! empty( $response->download_url ) ) {
				return $response;
			}
		}

		return null;
	}

	/**
	 * Get remote translation locale version
	 *
	 * @param $locale
	 *
	 * @return mixed|null (string) $version
	 */
	private function get_remote_translation_version( $locale ) {
		$contents_url = sprintf( '%1$s/contents/%2$s/kalium-%2$s.po', $this->repository, esc_attr( $locale ) );

		$request      = wp_remote_get( $contents_url );
		$request_body = wp_remote_retrieve_body( $request );

		if ( ! is_wp_error( $request ) ) {
			$response = json_decode( $request_body );

			if ( ! empty( $response->content ) ) {
				$content = base64_decode( $response->content );

				// Get translation version
				if ( preg_match( '#X-Translation-Version:\s*([0-9\.]+)#', $content, $matches ) ) {
					return $matches[1];
				}
			}
		}

		return null;
	}
}
