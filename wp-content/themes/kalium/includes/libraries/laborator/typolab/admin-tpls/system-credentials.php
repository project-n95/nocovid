<?php
/**
 * Kalium WordPress Theme
 *
 * Typolab Tabs.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

ob_start();
$creds            = request_filesystem_credentials( admin_url( 'admin-ajax.php' ) );
$credentials_form = ob_get_clean();

if ( ! $creds ) {

	// Remove form
	$credentials_form = str_replace( [ '<form', '</form' ], [ '<div', '</div' ], $credentials_form );

	?>
    <script>
		var typolab_request_system_credentials_form = <?php echo wp_json_encode( $credentials_form ); ?>;
    </script>
	<?php
}