<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService;

class RestorePluginsTask extends AbstractTask
{
    use ResourceTrait;
    //use MaintenanceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.plugins';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME = 'snapshot_site_restore_plugins';
    const TASK_TITLE = 'Importing Plugins';

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
        $result = $this->service->restorePlugins($this->requestDto->getSource());
        if ($result) {
            $this->logger->info(sprintf(
                'Finished restoring plugins from %s to  %s',
                $this->requestDto->getSource(),
                WP_PLUGIN_DIR
            ));
            return $this->generateResponse();
        }

        $this->logger->info(sprintf(
            'Restoring plugins from %s to  %s',
            $this->requestDto->getSource(),
            WP_PLUGIN_DIR
        ));
        $response = $this->generateResponse();
        $response->setStatus(false);

        // Set current back to 0 and make sure total is always higher than current.
        // Thus making sure next execution will happen
        $this->requestDto->getSteps()->setCurrent(0);
        $this->requestDto->getSteps()->setTotal(1);
        return $response;
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
