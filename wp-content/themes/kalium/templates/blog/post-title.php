<?php
/**
 * Kalium WordPress Theme
 *
 * Post title single page.
 *
 * @var $heading_tag_open
 * @var $heading_tag_close
 *
 * @author  Laborator
 * @version 2.3.4
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
?>
<header class="entry-header">

	<?php
	/**
	 * Post title.
	 */
	the_title( $heading_tag_open, $heading_tag_close );
	?>

</header>