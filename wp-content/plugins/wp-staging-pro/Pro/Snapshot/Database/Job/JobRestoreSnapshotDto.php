<?php

namespace WPStaging\Pro\Snapshot\Database\Job;

use WPStaging\Component\Job\QueueJobDto;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Pro\Snapshot\Entity\Snapshot;

class JobRestoreSnapshotDto extends QueueJobDto
{
    /** @var OptionCollection|Snapshot[]|null */
    private $snapshots;

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
    public function setSnapshots($snapshots)
    {
        // Hack for < PHP 7.x
        if (is_array($snapshots)) {
            $collection = new OptionCollection(Snapshot::class);
            $collection->attachAllByArray($snapshots);
            $snapshots = $collection;
        }

        $this->snapshots = $snapshots;
    }
}