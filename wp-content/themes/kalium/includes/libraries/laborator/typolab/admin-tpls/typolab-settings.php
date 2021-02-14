<?php
/**
 * Kalium WordPress Theme
 *
 * TypoLab Main Screen.
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<div id="typolab-wrapper" class="wrap">
	<?php
	// Page structure
	require_once 'title.php';
	require_once 'tabs.php';
	require_once 'font-settings.php';
	require_once 'footer.php';
	?>
</div>