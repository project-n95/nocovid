<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Database\Job;

use WPStaging\Component\Job\JobInterface;
use WPStaging\Pro\Snapshot\Database\Task\Dto\SnapshotCreateDto;
use WPStaging\Pro\Snapshot\Database\Task\CreateSnapshotTask;

class JobCreateSnapshot implements JobInterface
{
    const JOB_NAME = 'snapshot_database_create';

    /** @var CreateSnapshotTask */
    private $task;

    public function __construct(CreateSnapshotTask $task)
    {
        $this->task = $task;
        $task->setJobName(self::JOB_NAME);
    }

    // TODO Remove after Processing.php is removed. This is backward compatibility only
    public function setRequest(array $data = [])
    {
        $dto = (new SnapshotCreateDto)->hydrate($data);
        $this->task->setRequestDto($dto);
    }

    public function execute()
    {
        $response = $this->task->execute();
        $response->setJob('CreateSnapshot');
        return $response;
    }
}
