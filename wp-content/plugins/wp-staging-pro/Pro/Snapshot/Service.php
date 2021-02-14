<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot;

use WPStaging\Pro\Snapshot\Database\Command\Exception\SnapshotCommandException;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService as DatabaseService;
use WPStaging\Pro\Snapshot\Site\Service\SnapshotService as SiteService;
use WPStaging\Pro\Snapshot\Entity\Snapshot;

class Service
{
    /** @var SnapshotRepository */
    private $repository;

    /** @var DatabaseService */
    private $serviceDatabase;

    /** @var SiteService */
    private $serviceSite;

    public function __construct(
        SnapshotRepository $repository,
        DatabaseService $serviceDatabase,
        SiteService $serviceSite
    ) {
        $this->repository = $repository;
        $this->serviceDatabase = $serviceDatabase;
        $this->serviceSite = $serviceSite;
    }

    /**
     * @param string $id
     */
    public function deleteById($id)
    {
        $snapshot = $this->repository->find($id);
        if (!$snapshot) {
            return;
        }

        $this->delete($snapshot);
    }

    /**
     * @param Snapshot $snapshot
     * @param bool $skipDatabaseValidation
     */
    public function delete(Snapshot $snapshot, $skipDatabaseValidation = false)
    {
        if ($snapshot->getType() === Snapshot::TYPE_DATABASE) {
            $this->serviceDatabase->delete($snapshot->getId(), $skipDatabaseValidation);
            return;
        }

        $this->serviceSite->delete($snapshot);
    }

    /**
     * @param string $prefix
     */
    public function deleteTablesByPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        $this->serviceDatabase->delete($prefix, true);
    }
}