<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Database\Task;

use WPStaging\Pro\Snapshot\Database\Command\Dto\SnapshotDto;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Collection\Collection;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Database\Task\Dto\CreateSnapshotResponseDto;
use WPStaging\Pro\Snapshot\Database\Task\Dto\SnapshotCreateDto;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;
use WPStaging\Core\Utils\Logger;

class CreateSnapshotTask extends AbstractTask
{
    const REQUEST_NOTATION = 'snapshot.database.create';
    const REQUEST_DTO_CLASS = SnapshotCreateDto::class;
    const TASK_NAME = 'snapshot_database_create';
    const TASK_TITLE = 'Database Backup for %s Tables';

    const AUTOMATIC = 'automatic';
    const MANUAL = 'manual';
    const TEMP = 'temp';

    /** @var SnapshotCreateDto */
    protected $requestDto;

    /** @var Collection|TableDto[]|null */
    private $tables;

    /** @var SnapshotService */
    private $service;

    public function __construct(SnapshotService $service, Cache $cache)
    {
        parent::__construct($service->getAdapterHelper()->getLogger(), $cache);
        $this->service = $service;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->prepare();

        $dto = new SnapshotDto;
        $dto->setName($this->requestDto->getName());
        $dto->setNotes($this->requestDto->getNotes());
        $dto->setTargetPrefix($this->requestDto->getTarget());
        $dto->setSourcePrefix($this->requestDto->getSource());
        $dto->setStep($this->requestDto->getSteps()->getCurrent());

        $snapshot = $this->service->create($dto);

        $this->logger->log(Logger::TYPE_INFO, sprintf(
            'Created backup with prefix %s of table %s - %d/%d',
            $dto->getTargetPrefix(),
            $this->getCurrentTableName(),
            $this->requestDto->getSteps()->getCurrent() + 1,
            $this->requestDto->getSteps()->getTotal()
        ));

        return $this->generateResponse($snapshot);
    }

    /**
     * @param null|Snapshot $snapshot
     * @return CreateSnapshotResponseDto
     */
    public function generateResponse($snapshot = null)
    {
        /** @var CreateSnapshotResponseDto $response */
        $response = parent::generateResponse();
        $response->setSnapshotId($snapshot ? $snapshot->getId() : null);
        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getTaskName()
    {
        return self::TASK_NAME;
    }

    /**
     * @inheritDoc
     */
    public function getStatusTitle(array $args = [])
    {
        $totalTables = isset($args[0]) ? $args[0] : 0;
        if ($this->requestDto && $this->requestDto->getSteps()) {
            $totalTables = $this->requestDto->getSteps()->getTotal();
        }
        return sprintf(__(self::TASK_TITLE, 'wp-staging'), $totalTables);
    }

    /**
     * @inheritDoc
     */
    public function getRequestNotation()
    {
        return self::REQUEST_NOTATION;
    }

    /**
     * @inheritDoc
     */
    public function getRequestDtoClass()
    {
        return self::REQUEST_DTO_CLASS;
    }

    public function getCacheFiles()
    {
        return [
            $this->cache->getFilePath(),
        ];
    }

    protected function getResponseDto()
    {
        return new CreateSnapshotResponseDto;
    }

    /**
     * @inheritDoc
     */
    protected function findRequestDto()
    {
        parent::findRequestDto();
        $this->generateTargetPrefix();
        $this->setJobId(rtrim($this->requestDto->getTarget(), '_'));

        $this->requestDto->getSteps()->setTotal(count($this->findTables()));

        if ($this->requestDto->isReset()) {
            $this->requestDto->getSteps()->setCurrent(0);
        }
    }

    /**
     * @return Collection|TableDto[]|null
     */
    protected function findTables()
    {
        if ($this->tables || !$this->requestDto) {
            return $this->tables;
        }

        $this->tables = $this->service
            ->getDatabaseHelper()
            ->getTableService()
            ->findTableStatusStartsWith($this->requestDto->getSource())
        ;

        return $this->tables;
    }

    /**
     * @return string
     */
    private function getCurrentTableName()
    {
        $tables = $this->findTables();
        if (!$tables) {
            return '';
        }

        $tables = $tables->toArray();
        if (!isset($tables[$this->requestDto->getSteps()->getCurrent()])) {
            return '';
        }

        /** @var TableDto $table */
        $table = $tables[$this->requestDto->getSteps()->getCurrent()];
        return $table->getName();
    }

    /**
     * Generates Database prefix depending on Request's Type (`SnapshotCreateDto::getType()`)
     */
    private function generateTargetPrefix()
    {
        if (!$this->requestDto || $this->requestDto->getTarget()) {
            return;
        }

        switch($this->requestDto->getType()) {
            case self::MANUAL:
                $this->requestDto->setTarget($this->findTargetPrefix());
                return;
            case self::TEMP:
                $this->requestDto->setTarget(SnapshotService::PREFIX_TMP . '_');
                return;
        }

        $this->requestDto->setType(self::AUTOMATIC);
        $this->requestDto->setTarget($this->findTargetPrefix(SnapshotService::PREFIX_AUTOMATIC));
    }

    /**
     * @param string $prefix
     * @return string
     */
    private function findTargetPrefix($prefix = SnapshotService::PREFIX_MANUAL)
    {
        $used = $this->totalSnapshotsByPrefix($prefix);
        if (!$used) {
            return $prefix . 0 . '_';
        }

        $current = -1;
        do {
            $current++;
        } while (in_array($current, $used, true));

        return $prefix . $current . '_';
    }

    /**
     * @param string $prefix
     * @return array|null
     */
    private function totalSnapshotsByPrefix($prefix = SnapshotService::PREFIX_MANUAL)
    {
        $tables = $this->service
            ->getDatabaseHelper()
            ->getTableService()
            ->findTableStatusStartsWith($prefix)
        ;

        if (!$tables) {
            return null;
        }

        $found = [];
        foreach ($tables as $table) {
            $tablePrefix = substr($table->getName(), 0, strpos($table->getName(), '_'));
            $increment = (int) str_replace($prefix, null, $tablePrefix);
            $found[$increment] = '';
        }

        return array_keys($found);
    }
}
