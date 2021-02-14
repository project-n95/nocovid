<?php
/**
 * Kalium WordPress Theme
 *
 * Breadcrumb template.
 *
 * @var array  $classes
 * @var array  $container_classes
 * @var string $breadcrumb_html
 *
 * @author  Laborator
 * @version 3.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<nav <?php kalium_class_attr( $classes ); ?>>
    <div class="container">
        <div <?php kalium_class_attr( $container_classes ); ?>>
			<?php echo $breadcrumb_html; ?>
        </div>
    </div>
</nav>
