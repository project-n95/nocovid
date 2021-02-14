<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Collection\OptionCollection;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Pro\Snapshot\Service;
use WPStaging\Framework\Filesystem\Filesystem;

class RestoreCleanUpTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.restore.cleanUp';
    const REQUEST_DTO_CLASS = RestoreCleanUpDto::class;
    const TASK_NAME = 'snapshot_site_restore_cleanUp';
    const TASK_TITLE = 'Cleaning Up Orphaned WP Staging Files';

    /** @var Directory */
    private $directory;

    /** @var RestoreCleanUpDto */
    protected $requestDto;

    /** @var Service */
    private $service;

    public function __construct(Service $service, Directory $directory, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->directory = $directory;
        $this->service = $service;
    }

    public function execute()
    {
        $this->prepare();
        if ($this->requestDto->getSteps()->getCurrent() === 0) {
            return $this->cleanSnapshots();
        }
        return $this->cleanClones();
    }

    public function findRequestDto()
    {
        parent::findRequestDto();
        if ($this->requestDto->getSteps()->getTotal() === 2) {
            return;
        }

        // We got 2 steps; 0 -> Snapshots, 1-> Clones
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

    private function cleanClones()
    {
        $clones = $this->getClones();
        if (!$clones) {
            return $this->generateResponse();
        }

        $fs = (new Filesystem)->setShouldStop([$this, 'isThreshold'])->setLogger($this->logger);
        $isStopped = false;
        foreach($clones as $key => $value) {
            $fs->delete($value['path']);
            $this->service->deleteTablesByPrefix($value['prefix']);

            if ($this->isThreshold()) {
                $isStopped = true;
                break;
            }
        }

        $response = $this->generateResponse();
        if ($isStopped) {
            $this->requestDto->getSteps()->setCurrent(1);
            $response->setStatus(false);
        }
        return $response;
    }

    private function getClones()
    {
        // TODO this should be Repo
        $clones = get_option('wpstg_existing_clones_beta', null);
        // There are no clones in current database, delete them all
        if (!$clones || !is_array($clones)) {
            return $this->requestDto->getClones();
        }

        if ($this->requestDto->getClones()) {
            $clones = array_diff_key($clones, $this->requestDto->getClones());
        }
        return $clones;
    }

    private function cleanSnapshots()
    {
        /** @var Snapshot[]|null $snapshots */
        $snapshots = (new SnapshotRepository)->findAll();
        if (!$snapshots) {
            $snapshots = new OptionCollection(Snapshot::class);
            $snapshots->attachAllByArray($this->requestDto->getSnapshots() ?: []);
        }

        $isStopped = false;
        foreach($snapshots as $snapshot) {
            $this->service->delete($snapshot, true);
            if ($this->isThreshold()) {
                $isStopped = true;
                break;
            }
        }

        if (!$isStopped) {
            return $this->generateResponse();
        }

        $response = $this->generateResponse();
        $this->requestDto->getSteps()->setCurrent(0);
        $response->setStatus(false);
        return $response;
    }

}
