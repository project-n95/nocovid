<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use \stdClass;

// Quick and dirty
trait SnapshotTrait
{
    protected function assignSnapshotId(stdClass $options)
    {
        if (!isset($options->snapshotIds) || !$options->snapshotIds) {
            $options->snapshotIds = new stdClass;
        }

        if (isset($options->snapshotIds->{$options->current})) {
            $id = $this->provideUniqueId($options, $options->snapshotIds->{$options->current});
            $options->snapshotIds->{$options->current} = $id;
            return;
        }

        /** @noinspection TypeUnsafeArraySearchInspection */
        $id = array_search($options->current, array_keys($options->existingClones));
        $options->snapshotIds->{$options->current} = $this->provideUniqueId($options, $id);
    }

    protected function provideUniqueId($options, $id)
    {
        foreach ($options->existingClones as $key => $value) {
            if ($options->current === $key) {
                continue;
            }

            if (isset($value['snapshotId']) && $id === $value['snapshotId']) {
                return $this->provideUniqueId($options, $id + 1);
            }
        }

        return $id;
    }
}