<?php

namespace WPStaging\Backend\Pro\Notices;

use WPStaging\Backend\Notices\BooleanNotice;

/**
 * Class DisabledCacheNotice
 *
 * This class is used to show notice if mails sending is disabled on staging site
 *
 * @package WPStaging\Backend\Pro\Notices;
 */
class DisabledMailNotice extends BooleanNotice
{
    /**
     * The option name to store the visibility of disabled mail notice
     */
    const OPTION_NAME = 'wpstg_disabled_mail_notice';

    public function getOptionName()
    {
        return self::OPTION_NAME;
    }
}