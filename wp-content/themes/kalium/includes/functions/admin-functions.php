<?php
/**
 * Kalium WordPress Theme
 *
 * Admin theme functions.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Count theme bundled plugins that need update.
 *
 * @return int
 */
function kalium_plugin_updates_count() {
	global $tgmpa;

	// Plugin updates count
	$plugin_updates = 0;

	if ( $tgmpa && ! $tgmpa->is_tgmpa_complete() ) {
		foreach ( $tgmpa->plugins as $slug => $plugin ) {
			if ( $tgmpa->is_plugin_active( $slug ) && $tgmpa->does_plugin_have_update( $slug ) ) {
				$plugin_updates ++;
			}
		}
	}

	return $plugin_updates;
}

/**
 * Get Laborator svg logo for admin menu.
 *
 * @return string
 */
function get_laborator_admin_menu_logo() {
	return sprintf( '<span class="laborator-icon">%s</span>', kalium_get_svg_file( 'assets/admin/images/laborator-admin-menu.svg' ) );
}

/**
 * Get Open SSL Version.
 *
 * @param string $openssl_version_number
 *
 * @return bool|string
 */
function kalium_get_openssl_version_number( $openssl_version_number = null ) {
	if ( is_null( $openssl_version_number ) ) {
		$openssl_version_number = OPENSSL_VERSION_NUMBER;
	}

	$openssl_numeric_identifier = str_pad( (string) dechex( $openssl_version_number ), 8, '0', STR_PAD_LEFT );
	$openssl_version_parsed     = [];

	$preg = '/(?<major>[[:xdigit:]])(?<minor>[[:xdigit:]][[:xdigit:]])(?<fix>[[:xdigit:]][[:xdigit:]])';
	$preg .= '(?<patch>[[:xdigit:]][[:xdigit:]])(?<type>[[:xdigit:]])/';

	preg_match_all( $preg, $openssl_numeric_identifier, $openssl_version_parsed );

	$openssl_version = false;

	if ( ! empty( $openssl_version_parsed ) ) {
		$openssl_version = intval( $openssl_version_parsed['major'][0] ) . '.';
		$openssl_version .= intval( $openssl_version_parsed['minor'][0] ) . '.';
		$openssl_version .= intval( $openssl_version_parsed['fix'][0] );
		$patchlevel_dec  = hexdec( $openssl_version_parsed['patch'][0] );
	}

	return $openssl_version;
}
