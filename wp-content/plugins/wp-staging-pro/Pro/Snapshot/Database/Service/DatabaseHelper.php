<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints

namespace WPStaging\Pro\Snapshot\Database\Service;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;

class DatabaseHelper
{
    /** @var Database */
    private $database;

    /** @var TableService */
    private $tableService;

    /** @var SnapshotRepository */
    private $repository;

    public function __construct(Database $database, TableService $tableService, SnapshotRepository $repository)
    {
        $this->database = $database;
        $this->tableService = $tableService;
        $this->repository = $repository;
    }

    /**
     * @return TableService
     */
    public function getTableService()
    {
        return $this->tableService;
    }

    /**
     * @return SnapshotRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }
}
