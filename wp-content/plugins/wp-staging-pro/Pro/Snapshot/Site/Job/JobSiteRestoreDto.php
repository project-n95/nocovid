<?php

namespace WPStaging\Pro\Snapshot\Site\Job;

use WPStaging\Component\Job\QueueJobDto;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Site\Service\ExportFileHeadersDto;

class JobSiteRestoreDto extends QueueJobDto
{
    /** @var ExportFileHeadersDto */
    private $fileHeaders;

    /** @var Snapshot[]|OptionCollection|null */
    private $snapshots;

    /** @var array|null */
    private $clones;

    /**
     * @return ExportFileHeadersDto
     */
    public function getFileHeaders()
    {
        return $this->fileHeaders;
    }

    /**
     * @param ExportFileHeadersDto $fileHeaders
     */
    public function setFileHeaders(ExportFileHeadersDto $fileHeaders)
    {
        $this->fileHeaders = $fileHeaders;
    }

    /**
     * @return OptionCollection|Snapshot[]|null
     */
    public function getSnapshots()
    {
        return $this->snapshots;
    }

    /**
     * @param OptionCollection|Snapshot[]|null $snapshots
     */
    public function setSnapshots($snapshots = null)
    {
        $this->snapshots = $snapshots;
    }

    /**
     * @return array|null
     */
    public function getClones()
    {
        return $this->clones;
    }

    /**
     * @param array|null $clones
     */
    public function setClones(array $clones = null)
    {
        $this->clones = $clones;
    }
}