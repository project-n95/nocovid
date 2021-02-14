<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Database\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Snapshot\Database\Job\JobRestoreSnapshot;
use WPStaging\Core\WPStaging;

class Restore extends AbstractTemplateComponent
{
    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $job = WPStaging::getInstance()->get(JobRestoreSnapshot::class);

        $response = $job->execute();

        // Trigger JobRestoreSnapshot::__destruct()
        unset($job);

        wp_send_json($response);
    }
}
