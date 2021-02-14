<?php
/**
 * Kalium WordPress Theme
 *
 * Custom Header Template
 *
 * @author  Laborator
 * @link    https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="header-block">

	<?php
	/**
	 * Display header content above the logo.
	 *
	 * Hook: kalium_header_content_before.
	 */
	do_action( 'kalium_header_content_before' );
	?>

	<div class="header-block__row-container container">

		<div class="header-block__row header-block__row--main">
			<?php
			/**
			 * Display main row for header.
			 *
			 * Hook: kalium_header_content_main.
			 *
			 * @hooked kalium_header_content_left - 10
			 * @hooked kalium_header_content_logo - 20
			 * @hooked kalium_header_content_right - 30
			 */
			do_action( 'kalium_header_content_main' );
			?>
		</div>

	</div>

	<?php
	/**
	 * Display header content below the logo.
	 *
	 * Hook: kalium_header_content_after.
	 *
	 * @hooked kalium_header_content_below - 10
	 */
	do_action( 'kalium_header_content_after' );
	?>

</div>
