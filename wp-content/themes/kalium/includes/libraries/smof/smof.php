<?php
/**
 * Kalium WordPress Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Definitions.
 */
$theme_obj     = wp_get_theme( 'kalium' );
$theme_version = $theme_obj->get( 'Version' );
$theme_name    = $theme_obj->get( 'Name' );
$smof_output   = '';

if ( ! defined( 'ADMIN_PATH' ) ) {
	define( 'ADMIN_PATH', get_template_directory() . '/includes/libraries/smof/' );
}

if ( ! defined( 'ADMIN_DIR' ) ) {
	define( 'ADMIN_DIR', get_template_directory_uri() . '/includes/libraries/smof/' );
}

define( 'THEMENAME', $theme_name );
define( 'BACKUPS', 'backups' );

/**
 * Required action filters.
 */
add_action( 'admin_init', 'optionsframework_admin_init' );
add_action( 'admin_menu', 'optionsframework_add_admin' );

/**
 * Required Files.
 */
require_once( ADMIN_PATH . 'functions/functions.load.php' );
require_once( ADMIN_PATH . 'classes/class.options_machine.php' );

/**
 * AJAX Saving Options.
 */
add_action( 'wp_ajax_of_ajax_post_action', 'of_ajax_callback' );
