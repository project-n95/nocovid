<?php
/**
 * Kalium WordPress Theme
 *
 * Help page.
 *
 * @var array   $help_links
 * @var int     $support_remaining
 * @var boolean $support_nearly_expiring
 * @var boolean $is_theme_registered
 * @var string  $support_left
 * @var string  $support_status
 * @var string  $support_status_class
 * @var string  $support_badge_class
 * @var string  $validate_license_link
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="row">
    <div class="col col-7 col-lg-12">
        <div class="about-kalium__heading no-top-margin">
            <h2>Help</h2>
            <p>Select the help section that you are interested to learn more. Each section contains articles that might
                answer your question without opening a support ticket.</p>
        </div>

        <ul class="about-kalium__help-links row">

			<?php if ( ! empty( $help_links ) ) : foreach ( $help_links as $help_link ) : ?>
                <li class="col col-3 col-sm-6">
                    <a href="<?php echo esc_url( $help_link['link'] ); ?>" target="_blank" rel="noreferrer noopener" class="about-kalium__help-links-entry">
                        <img src="<?php echo esc_url( kalium()->assets_url( "admin/images/help/{$help_link['icon']}.svg" ) ); ?>" width="45" height="45" alt="<?php echo sanitize_title( $help_link['title'] ); ?>">
                        <span><?php echo esc_html( $help_link['title'] ); ?></span>
                    </a>
                </li>
			<?php endforeach; endif; ?>

        </ul>
    </div>
    <div class="col col-5 col-lg-12">

        <div class="about-kalium__support-info">

			<?php if ( ! $is_theme_registered ) : ?>
                <div class="about-kalium__support-info about-kalium__support-info--not-registered">
                    <h4 class="no-margin">Theme not registered</h4>
                    <p class="about-kalium__support-info-status">To view your support status you must register the
                        theme.</p>
                    <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'theme-registration' ) ); ?>" class="button button-primary">Register
                        theme</a>
                </div>
			<?php endif; ?>

            <h3>Support</h3>

            <p>According to Envato’s terms, Kalium comes with 6 months of support for every license you purchase, and
                free lifetime theme updates. This support can be
                <a href="https://documentation.laborator.co/kb/general/extending-and-renewing-envato-item-support/" target="_blank" rel="noreferrer noopener">extended
                    through subscriptions</a> via ThemeForest.</p>

            <p>Support is limited to questions regarding the Kalium’s features, or problems with the theme. To open a
                support ticket, please navigate to our
                <a href="https://laborator.ticksy.com" target="_blank" rel="noreferrer noopener">Support Center</a>
                homepage and click the ‘Submit a Ticket’
                button.</p>

            <h4>Item support includes</h4>
            <ul>
                <li>
                    <i class="dashicons dashicons-yes"></i>
                    Responding to questions or problems regarding the theme’s features
                </li>
                <li>
                    <i class="dashicons dashicons-yes"></i>
                    Fixing bugs and reported issues
                </li>
                <li>
                    <i class="dashicons dashicons-yes"></i>
                    Providing updates to ensure compatibility with new WordPress versions
                </li>
            </ul>

            <h4>However, item support does not include:</h4>
            <ul>
                <li>
                    <i class="dashicons dashicons-no-alt"></i>
                    Theme customization
                </li>
                <li>
                    <i class="dashicons dashicons-no-alt"></i>
                    Requests that require or involve Custom Coding
                </li>
                <li>
                    <i class="dashicons dashicons-no-alt"></i>
                    Support for 3rd party Plugins
                </li>
                <li>
                    <i class="dashicons dashicons-no-alt"></i>
                    Support for Outdated Themes
                </li>
            </ul>
        </div>

		<?php if ( $is_theme_registered ) : ?>
            <div class="about-kalium__support-info about-kalium__support-info--<?php echo esc_attr( $support_status_class ); ?>">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="no-margin">Support status</h4>
                        <p class="about-kalium__support-info-status no-bottom-margin"><?php echo wp_kses_post( $support_left ); ?></p>
                    </div>
                    <div class="col col-auto">
                        <div class="about-kalium__support-info-badge about-kalium__support-info-badge--<?php echo esc_attr( $support_badge_class ); ?>"><?php echo esc_html( $support_status ); ?></div>
                    </div>
                </div>
				<?php if ( $support_nearly_expiring ) : ?>
                    <div class="about-kalium__support-info-description">
                        Your support package will expire soon, <a href="https://1.envato.market/KYm9a" target="_blank">renew
                            support</a> with 30% discount before it expires.
                    </div>
				<?php endif; ?>

				<?php if ( ! empty( $validate_license_link ) && ( $support_nearly_expiring || 0 === $support_remaining ) ) : ?>
                    <div class="about-kalium__support-info-description">
                        If you already renewed your support period, click
                        <a href="<?php echo esc_url( $validate_license_link ); ?>">here</a> to validate and reload
                        license status.
                    </div>
				<?php endif; ?>
            </div>
		<?php endif; ?>

    </div>
</div>