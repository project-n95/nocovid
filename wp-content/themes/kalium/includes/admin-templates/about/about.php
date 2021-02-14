<?php
/**
 * Kalium WordPress Theme
 *
 * About Kalium.
 *
 * @var bool $welcome
 * @var string $version
 * @var bool $is_theme_registered
 *
 * @author Laborator
 * @link   https://kaliumtheme.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}

?>
<div class="about-kalium__heading">
    <h2>Thank you for choosing Kalium</h2>
    <p>Kalium continuously expands with new features, bug fixes and other adjustments to provide smoother experience for
        everyone.</p>
</div>

<?php if ( $welcome ) : $list_count = 1; ?>
    <div class="about-kalium__welcome">

        <div class="about-kalium__welcome-heading">
            <h3>Thanks for choosing Kalium!</h3>
            <p>Here are the first steps to setup the theme:</p>
        </div>

        <ul class="about-kalium__welcome-steps">
			<?php if ( ! $is_theme_registered ) : ?>
                <li>
                    <span class="step-number"><?php echo esc_html( $list_count ++ ); ?></span>
                    <p>Register the theme on
                        <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'theme-registration' ) ); ?>" target="_blank">Registration</a>
                        tab</p>
                </li>
			<?php endif; ?>
            <li>
                <span class="step-number"><?php echo esc_html( $list_count ++ ); ?></span>
                <p>Install and activate required plugins on
                    <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'plugins' ) ); ?>" target="_blank">Plugins</a> tab</p>
            </li>
            <li>
                <span class="step-number"><?php echo esc_html( $list_count ++ ); ?></span>
                <p>Install a pre-made demo on <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'demos' ) ); ?>">Demos</a> tab (optional)</p>
            </li>
            <li>
                <span class="step-number"><?php echo esc_html( $list_count ++ ); ?></span>
                <p>Configure
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=laborator_options' ) ); ?>" target="_blank">Theme
                        Options</a> based in your needs</p>
            </li>
            <li>
                <span class="step-number"><?php echo esc_html( $list_count ++ ); ?></span>
                <p>Head over to <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'help' ) ); ?>" target="_blank" rel="noopener">Help</a> tab and learn more about your theme</p>
            </li>
        </ul>

        <div class="about-kalium__welcome-getting-started-figure">
            <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/getting-started.svg' ) ); ?>" width="300" height="289" alt="getting-started"/>
        </div>
    </div>
<?php endif; ?>

<ul class="about-kalium__features-list">

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-welcome.svg' ) ); ?>" width="48" height="48" alt="welcome-to-kalium"/>
            </div>
            <h3 class="about-kalium__features-list-item-title">
                Welcome to Kalium
            </h3>
            <p class="about-kalium__features-list-item-description">Thank you for choosing our theme.
                With stacks of layout designs, rich theme options and drag and drop content builder elements to create
                your site in minutes, it is well organized and visually stunning.</p>
        </div>
    </li>

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-new.svg' ) ); ?>" width="48" height="48" alt="whats-new"/>
            </div>

            <h3 class="about-kalium__features-list-item-title">
                What's new in <?php echo esc_html( $version ); ?>
            </h3>

            <p class="about-kalium__features-list-item-description">Kalium continuously expands with new features, bug
                fixes and other adjustments to provide smoother experience for everyone.</p>

            <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'whats-new' ) ); ?>#changelog" class="about-kalium__features-list-item-link button button-primary">Read changelog</a>
        </div>
    </li>

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-documentation.svg' ) ); ?>" width="48" height="48" alt="documentation"/>
            </div>

            <h3 class="about-kalium__features-list-item-title">
                Documentation
            </h3>

            <p class="about-kalium__features-list-item-description">Laborator Knowledge Base provides detailed
                information about how to set up your theme.</p>

            <a href="https://documentation.laborator.co/" target="_blank" rel="noreferrer noopener" class="about-kalium__features-list-item-link button button-primary">Read documentation</a>
        </div>
    </li>

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-subscribe.svg' ) ); ?>" width="48" height="48" alt="subscribe"/>
            </div>

            <h3 class="about-kalium__features-list-item-title">
                Subscribe
            </h3>

            <p class="about-kalium__features-list-item-description">Subscribe to our newsletter to get notified for
                theme sales and promotional offers.</p>

            <form action="https://laborator.us19.list-manage.com/subscribe/post?u=d14e54b0150aa63b1d1363b66&amp;id=6863811c9e" method="post" class="about-kalium__features-list-item-subscribe" target="_blank">
                <div style="position: absolute; left: -5000px;" aria-hidden="true">
                    <input type="text" name="b_d14e54b0150aa63b1d1363b66_6863811c9e" tabindex="-1" value=""></div>
                <input type="email" class="regular-text" placeholder="Enter your e-mail" name="EMAIL" class="required email"/>
                <button type="submit" class="button button-primary" name="subscribe">Subscribe</button>
            </form>
        </div>
    </li>

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-faq.svg' ) ); ?>" width="48" height="48" alt="faq"/>
            </div>

            <h3 class="about-kalium__features-list-item-title">
                F.A.Q
            </h3>

            <p class="about-kalium__features-list-item-description">Frequently asked questions and most common topics
                asked by our theme customers.</p>

            <a href="<?php echo esc_url( Kalium_About::get_tab_link( 'faq' ) ); ?>" class="about-kalium__features-list-item-link button button-primary">Read
                FAQ</a>
        </div>
    </li>

    <li>
        <div class="about-kalium__features-list-item">
            <div class="about-kalium__features-list-item-icon">
                <img src="<?php echo esc_url( kalium()->assets_url( 'admin/images/icon-help.svg' ) ); ?>" width="48" height="48" alt="need-help"/>
            </div>
            <h3 class="about-kalium__features-list-item-title">
                Need help?
            </h3>
            <p class="about-kalium__features-list-item-description">If you need help with the theme you can always
                create a ticket in our support system.</p>

            <a href="https://laborator.ticksy.com/" target="_blank" rel="noreferrer noopener" class="about-kalium__features-list-item-link button button-primary">Submit
                a ticket</a>
        </div>
    </li>

</ul>
