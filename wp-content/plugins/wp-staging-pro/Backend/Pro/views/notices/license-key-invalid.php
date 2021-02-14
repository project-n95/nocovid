<div class="notice notice-error wpstg-error">
    <p>
        <?php
        echo sprintf( __(
                        '<strong>WP STAGING Pro:</strong> Your license key of WP STAGING Pro is invalid or deactivated. You need a valid license key to use the push feature and to get further updates. Updates are important to make sure that your version of WP STAGING is compatible with your version of WordPress and to prevent any data loss while using WP Staging Pro. Get the license key from <a href="%1$s" target="_blank">your account</a>.' .
                        '<br><br><a href="%2$s" target="_self"><strong>Enter License Key Now</strong></a>', 'wp-staging'), 
                'https://wp-staging.com/?utm_source=admin_notice&utm_medium=plugin&utm_campaign=license_invalid',
                admin_url() . 'admin.php?page=wpstg-license',
                'your account' 
        );
        ?>
    </p>
</div>