<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; type-hints & return types

namespace WPStaging\Pro\Snapshot\Database\Command;

use WPStaging\Pro\Snapshot\Database\Command\Dto\SnapshotDto;
use WPStaging\Pro\Snapshot\Database\Command\Exception\SnapshotCommandException;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Framework\Command\CommandInterface;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;

abstract class AbstractSnapshotCommand implements CommandInterface
{

    /** @var SnapshotService */
    protected $service;

    /** @var Database */
    protected $database;

    /** @var SnapshotDto */
    protected $dto;

    /** @var Snapshot[]|OptionCollection */
    protected $snapshots;

    abstract protected function saveSnapshots();

    public function __construct(SnapshotService $service)
    {
        $this->service = $service;
        $this->database = $service->getDatabaseHelper()->getDatabase();
        $this->snapshots = $service->getDatabaseHelper()->getRepository()->findAll()?:
            new OptionCollection(Snapshot::class)
        ;
    }

    /**
     * @param SnapshotDto $dto
     */
    public function setDto(SnapshotDto $dto = null)
    {
        if (!$dto) {
            return;
        }

        $this->dto = $dto;

        if (!$this->dto->getSourcePrefix()) {
            $this->dto->setSourcePrefix($this->database->getPrefix());
        }
    }

    /**
     * @throws SnapshotCommandException
     */
    protected function validateSnapshot()
    {
        if ($this->database->getPrefix() === $this->dto->getTargetPrefix()) {
            throw new SnapshotCommandException('You are trying to process production tables!');
        }

        if ($this->dto->getSourcePrefix() === $this->dto->getTargetPrefix()) {
            throw new SnapshotCommandException('You are trying to process same tables!');
        }
    }
}
