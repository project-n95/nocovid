<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Component\Task\TaskResponseDto;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService;

class RestoreWpContentTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.wpContent';
    const REQUEST_DTO_CLASS = RestoreWpContentDto::class;
    const TASK_NAME = 'snapshot_site_restore_wpContent';
    const TASK_TITLE = 'Restoring WP Content From Snapshot';

    /** @var RestoreFilesService */
    private $service;

    /** @var RestoreWpContentDto */
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
        if ($this->requestDto->getSteps()->getCurrent() === 0) {
            return $this->delete();
        }
        return $this->restore();
    }

    public function findRequestDto()
    {
        parent::findRequestDto();
        if ($this->requestDto->getSteps()->getTotal() === 2) {
            return;
        }

        // We got 2 steps; 0 -> Delete, 1-> Restore
        // Steps starts from 0 but we add total steps number we need due to how $this->generateResponse() work
        $this->requestDto->getSteps()->setTotal(2);
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
     * @return TaskResponseDto
     */
    private function delete()
    {
        $result = $this->service->deleteWpContent($this->requestDto->getSource(), [$this, 'isThreshold']);
        if ($result) {
            $this->logger->info('Finished deleting target wp-content directories from ' . $this->requestDto->getSource());
            return $this->generateResponse();
        }

        $this->logger->info('Deleting target wp-content directories from ' . $this->requestDto->getSource());
        // Get the response and current step will be incremented automatically
        $response = $this->generateResponse();
        $this->requestDto->getSteps()->setCurrent(0);
        return $response;
    }

    private function restore()
    {
        $result = $this->service->restoreWpContent(
            $this->requestDto->getSource(),
            $this->requestDto->getExcludedDirs(),
            [$this, 'isThreshold']
        );
        if ($result) {
            $this->logger->info(sprintf(
                'Finished restoring target wp-content directories from %s except %s',
                 $this->requestDto->getSource(),
                implode(', ', $this->requestDto->getExcludedDirs())
            ));
            return $this->generateResponse();
        }

        $this->logger->info(sprintf(
            'Restoring target wp-content directories from %s except %s',
            $this->requestDto->getSource(),
            implode(', ', $this->requestDto->getExcludedDirs())
        ));
        // Get the response and current step will be incremented automatically
        $response = $this->generateResponse();
        $this->requestDto->getSteps()->setCurrent(1);
        $response->setStatus(false);
        return $response;
    }
}
