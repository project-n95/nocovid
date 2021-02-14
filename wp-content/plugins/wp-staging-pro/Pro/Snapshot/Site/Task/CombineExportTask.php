<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Site\Task;

use DateTime;
use Exception;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Pro\Snapshot\Site\Service\Compressor;

class CombineExportTask extends AbstractTask
{

    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.export.join';
    const REQUEST_DTO_CLASS = CombineExportDto::class;
    const TASK_NAME = 'snapshot_site_export_join';
    const TASK_TITLE = 'Finalizing Snapshot Export';
    const DEFAULT_SNAPSHOT_NAME = 'Files and Database';

    /** @var CombineExportDto */
    protected $requestDto;

    /** @var Compressor */
    private $exporter;

    /** @var SnapshotRepository */
    private $repository;

    // TODO reduce args
    public function __construct(Compressor $exporter, SnapshotRepository $repository, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
        $this->repository = $repository;
    }

    public function execute()
    {
        $this->prepare();

        $dto = $this->exporter->getDto();
        $dto->setOffset($this->requestDto->getSteps()->getCurrent());

        $dtoData = $dto->getFileHeaders();
        $dtoData->setDirectories($this->requestDto->getDirectories());
        $dtoData->setTotalDirectories($this->requestDto->getTotalDirectories());
        $dtoData->setTotalFiles($this->requestDto->getTotalFiles());
        $dtoData->setDatabaseIncluded($this->requestDto->isDatabaseIncluded());
        $dtoData->setDatabaseFile($this->requestDto->getDatabaseFile());
        $dtoData->setSiteUrl((new Urls)->getHomeUrlWithoutScheme());
        $dtoData->setAbspath(ABSPATH);

        $exportFilePath = null;
        try {
            $exportFilePath = $this->exporter->combine();
        } catch (Exception $e) {
            $this->logger->critical('Failed to generate snapshot file: ' . $e->getMessage());
        }

        if ($exportFilePath) {
            $this->requestDto->getSteps()->finish();
            return $this->generateResponse($this->saveSnapshot($exportFilePath));
        }

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($dto->getOffset());
        $steps->setTotal($dto->getFileSize());

        $this->logger->info(sprintf('Written %d bytes to compressed export', $dto->getWrittenBytes()));
        return $this->generateResponse($exportFilePath);
    }

    /**
     * @param null|Snapshot $snapshot
     * @return CombineExportResponseDto
     */
    public function generateResponse(Snapshot $snapshot = null)
    {
        /** @var CombineExportResponseDto $response */
        $response = parent::generateResponse();
        $response->setSnapshotId($snapshot ? $snapshot->getId() : null);
        return $response;
    }

    public function getCaches()
    {
        $caches = parent::getCaches();
        $caches[] = $this->exporter->getCacheIndex();
        $caches[] = $this->exporter->getCacheCompressed();
        return $caches;
    }

    public function getTaskName()
    {
        return self::TASK_NAME;
    }

    public function getRequestNotation()
    {
        return self::REQUEST_NOTATION;
    }

    public function getRequestDtoClass()
    {
        return self::REQUEST_DTO_CLASS;
    }

    /**
     * @inheritDoc
     */
    public function getStatusTitle(array $args = [])
    {
        return __(self::TASK_TITLE, 'wp-staging');
    }

    protected function getResponseDto()
    {
        return new CombineExportResponseDto;
    }

    /**
     * @param string $exportFilePath
     * @return Snapshot
     */
    protected function saveSnapshot($exportFilePath)
    {
        /** @var Snapshot[]|OptionCollection|null $snapshots */
        $snapshots = $this->repository->findAll();
        if (!$snapshots) {
            $snapshots = new OptionCollection(Snapshot::class);
        }

        $snapshot = new Snapshot;
        $snapshot->setId($this->requestDto->getId());
        $snapshot->setName($this->requestDto->getName()?: self::DEFAULT_SNAPSHOT_NAME);
        $snapshot->setNotes($this->requestDto->getNotes());
        $snapshot->setType(Snapshot::TYPE_SITE);
        $snapshot->setDirectories($this->requestDto->getDirectories());
        $snapshot->setFilePath($exportFilePath);
        $snapshot->setCreatedAt(new DateTime);

        $snapshots->attach($snapshot);
        $this->repository->save($snapshots);
        return $snapshot;
    }
}
