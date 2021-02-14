<?php
/**
 * Kalium WordPress Theme
 *
 * Maintenance page.
 *
 * @var string $page_description
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

/**
 * Theme header.
 */
get_header();

?>
    <div class="container">
        <div class="page-container">
            <div class="coming-soon-container">
                <div class="message-container wow fadeIn">
                    <i class="icon icon-ecommerce-megaphone"></i>
					<?php echo kalium_format_content( $page_description ); ?>
                </div>
            </div>
        </div>
    </div>
<?php

/**
 * Theme footer.
 */
get_footer();
