<div class="notice notice-error wpstg-error">
    <p>
        <?php
        echo sprintf( __(
                        'WP STAGING Pro license key has been expired. You need a valid license key to use the push feature and to get further updates. Updates are important to make sure that your version of WP STAGING is compatible with your version of WordPress and to prevent any data loss while using WP STAGING Pro.' .
                        '<br><br><a href="%s" target="_blank"><strong>Renew Your License Key Now</strong></a>', 'wp-staging'), 
                'https://wp-staging.com/checkout/?nocache=true&edd_license_key='.$licensekey.'&download_id=11'
                );
        ?>
    </p>
</div>