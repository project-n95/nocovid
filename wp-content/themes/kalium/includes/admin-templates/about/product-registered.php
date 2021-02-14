<?php
/**
 * Kalium WordPress Theme
 *
 * Product registration page.
 *
 * @var $licensed_domain
 * @var $licensee
 * @var $license_key
 * @var $registration_date
 * @var $support_remaining
 * @var $support_left
 * @var $support_status
 * @var $support_status_class
 * @var $support_badge_class
 * @var $support_nearly_expiring
 * @var $validate_license_link
 * @var $theme_backups
 * @var $remove_registration_link
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="about-kalium__heading">
    <h2>Theme is registered</h2>
    <p>You have successfully registered the theme, all theme updates and premium bundled plugins can be installed.</p>
</div>

<div class="about-kalium__register-theme-information">
    <!-- Theme status -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">Theme status</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
            <span class="about-kalium__support-info-badge">Registered</span>
        </div>
        <div class="about-kalium__register-theme-information-entry-column type-remove-registration">
			<span class="remove-registration-tooltip tooltipster" title="By removing registration you won’t be able to update the theme and it’s premium bundled plugins, you can register the theme again on this current website.">
				<i class="dashicons dashicons-info"></i>
			</span>
            <a href="<?php echo esc_url( $remove_registration_link ); ?>" class="button remove-registration-button">Remove
                Registration</a>
        </div>
    </div>

    <!-- Licensed domain -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">License domain</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
			<?php echo esc_html( $licensed_domain ); ?>
        </div>
    </div>

    <!-- Lincesee -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">Licensee</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
			<?php echo esc_html( $licensee ); ?>
        </div>
    </div>

    <!-- License key -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">License key</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
			<?php echo esc_html( $license_key ); ?>
        </div>
    </div>

    <!-- Registration date -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">Registration date</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
			<?php echo esc_html( $registration_date ); ?>
        </div>
    </div>

    <!-- Registration date -->
    <div class="about-kalium__register-theme-information-entry">
        <div class="about-kalium__register-theme-information-entry-column type-name">Support status</div>
        <div class="about-kalium__register-theme-information-entry-column type-value">
            <div class="about-kalium__support-info-badge about-kalium__support-info-badge--<?php echo esc_attr( $support_badge_class ); ?>"><?php echo esc_html( $support_status ); ?></div>
            &nbsp;
			<?php echo wp_kses_post( $support_left ); ?>

			<?php if ( $support_nearly_expiring ) : ?>
                <div class="about-kalium__support-info-description">
                    Your support package will expire soon, <a href="https://1.envato.market/KYm9a" target="_blank">renew
                        support</a> with 30% discount before it expires.
                </div>
			<?php endif; ?>

			<?php if ( ! empty( $validate_license_link ) && ( $support_nearly_expiring || 0 === $support_remaining ) ) : ?>
                <div class="about-kalium__support-info-description<?php echo esc_attr( kalium_conditional( $support_nearly_expiring, ' about-kalium__support-info-description--no-border' ) ); ?>">
                    If you already renewed your support period, click
                    <a href="<?php echo esc_url( $validate_license_link ); ?>">here</a> to validate and reload
                    license status.
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>

<div class="about-kalium__heading">
    <h2>Reset license</h2>
    <p>If you have registered the theme in a test domain or you want to use the theme in another site, you need to
        reset the registration then <a href="https://laborator.ticksy.com/" target="_blank" rel="noreferrer noopener">submit
            a ticket</a> or if you’re out of support subscription
        <a href="mailto:info@laborator.co" target="_blank" rel="noreferrer noopener">write us an e-mail</a> and include
        your purchase code.</p>
</div>

<div class="row align-items-center">
    <div class="col col-9">
        <div class="about-kalium__heading">
            <h2>Theme backups</h2>
            <p>List of previous theme backups with download link. You can toggle theme backups at any time.</p>
        </div>
    </div>
    <div class="col text-align-right">
        <div class="about-kalium__register-theme-backups-toggle">
            <a href="<?php echo add_query_arg( [ 'theme_backups' => 1 ] ); ?>" class="button first <?php echo true === kalium()->theme_license->get_backups_status() ? ' button-primary' : ''; ?>">Enabled</a>
            <a href="<?php echo add_query_arg( [ 'theme_backups' => 0 ] ); ?>" class="button last <?php echo false === kalium()->theme_license->get_backups_status() ? ' button-primary' : ''; ?>">Disabled</a>
        </div>
    </div>
</div>

<ul class="about-kalium__changelog-previous">
	<?php
	if ( ! empty( $theme_backups ) ) :
		foreach ( $theme_backups as $theme_backup ) :
			$relative_backup_file = str_replace( ABSPATH, '', $theme_backup );

			?>
            <li class="about-kalium__changelog-previous-entry">
                <div class="about-kalium__changelog-previous-entry-title"><?php echo esc_html( $theme_backup['base_name'] ); ?></div>
                <div class="about-kalium__changelog-previous-entry-date"><?php echo esc_html( date_i18n( 'F d, Y', $theme_backup['time'] ) ); ?></div>
                <div class="about-kalium__changelog-previous-entry-size"><?php echo esc_html( $theme_backup['size'] ); ?></div>
                <div class="about-kalium__changelog-previous-entry-link">
                    <a href="<?php echo esc_url( $theme_backup['url'] ); ?>" target="_blank" rel="noreferrer noopener">Download</a>
                </div>
            </li>
		<?php
		endforeach;
	else :

		?>
        <li class="about-kalium__changelog-previous-entry justify-content-center">
            There are no saved backups of the theme.
        </li>
	<?php

	endif; ?>
</ul>
