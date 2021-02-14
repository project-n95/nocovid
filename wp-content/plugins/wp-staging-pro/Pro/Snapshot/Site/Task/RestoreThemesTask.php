<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService;

class RestoreThemesTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.themes';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME = 'snapshot_site_restore_themes';
    const TASK_TITLE = 'Restoring Themes From Snapshot';

    /** @var RestoreFilesService */
    private $service;

    /** @var RestoreFilesDto */
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
        if ($this->requestDto->getSteps()->getTotal() > 0) {
            return;
        }

        // We got 2 steps; 0 -> Delete, 1 -> Restore
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

    private function delete()
    {
        $result = $this->service->deleteThemes($this->requestDto->getSource(), [$this, 'isThreshold']);
        // Deleted everything
        if ($result) {
            $this->logger->info(sprintf(
                'Finished deleting themes in %s except existing themes from %s',
                get_theme_root(),
                $this->requestDto->getSource()
            ));
            return $this->generateResponse();
        }

        // Not finished deleting yet
        $this->logger->info(sprintf(
            'Deleting themes in %s except existing themes from %s',
            get_theme_root(),
            $this->requestDto->getSource()
        ));

        $response = $this->generateResponse();
        $this->requestDto->getSteps()->setCurrent(0);
        return $response;
    }

    private function restore()
    {
        $result = $this->service->restoreThemes($this->requestDto->getSource(), [$this, 'isThreshold']);
        if ($result) {
            $this->logger->info(sprintf(
                'Finished restoring themes from %s to  %s',
                $this->requestDto->getSource(),
                trailingslashit(get_theme_root())
            ));
            return $this->generateResponse();
        }

        $this->logger->info(sprintf(
            'Restoring themes from %s to  %s',
            $this->requestDto->getSource(),
            trailingslashit(get_theme_root())
        ));
        $response = $this->generateResponse();
        $this->requestDto->getSteps()->setCurrent(1);
        $response->setStatus(false);
        return $response;
    }
}
