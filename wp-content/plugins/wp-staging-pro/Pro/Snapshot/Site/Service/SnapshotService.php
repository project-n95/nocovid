<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Site\Service;

use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Framework\Filesystem\Filesystem;

class SnapshotService
{
    /** @var Filesystem */
    private $filesystem;

    /** @var SnapshotRepository */
    private $repository;

    public function __construct(Filesystem $filesystem, SnapshotRepository $repository)
    {
        $this->filesystem = $filesystem;
        $this->repository = $repository;
    }

    public function delete(Snapshot $snapshot)
    {
        $this->filesystem->delete($snapshot->getFilePath());
        return $this->repository->delete($snapshot);
    }
}
