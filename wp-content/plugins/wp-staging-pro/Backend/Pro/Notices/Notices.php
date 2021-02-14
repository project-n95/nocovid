<?php

namespace WPStaging\Backend\Pro\Notices;

use WPStaging\Backend\Notices\Notices as FreeNotices;
use WPStaging\Core\WPStaging;

/*
 *  Admin Notices | Warnings | Messages
 */

// No Direct Access
if (!defined("WPINC")) {
    die;
}

/**
 * Class Notices
 * @package WPStaging\Backend\Pro\Notices
 */
class Notices
{
    /**
     * @var FreeNotices
     */
    private $notices;

    /**
     * @var object
     */
    private $license;


    /**
     * Notices constructor.
     * @param $notices FreeNotices Notices class
     */
    public function __construct($notices)
    {
        $this->notices = $notices;
        $this->license = get_option('wpstg_license_status');
    }

    public function getNotices()
    {
        $this->getGlobalAdminNotices();
        $this->getPluginAdminNotices();
        $this->checkTestedWithCurrentWordPressVersion();
    }


    /**
     * Notices shown on all admin pages
     */
    public function getGlobalAdminNotices()
    {
        // Customer never used any valid license key at all. A valid (expired) license key is needed to make use of all wp staging pro features
        // So show this admin notice on all pages to make sure customer is aware that license key must be entered
        if (((isset($this->license->error) && $this->license->error !== 'expired') || $this->license === false) && !wpstg_is_stagingsite()) {
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-invalid.php';
        }

        // show emails disabled notice globally if mails are disabled. (Show only on staging site)
        if ((new DisabledMailNotice())->isEnabled()) {
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/disabled-mails.php';
        }
    }


    /**
     * Notices shown on WP Staging admin pages only
     */
    public function getPluginAdminNotices()
    {
        if (!current_user_can("update_plugins") || !$this->notices->isAdminPage()) {
            return;
        }

        // License key has been expired
        if ((isset($this->license->error) && $this->license->error === 'expired') || (isset($this->license->license) && $this->license->license === 'expired')) {
            $licensekey = get_option('wpstg_license_key', '');
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/license-key-expired.php';
        }

    }

    private function checkTestedWithCurrentWordPressVersion()
    {
        // Early bail: Only display this message on certain admin pages to certain users.
        if ( ! current_user_can("update_plugins") || ! $this->notices->isAdminPage()) {
            return;
        }

        // Version Control for Pro
        if (version_compare( WPStaging::getInstance()->get('WPSTG_COMPATIBLE'), get_bloginfo("version"), "<")) {
            require_once WPSTG_PLUGIN_DIR . 'Backend/Pro/views/notices/wp-version-compatible-message.php';
        }
    }


}
