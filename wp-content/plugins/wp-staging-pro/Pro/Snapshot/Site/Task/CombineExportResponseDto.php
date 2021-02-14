<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Task\TaskResponseDto;

class CombineExportResponseDto extends TaskResponseDto
{
    /** @var int|null */
    private $snapshotId;

    /**
     * @return int|null
     */
    public function getSnapshotId()
    {
        return $this->snapshotId;
    }

    /**
     * @param int|null $snapshotId
     */
    public function setSnapshotId($snapshotId)
    {
        $this->snapshotId = $snapshotId;
    }
}