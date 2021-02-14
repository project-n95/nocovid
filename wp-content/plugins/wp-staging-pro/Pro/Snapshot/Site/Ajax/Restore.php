<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Site\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Snapshot\Site\Job\JobSiteRestore;
use WPStaging\Core\WPStaging;

class Restore extends AbstractTemplateComponent
{
    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        // We explicitly do not check capabilities here, since the DB is going to be replaced.
        // For authentication, we rely on the AccessToken, only granted to authenticated users.

        /** @var JobSiteRestore $job */
        $job = WPStaging::getInstance()->get(JobSiteRestore::class);
        $response = $job->execute();

        // Trigger __destruct()
        unset($job);

        wp_send_json($response);
    }
}
