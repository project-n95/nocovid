<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService;

class RestoreMuPluginsTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.muPlugins';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME = 'snapshot_site_restore_muPlugins';
    const TASK_TITLE = 'Restoring Mu-Plugins From Snapshot';

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
        $this->service->restoreMuPlugins($this->requestDto->getSource());
        $this->logger->info(sprintf(
            'Finished Restoring mu-plugins from %s to  %s',
            $this->requestDto->getSource(),
            WPMU_PLUGIN_DIR
        ));
        return $this->generateResponse();
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
}
