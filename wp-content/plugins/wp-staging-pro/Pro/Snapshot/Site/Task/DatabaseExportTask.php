<?php

namespace WPStaging\Pro\Snapshot\Site\Task;

use DateTime;
use Exception;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\TaskResponseDto;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Database\DatabaseDumper;
use WPStaging\Pro\Snapshot\Site\Service\Compressor;
use WPStaging\Pro\Snapshot\Site\Service\ExporterDto;

class DatabaseExportTask extends AbstractTask
{
    use ResourceTrait;

    const FILE_FORMAT = 'sql';
    const REQUEST_NOTATION = 'database.export';
    const REQUEST_DTO_CLASS = DatabaseExportDto::class;
    const TASK_NAME = 'database_export';
    const TASK_TITLE = 'Export database';

    /**  @var DatabaseDumper */
    private $service;

    /** @var TableService */
    private $tableService;

    /** @var Compressor */
    private $exporter;

    /** @var ExporterDto */
    private $exporterDto;

    /** @var DatabaseExportDto */
    protected $requestDto;

    public function __construct(Compressor $exporter, LoggerInterface $logger, Cache $cache, DatabaseDumper $service)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
        $this->exporterDto = $this->exporter->getDto();
        $this->service = $service;
        $this->tableService = new TableService(new Database);
    }

    /**
     * @return object|TaskResponseDto
     * @throws Exception
     */
    public function execute()
    {
        $this->prepare();

        $this->setTimeLimit(DatabaseDumper::MAX_EXECUTION_TIME_SECONDS);

        $result = $this->generateSqlFile();

        $this->requestDto->getSteps()->setCurrent($this->service->getTableIndex());
        $this->requestDto->setTableRowsExported($this->service->getTableRowsExported());
        $this->requestDto->setTableRowsOffset($this->service->getTableRowsOffset());

        $this->writeLog();

        $response = $this->generateResponse();

        if (!$result) {
            // Not finished - continue with current table
            $this->requestDto->getSteps()->setCurrent($this->service->getTableIndex());
            return $response;
        }

        $this->requestDto->getSteps()->setTotal(0);
        $response->setFilePath($result);
        return $response;
    }

    /**
     * @param null|string $filePath
     * @return DatabaseExportResponseDto
     */
    public function generateResponse($filePath = null)
    {
        /** @var DatabaseExportResponseDto $response */
        $response = parent::generateResponse();
        $response->setFilePath($filePath);
        return $response;
    }

    /**
     * @return string
     */
    public function getTaskName()
    {
        return self::TASK_NAME;
    }

    /**
     * @param array $args
     * @return string
     */
    public function getStatusTitle(array $args = [])
    {
        return self::TASK_TITLE;
    }

    /**
     * @return string
     */
    public function getRequestNotation()
    {
        return self::REQUEST_NOTATION;
    }

    /**
     * @return string
     */
    public function getRequestDtoClass()
    {
        return self::REQUEST_DTO_CLASS;
    }

    protected function getResponseDto()
    {
        return new DatabaseExportResponseDto;
    }

    protected function writeLog()
    {
        if ($this->requestDto && $this->requestDto->getTableRowsExported()) {
            $this->logger->info(sprintf(__('Exporting database... %s records saved', 'wp-staging'), number_format_i18n($this->requestDto->getTableRowsExported())));
        } else {
            $this->logger->info('Exporting database...');
        }
    }

    /**
     * @return string|null
     */
    protected function generateSqlFile()
    {
        $this->service->setTables($this->getIncludeTables());
        $this->service->setFileName($this->getStoragePath());
        $this->service->setTableIndex($this->requestDto->getSteps()->getCurrent());
        $this->service->setTableRowsOffset($this->requestDto->getTableRowsOffset());
        $this->service->setTableRowsExported($this->requestDto->getTableRowsExported());

        $this->requestDto->getSteps()->setTotal(count($this->getIncludeTables()) + 1);

        try {
            return $this->service->export([$this, 'isThreshold']);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * @return array
     */
    protected function getIncludeTables()
    {
        $tables = $this->tableService->findTableNamesStartWith($this->service->getDatabase()->getPrefix());
        $views = $this->tableService->findViewsNamesStartWith($this->service->getDatabase()->getPrefix());
        // Add views to bottom of the array to make sure they can be created. Views are based on tables. So tables need to be created before views
        $tablesAndViews = array_merge($tables, $views);
        return $tablesAndViews;
    }

    /**
     * @return string
     */
    private function getStoragePath()
    {
        if (!$this->requestDto->getFileName()) {
            $this->requestDto->setFileName(sprintf(
                '%s_%s_%s.%s',
                rtrim($this->service->getDatabase()->getPrefix(), '_-'),
                (new DateTime)->format('Y-m-d_H-i-s'),
                md5(mt_rand()),
                self::FILE_FORMAT
            ));
        }

        $this->exporterDto->setFilePath($this->exporter->findDestinationDirectory() . $this->requestDto->getFileName());
        return $this->exporter->findDestinationDirectory() . $this->requestDto->getFileName();
    }

}
