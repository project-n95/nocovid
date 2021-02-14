<?php

namespace WPStaging\Pro\Snapshot\Site\Job;

use RuntimeException;
use WPStaging\Component\Job\AbstractQueueJob;
use WPStaging\Component\Job\QueueJobDto;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\Queue;
use WPStaging\Framework\Queue\Storage\CacheStorage;
use WPStaging\Framework\Traits\BenchmarkTrait;
use WPStaging\Framework\Traits\RequestNotationTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Pro\Snapshot\Site\Service\ExportFileHeadersDto;
use WPStaging\Pro\Snapshot\Site\Service\Extractor;
use WPStaging\Pro\Snapshot\Site\Task\ExtractFilesTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreCleanUpTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreDatabaseTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreDeleteExtractDirTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreUploadsTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreMuPluginsTask;
use WPStaging\Pro\Snapshot\Site\Task\RestorePluginsTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreThemesTask;
use WPStaging\Pro\Snapshot\Site\Task\RestoreWpContentTask;
use WPStaging\Framework\Filesystem\Filesystem;

// Need to make sure the destination is not already there before we extract or will create problems with file contents
class JobSiteRestore extends AbstractQueueJob
{
    use BenchmarkTrait;
    use RequestNotationTrait;

    const JOB_NAME = 'snapshot_site_restore';
    const REQUEST_NOTATION = 'jobs.snapshot.site.restore';

    /** @var JobSiteRestoreDto */
    protected $dto;

    /** @var JobSiteRestoreRequestDto */
    protected $requestDto;

    /** @var SnapshotRepository */
    private $snapshotRepository;

    /** @var ExportFileHeadersDto */
    private $exportFileHeadersDto;

    /** @var Directory */
    private $directory;

    /** @var Filesystem */
    private $filesystem;

    public function __construct(
        Cache $jobCache,
        Queue $queue,
        CacheStorage $queueCache,
        QueueJobDto $dto,
        SnapshotRepository $snapshotRepository,
        ExportFileHeadersDto $exportFileHeadersDto,
        Directory $directory,
        Filesystem $filesystem
    ) {
        $this->snapshotRepository   = $snapshotRepository;
        $this->exportFileHeadersDto = $exportFileHeadersDto;
        $this->directory            = $directory;
        $this->filesystem           = $filesystem;

        // This must be last.
        parent::__construct($jobCache, $queue, $queueCache, $dto);
    }

    public function initiateTasks()
    {
        $this->addTasks([
            RestoreDeleteExtractDirTask::class,
            ExtractFilesTask::class,
            RestoreUploadsTask::class,
            RestoreThemesTask::class,
            RestoreDatabaseTask::class,
            RestorePluginsTask::class,
            RestoreMuPluginsTask::class,
            // Improve later; If this is not the last, it will attempt to move plugins, mu-plugins etc. as well
            RestoreWpContentTask::class,
            RestoreCleanUpTask::class,
        ]);
    }

    public function execute()
    {
        $this->startBenchmark();

        $this->prepare();
        $response = $this->getResponse($this->currentTask->execute());

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
        // We need request DTO so we know the file path
        $this->provideRequestDto();

        if ($this->dto->getFileHeaders()) {
            return;
        }

        $fileHeaders = $this->exportFileHeadersDto->hydrateByFilePath($this->requestDto->getFile());
        if (!$fileHeaders->getHeaderStart()) {
            throw new RuntimeException('Failed to get File Headers');
        }

        $this->dto->setFileHeaders($fileHeaders);

        // We need them before we restore the database so cleanup task can clean them up
        $snapshots = $this->snapshotRepository->findAll();
        $this->dto->setSnapshots($snapshots);
        $this->dto->setClones($this->getClones());
    }

    protected function injectRequests()
    {
        if (!$this->currentTask) {
            return;
        }

        switch (get_class($this->currentTask)) {
            case RestoreDeleteExtractDirTask::class:
                $this->injectTaskRequest(
                    RestoreDeleteExtractDirTask::REQUEST_NOTATION,
                    [
                        'source' => $this->withExtractedPath(),
                    ]
                );
                break;
            case ExtractFilesTask::class:
                $this->injectTaskRequest(
                    ExtractFilesTask::REQUEST_NOTATION,
                    [
                        'id' => $this->requestDto->getId(),
                        'filePath' => $this->requestDto->getFile(),
                    ]
                );
                break;
            case RestoreUploadsTask::class:
                $this->injectTaskRequest(
                    RestoreUploadsTask::REQUEST_NOTATION,
                    [
                        'id' => $this->requestDto->getId(),
                        'source' => $this->withExtractedPath($this->dto->getFileHeaders()->getDirUploads()),
                        'mergeFiles' => $this->requestDto->isMergeMediaFiles(),
                    ]
                );
                break;
            case RestoreThemesTask::class:
                $this->injectTaskRequest(
                    RestoreThemesTask::REQUEST_NOTATION,
                    [
                        'source' => $this->withExtractedPath($this->dto->getFileHeaders()->getDirThemes()),
                    ]
                );
                break;
            case RestoreDatabaseTask::class:
                $this->injectTaskRequest(
                    RestoreDatabaseTask::REQUEST_NOTATION,
                    [
                        'file' => $this->withExtractedPath() . basename($this->dto->getFileHeaders()->getDatabaseFile()),
                        'search' => $this->requestDto->getSearch(),
                        'replace' => $this->requestDto->getReplace(),
                        'sourceAbspath' => $this->dto->getFileHeaders()->getAbspath(),
                        'sourceSiteUrl' => $this->dto->getFileHeaders()->getSiteUrl(),
                    ]
                );
                break;
            case RestorePluginsTask::class:
                $this->injectTaskRequest(
                    RestorePluginsTask::REQUEST_NOTATION,
                    [
                        'source' => $this->withExtractedPath($this->dto->getFileHeaders()->getDirPlugins()),
                    ]
                );
                break;
            case RestoreMuPluginsTask::class:
                $this->injectTaskRequest(
                    RestoreMuPluginsTask::REQUEST_NOTATION,
                    [
                        'source' => $this->withExtractedPath($this->dto->getFileHeaders()->getDirMuPlugins()),
                    ]
                );
                break;
            case RestoreWpContentTask::class:
                $this->injectTaskRequest(
                    RestoreWpContentTask::REQUEST_NOTATION,
                    [
                        'id' => $this->requestDto->getId(),
                        'source' => $this->withExtractedPath($this->dto->getFileHeaders()->getDirWpContent()),
                        'excludedDirs' => [
                            $this->withExtractedPath($this->dto->getFileHeaders()->getDirUploads()),
                            $this->withExtractedPath($this->dto->getFileHeaders()->getDirThemes()),
                            $this->withExtractedPath($this->dto->getFileHeaders()->getDirPlugins()),
                            $this->withExtractedPath($this->dto->getFileHeaders()->getDirMuPlugins()),
                        ],
                    ]
                );
                break;
            case RestoreCleanUpTask::class:
                $this->injectTaskRequest(
                    RestoreCleanUpTask::REQUEST_NOTATION,
                    [
                        'snapshots' => $this->dto->getSnapshots(),
                        'clones' => $this->dto->getClones(),
                    ]
                );
                break;
        }
    }

    protected function provideRequestDto()
    {
        $this->requestDto = $this->initializeRequestDto(
            JobSiteRestoreRequestDto::class,
            self::REQUEST_NOTATION
        );
    }

    private function getClones()
    {
        $clones = get_option('wpstg_existing_clones_beta', null);
        if (!$clones || !is_array($clones)) {
            return null;
        }
        return $clones;
    }

    private function withExtractedPath($relativePath = null)
    {
        $dir = $this->directory->getPluginUploadsDirectory() . Extractor::TMP_DIRECTORY . $this->requestDto->getId();
        $this->filesystem->mkdir($dir);

        return trailingslashit($this->filesystem->safePath($dir . $relativePath));
    }
}
