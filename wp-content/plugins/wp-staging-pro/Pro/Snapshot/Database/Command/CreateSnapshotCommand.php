<?php

namespace WPStaging\Pro\Snapshot\Database\Command;

use DateTime;
use WPStaging\Pro\Snapshot\Database\Command\Exception\SnapshotCommandException;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;

class CreateSnapshotCommand extends AbstractSnapshotCommand
{
    /** @var null|Snapshot */
    private $snapshot;

    /** @noinspection PhpUnhandledExceptionInspection */
    public function execute()
    {
        $this->validateSnapshot();

        if ($this->dto->getStep() === null) {
            $this->executeAll();
            return;
        }

        $this->executeStep();
    }

    /**
     * @return Snapshot|null
     */
    public function getSnapshot()
    {
        return $this->snapshot;
    }

    protected function executeAll()
    {
        foreach($this->findTables() as $table) {
            $this->backupTable($table);
        }

        $this->saveSnapshots();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function executeStep()
    {
        /** @var array $tables */
        $tables = $this->findTables();

        if (!isset($tables[$this->dto->getStep()])) {
            throw new SnapshotCommandException('failed to get tables with prefix: ' . $this->dto->getSourcePrefix());
        }

        $this->backupTable($tables[$this->dto->getStep()]);

        // This was the last step, save the snapshot
        if (count($tables) === $this->dto->getStep() + 1) {
            $this->saveSnapshots();
        }
    }

    /**
     * @return TableDto[]|null
     */
    protected function findTables()
    {
        $tables = (new TableService)->findTableStatusStartsWith($this->dto->getSourcePrefix());
        if (!$tables) {
            return null;
        }
        return $tables->toArray();
    }

    protected function backupTable(TableDto $tableDto)
    {
        $newTableName = $this->dto->getTargetPrefix() . str_replace($this->dto->getSourcePrefix(), null, $tableDto->getName());
        $this->database->exec('OPTIMIZE TABLE '. $tableDto->getName());
        $this->database->exec('DROP TABLE IF EXISTS '. $newTableName);
        $this->database->exec('CREATE TABLE ' . $newTableName . ' LIKE ' . $tableDto->getName());
        $this->database->exec('INSERT INTO ' . $newTableName . ' SELECT * FROM ' . $tableDto->getName());
        $this->database->exec('OPTIMIZE TABLE ' . $newTableName);
    }

    protected function saveSnapshots()
    {
        if (!$this->dto->isSaveRecords()) {
            return;
        }

        /** @var Snapshot $snapshot */
        $snapshot = $this->snapshots->findById($this->dto->getTargetPrefix());

        if ($snapshot) {
            $snapshot->setUpdatedAt(new DateTime);
            $this->service->getDatabaseHelper()->getRepository()->save($this->snapshots);
            return;
        }

        $this->snapshot = new Snapshot;
        $this->snapshot->setId($this->dto->getTargetPrefix());
        $this->snapshot->setName($this->dto->getName());
        $this->snapshot->setNotes($this->dto->getNotes());
        $this->snapshot->setCreatedAt(new DateTime);

        $this->snapshots->attach($this->snapshot);
        $this->service->getDatabaseHelper()->getRepository()->save($this->snapshots);
    }
}
