<?php

namespace WPStaging\Pro\Snapshot\Site\Job;

use WPStaging\Component\Job\AbstractQueueJob;
use WPStaging\Component\Task\Filesystem\DirectoryScannerTask;
use WPStaging\Component\Task\Filesystem\FileScannerTask;
use WPStaging\Component\Task\TaskResponseDto;
use WPStaging\Core\Forms\Elements\File;
use WPStaging\Framework\Traits\BenchmarkTrait;
use WPStaging\Framework\Traits\RequestNotationTrait;
use WPStaging\Pro\Snapshot\Site\Task\DatabaseExportResponseDto;
use WPStaging\Pro\Snapshot\Site\Task\DatabaseExportTask;
use WPStaging\Pro\Snapshot\Site\Task\CombineExportTask;
use WPStaging\Pro\Snapshot\Site\Task\IncludeDatabaseTask;
use WPStaging\Pro\Snapshot\Site\Task\SiteExportTask;

class JobSiteExport extends AbstractQueueJob
{
    use BenchmarkTrait;
    use RequestNotationTrait;

    const JOB_NAME = 'snapshot_site_export';
    const REQUEST_NOTATION = 'jobs.snapshot.site.create';

    /** @var JobSiteExportDto */
    protected $dto;

    /** @var JobSiteExportRequestDto */
    protected $requestDto;

    private static $availableTasks = [
        DirectoryScannerTask::class,
        FileScannerTask::class,
        SiteExportTask::class,
        DatabaseExportTask::class,
        IncludeDatabaseTask::class,
        CombineExportTask::class,
    ];

    public function initiateTasks()
    {
        $this->addTasks(self::$availableTasks);
    }

    public function execute()
    {
        $this->startBenchmark();

        $this->prepare();
        $response = $this->getResponse($this->currentTask->execute());
        $this->setDtoByResponse($response);

        $this->finishBenchmark(get_class($this->currentTask));

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getJobName()
    {
        return self::JOB_NAME;
    }

    protected function init()
    {
        $this->provideRequestDto();

        if ($this->requestDto->getDirectories()) {
            $this->requestDto->setDirectories(array_unique($this->requestDto->getDirectories()));
        }

        if ($this->requestDto->isExportDatabase()) {
            return;
        }

        $removeTasks = [
            DatabaseExportTask::class,
            IncludeDatabaseTask::class,
        ];
        foreach ($removeTasks as $removeTask) {
            $key = array_search($removeTask, self::$availableTasks);
            if ($key) {
                // TODO PHP5.6; use a property instead of modifying self::$availableTasks
                unset(self::$availableTasks[$key]);
            }
        }
    }

    protected function findCurrentStatusTitleArgs()
    {
        $args = [];
        if (!$this->currentTask) {
            return $args;
        }

        switch (get_class($this->currentTask)) {
            case FileScannerTask::class:
                $args[] = $this->dto->getTotalDirectories();
                break;
            case SiteExportTask::class:
                $args[] = $this->dto->getTotalFiles();
                break;
        }

        return $args;
    }

    protected function provideRequestDto()
    {
        if ($this->requestDto) {
            return;
        }

        $this->requestDto = $this->initializeRequestDto(
            JobSiteExportRequestDto::class,
            self::REQUEST_NOTATION
        );
    }

    protected function injectRequests()
    {
        if (!$this->currentTask) {
            return;
        }

        switch (get_class($this->currentTask)) {
            case DirectoryScannerTask::class:
                $this->injectTaskRequest(
                    DirectoryScannerTask::REQUEST_NOTATION,
                    [
                        'included'                     => $this->requestDto->getDirectories(),
                        'excluded'                     => $this->requestDto->getExcludedDirectories(),
                    ]
                );
                break;
            case FileScannerTask::class:
                $this->injectTaskRequest(
                    FileScannerTask::REQUEST_NOTATION,
                    [
                        'includeOtherFilesInWpContent' => $this->requestDto->isIncludeOtherFilesInWpContent(),
                    ]
                );
                break;
            case SiteExportTask::class:
                $this->injectTaskRequest(
                    SiteExportTask::REQUEST_NOTATION,
                    [
                        'steps' => [
                            'total' => $this->dto->getTotalFiles(),
                        ],
                    ]
                );
                break;
            case IncludeDatabaseTask::class:
                $this->injectTaskRequest(
                    IncludeDatabaseTask::REQUEST_NOTATION,
                    [
                        'filePath' => $this->dto->getDatabaseExportPath(),
                    ]
                );
                break;
            case CombineExportTask::class:
                $this->injectTaskRequest(
                    CombineExportTask::REQUEST_NOTATION,
                    [
                        'id' => $this->dto->getId(),
                        'name' => $this->requestDto->getName(),
                        'notes' => $this->requestDto->getNotes(),
                        'directories' => array_map(
                            static function($dir) {
                                return str_replace(ABSPATH, null, $dir);
                            },
                            $this->requestDto->getIncludedDirectories() ?: []
                        ),
                        'databaseIncluded' => $this->requestDto->isExportDatabase(),
                        'databaseFile' => $this->dto->getDatabaseExportPath(),
                        'totalFiles' => $this->dto->getTotalFiles(),
                        'totalDirectories' => $this->dto->getTotalDirectories(),
                    ]
                );
                break;
        }
    }

    private function setDtoByResponse(TaskResponseDto $responseDto)
    {
        switch ($responseDto->getTask()) {
            case DirectoryScannerTask::TASK_NAME:
                $this->dto->setTotalDirectories($responseDto->getTotal());
                break;
            case FileScannerTask::TASK_NAME:
                $this->dto->setTotalFiles($responseDto->getTotal());
                break;
            case DatabaseExportTask::TASK_NAME:
                /** @var DatabaseExportResponseDto $responseDto */
                $this->dto->setDatabaseExportPath($responseDto->getFilePath());
                break;
        }
    }
}
