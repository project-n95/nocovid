<?php
/**
 * @var $this \WPStaging\Backend\Pro\Notices\Notices
 * @see \WPStaging\Backend\Pro\Notices\Notices::getNotices
 */
?>
<div class="notice wpstg-mails-notice" style="border-left: 4px solid #ffba00;">
    <p>
        <strong style="margin-bottom: 10px;"><?php _e('Mails Disabled', 'wp-staging'); ?></strong> <br/>
        <?php _e('WP STAGING has disabled the outgoing mails which depends upon wp_mail service on this staging site by your permission during the cloning process.', 'wp-staging'); ?>
    </p>
    <p>
        <a href="javascript:void(0);" class="wpstg_hide_disabled_mail_notice" title="Close this message"
            style="font-weight:bold;">
            <?php _e('Close this message', 'wp-staging') ?>
        </a>
    </p>
</div>
<?php
/*
 * Cache-burst mechanism to ensure the browser cache will not get in the way
 * of the script working properly when there's updates.
 */
$file = trailingslashit($this->notices->getPluginPath()) . "Pro/public/js/wpstg-admin-disabled-mail-notice.js";

if (file_exists($file)) {
    $version = (string)@filemtime($file);
} else {
    $version = '{{version}}';
}
?>
<script src="<?php echo esc_url(trailingslashit($this->notices->getPluginUrl()) . "../Pro/public/js/wpstg-admin-disabled-mail-notice.js?v=$version") ?>"></script>
