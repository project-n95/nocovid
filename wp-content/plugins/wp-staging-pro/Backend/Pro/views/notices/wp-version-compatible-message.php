<div class="wpstg-error">
    <p>
        <?php
        /* translators: %s: Currently installed WordPress version */
        echo sprintf(wp_kses_post(
            __(<<<'HTML'
<p>Your version of WP STAGING Pro has not been tested with WordPress %1$s.</p>
<p>You can continue to use the plugin normally, but we highly recommend you wait for the quality assurance team of WP STAGING to finish performing the compatibility audit that we perform on all new WordPress releases, to make sure you have a smooth, reliable, and professional experience with our plugin, always.</p>
<p>You can expect an update to WP STAGING Pro as soon as we finish the compatibility audit with WordPress %1$s, which usually happens in 1 or 2 days after a new WordPress version is released.</p>
HTML
                , 'wp-staging')
        ), get_bloginfo('version'));
        ?>
    </p>
</div>
