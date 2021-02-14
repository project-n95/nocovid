<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Pro\Snapshot\Entity\Snapshot;

class RestoreCleanUpDto extends RestoreFilesDto
{
    /** @var Snapshot[]|OptionCollection|null */
    protected $snapshots;

    /** @var array|null */
    protected $clones;

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