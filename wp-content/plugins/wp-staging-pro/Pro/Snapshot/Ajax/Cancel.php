<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Ajax;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Snapshot\Database\Job\JobCreateSnapshot;
use WPStaging\Pro\Snapshot\Site\Job\JobSiteExport;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Core\WPStaging;

// TODO RPoC
class Cancel extends AbstractTemplateComponent
{
    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $directory = WPStaging::getInstance()->get(Directory::class);
        (new Filesystem)->delete($directory->getCacheDirectory() . $this->findJobName());
        wp_send_json(true);
    }

    // Hack & Slash || Rip & Tear until it is done!
    private function findJobName()
    {
        if (!empty($_POST['type']) && $_POST['type'] === 'database') {
            return JobCreateSnapshot::JOB_NAME;
        }

        return JobSiteExport::JOB_NAME;
    }
}
