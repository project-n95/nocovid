<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Component\Task\TaskResponseDto;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService;

class RestoreUploadsTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.uploads';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME = 'snapshot_site_restore_uploads';
    const TASK_TITLE = 'Restoring Uploads Folder';

    /** @var RestoreFilesService */
    private $service;

    /** @var RestoreMergeFilesDto */
    protected $requestDto;

    public function __construct(RestoreFilesService $service, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->service = $service;
        //$this->skipMaintenanceMode();
    }

    public function __destruct()
    {
        parent::__destruct();
        //$this->enableMaintenance(false);
    }

    public function init()
    {
        //$this->enableMaintenance(true);
    }

    public function execute()
    {
        $this->prepare();

        if ($this->shouldDeleteUploads()) {
            return $this->deleteUploads();
        }

        return $this->restoreUploads();
    }

    public function findRequestDto()
    {
        parent::findRequestDto();
        if ($this->requestDto->getSteps()->getTotal() !== null) {
            return;
        }

        // We got 2 options;
        // Merge Files has 1 step; 0 -> Restore
        // If we are not merging, it means we delete the uploads first so we got 2 steps;
        // 0 -> Delete, 1-> Restore
        // Steps starts from 0 but we add total steps number we need due to how $this->generateResponse() work
        $this->requestDto->getSteps()->setTotal($this->requestDto->isMergeFiles() ? 1 : 2);
    }

    public function getTaskName()
    {
        return self::TASK_NAME;
    }

    public function getStatusTitle(array $args = [])
    {
        return __(self::TASK_TITLE, 'wp-staging');
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
     * @return bool
     */
    private function shouldDeleteUploads()
    {
        return !$this->requestDto->isMergeFiles()
            && $this->requestDto->getSteps()->getTotal() === 1
            && $this->requestDto->getSteps()->getCurrent() === 0
        ;
    }

    /**
     * @return TaskResponseDto
     */
    private function deleteUploads()
    {
        $directory = $this->getDirectory();
        // Delete media, if all is deleted then $result will be true
        $result = $this->service->deleteUploads([$this, 'isThreshold']);
        if ($result) {
            $this->logger->info(sprintf(
                'Finished deleting uploads directory at %s except %s sub-directory',
                $directory->getUploadsDirectory(),
                $directory->getDomain()
            ));
            return $this->generateResponse();
        }

        $this->logger->info(sprintf(
            'Deleting uploads directory at %s except %s sub-directory',
            $directory->getUploadsDirectory(),
            $directory->getDomain()
        ));
        // Get the response and current step will be incremented automatically
        $response = $this->generateResponse();
        // Not finished deleting media (hit the threshold) so set the current step 0 to attempt to delete
        // media file with the next request
        $this->requestDto->getSteps()->setCurrent(0);
        return $response;
    }

    private function restoreUploads()
    {
        $directory = $this->getDirectory();
        $result = $this->service->restoreUploads($this->requestDto->getSource(), [$this, 'isThreshold']);
        if ($result) {
            $this->logger->info(sprintf(
                'Finished restoring uploads directory from %s to %s',
                $this->requestDto->getSource(),
                $directory->getUploadsDirectory()
            ));
            return $this->generateResponse();
        }

        $this->logger->info(sprintf(
            'Restoring uploads directory from %s to %s',
            $this->requestDto->getSource(),
            $directory->getUploadsDirectory()
        ));
        // Get the response and current step will be incremented automatically
        $response = $this->generateResponse();
        // Not finished moving media (hit the threshold) so set the current step 1 to attempt to restore
        // left over media files with next request
        $response->setStatus(false);
        $this->requestDto->getSteps()->setCurrent(1);
        return $response;
    }

    private function getDirectory()
    {
        return $this->service->getDirectory();
    }
}
