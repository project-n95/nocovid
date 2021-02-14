<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Database\Job;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Component\Task\Database\RenameTablesTask;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;
use WPStaging\Pro\Snapshot\Database\Task\CreateSnapshotTask;
use WPStaging\Component\Job\AbstractQueueJob;
use WPStaging\Core\WPStaging;

class JobRestoreSnapshot extends AbstractQueueJob
{
    const JOB_NAME = 'snapshot_database_restore';

    /** @var JobRestoreSnapshotDto */
    protected $dto;

    public function __destruct()
    {
        parent::__destruct();
        if (!$this->dto->isFinished() || !$this->dto->getSnapshots()) {
            return;
        }

        /** @var SnapshotRepository $repository */
        $repository = WPStaging::getInstance()->get(SnapshotRepository::class);
        $snapshots = $this->dto->getSnapshots();
        $repository->save($snapshots);
    }

    /**
     * @inheritDoc
     */
    public function getJobName()
    {
        return self::JOB_NAME;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->injectTaskRequest(
            CreateSnapshotTask::REQUEST_NOTATION,
            [
                'target' => SnapshotService::PREFIX_TMP . '_',
                'type' => CreateSnapshotTask::TEMP,
            ]
        );

        /** @var Database $database */
        $database = WPStaging::getInstance()->get(Database::class);

        $this->injectTaskRequest(
            RenameTablesTask::REQUEST_NOTATION,
            [
                'source' => SnapshotService::PREFIX_TMP . '_',
                'target' => $database->getPrefix(),
            ]
        );

        return $this->getResponse($this->currentTask->execute());
    }

    protected function init()
    {
        /** @var SnapshotRepository $repository */
        $repository = WPStaging::getInstance()->get(SnapshotRepository::class);
        $this->dto->setSnapshots($repository->findAll());
    }

    protected function initiateTasks()
    {
        $this->addTasks([
            CreateSnapshotTask::class,
            RenameTablesTask::class,
        ]);
    }
}
