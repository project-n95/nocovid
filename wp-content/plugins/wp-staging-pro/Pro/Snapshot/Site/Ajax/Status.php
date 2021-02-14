<?php

namespace WPStaging\Pro\Snapshot\Site\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Snapshot\Site\Job\JobSiteExport;
use WPStaging\Pro\Snapshot\Site\Job\JobSiteRestore;
use WPStaging\Core\WPStaging;

class Status extends AbstractTemplateComponent
{
    const TYPE_RESTORE = 'restore';

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        wp_send_json($this->getJob()->getDto());
    }

    /**
     * @return JobSiteExport|JobSiteRestore
     */
    private function getJob()
    {
        if (!empty($_GET['process']) && $_GET['process'] === self::TYPE_RESTORE) {
            return WPStaging::getInstance()->get(JobSiteRestore::class);
        }
        return WPStaging::getInstance()->get(JobSiteExport::class);
    }
}
