<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Database\Task;

use Exception;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Database\Service\NotCompatibleException;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;
use WPStaging\Pro\Snapshot\Database\Task\Dto\ExportSnapshotResponseDto;
use WPStaging\Pro\Snapshot\Database\Task\Dto\SnapshotExportDto;

class ExportSnapshotTask extends AbstractTask
{
    const REQUEST_NOTATION = 'snapshot.database.export';
    const REQUEST_DTO_CLASS = SnapshotExportDto::class;
    const TASK_NAME = 'snapshot_database_export';
    const TASK_TITLE = 'Exporting Database';

    /** @var SnapshotExportDto */
    protected $requestDto;

    /** @var SnapshotService */
    private $service;

    public function __construct(SnapshotService $service, Cache $cache)
    {
        parent::__construct($service->getAdapterHelper()->getLogger(), $cache);
        $this->service = $service;
    }

    public function execute()
    {
        $this->prepare();
        $filePath = null;
        try {
            $filePath = $this->service->export($this->requestDto->getPrefix());
        } catch (NotCompatibleException $e) {
            $this->logger->warning($e->getMessage());
        } catch (Exception $e) {
            if (!$this->requestDto->getPrefix()) {
                $this->logger->warning(__('Failed to export snapshot of production database'));
            } else {
                $this->logger->warning(sprintf(
                    __('Failed to export snapshot of tables with %s prefix'),
                    $this->requestDto->getPrefix()
                ));
            }
        }

        $this->executionLog();
        return $this->generateResponse($filePath);
    }

    /**
     * @param null|string $exportFilePath
     * @return ExportSnapshotResponseDto
     */
    public function generateResponse($exportFilePath = null)
    {
        /** @var ExportSnapshotResponseDto $response */
        $response = parent::generateResponse();
        $response->setFilePath($exportFilePath);
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

    /**
     * @inheritDoc
     */
    public function getStatusTitle(array $args = [])
    {
        return __(self::TASK_TITLE, 'wp-staging');
    }

    protected function getResponseDto()
    {
        return new ExportSnapshotResponseDto;
    }

    protected function executionLog()
    {
        $steps = $this->requestDto->getSteps();
        if ($steps->getTotal() > 0) {
            $this->logger->info(sprintf('Exported %d/%d tables', $steps->getCurrent(), $steps->getTotal()));
            return;
        }

        if ($this->requestDto->getPrefix()) {
            $this->logger->info(sprintf('Exported tables with %s prefix', $this->requestDto->getPrefix()));
            return;
        }

        $this->logger->info('Exported live database');
    }
}