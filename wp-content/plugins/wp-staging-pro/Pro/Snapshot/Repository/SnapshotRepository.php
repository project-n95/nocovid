<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x type hints & return types

namespace WPStaging\Pro\Snapshot\Repository;

use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Collection\OptionCollection;

class SnapshotRepository
{
    const OPTION_NAME = 'wpstg_snapshots';

    /**
     * @return OptionCollection|null
     */
    public function findAll()
    {
        $snapshots = get_option(self::OPTION_NAME, []);
        if (!$snapshots || !is_array($snapshots)) {
            return null;
        }

        $collection = new OptionCollection(Snapshot::class);
        $collection->attachAllByArray($snapshots);

        return $collection;
    }

    /**
     * @param string $id
     * @return Snapshot
     */
    public function find($id)
    {
        $snapshots = $this->findAll();
        if (!$snapshots) {
            return null;
        }

        /** @var Snapshot|null $snapshot */
        $snapshot = $snapshots->findById($id);
        return $snapshot;
    }

    public function save(OptionCollection $snapshots)
    {
        $data = $snapshots->toArray();
        $existing = $this->findAll();
        if ($existing && $data === $existing->toArray()) {
            return true;
        }

        return update_option(self::OPTION_NAME, $data, false);
    }

    public function delete(Snapshot $snapshot)
    {
        $snapshots = $this->findAll();
        if (!$snapshots || !$snapshots->doesIncludeId($snapshot->getId())) {
            return true;
        }

        $snapshots->removeById($snapshot->getId());
        return $this->save($snapshots);
    }

    public function deleteById($id)
    {
        $snapshots = $this->findAll();
        if (!$snapshots) {
            return true;
        }

        /** @var Snapshot|null $snapshot */
        $snapshot = $snapshots->findById($id);
        if (!$snapshot) {
            return true;
        }

        $snapshots->detach($snapshot);
        return $this->save($snapshots);
    }
}
