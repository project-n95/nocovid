<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Site\Task;

use Exception;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\Compressor;
use WPStaging\Framework\Filesystem\Filesystem;

class IncludeDatabaseTask extends AbstractTask
{

    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.export.include.database';
    const REQUEST_DTO_CLASS = IncludeDatabaseDto::class;
    const TASK_NAME = 'snapshot_site_export_include_database';
    const TASK_TITLE = 'Including Database to Site Export';

    /** @var IncludeDatabaseDto */
    protected $requestDto;

    /** @var Compressor */
    private $exporter;

    public function __construct(Compressor $exporter, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
    }

    public function findRequestDto()
    {
        parent::findRequestDto();

        $filePath = $this->requestDto->getFilePath();
        if (!$filePath || !file_exists($filePath)) {
            $this->logger->warning(sprintf('Database Export file not found: %s', $filePath));
            $this->requestDto->getSteps()->finish();
        }

        if ($this->requestDto->getSteps()->getTotal() > 0) {
            return;
        }

        $this->exporter->getDto()->setFilePath($this->requestDto->getFilePath());
        $this->requestDto->getSteps()->setTotal(filesize($this->exporter->getDto()->getFilePath()));
    }

    public function execute()
    {
        $this->prepare();

        if (!$this->shouldExecute()) {
            return $this->generateResponse();
        }

        $dto = $this->exporter->getDto();
        $dto->setOffset($this->requestDto->getSteps()->getCurrent());

        try {
            $this->exporter->appendFile($this->requestDto->getFilePath());
        } catch (Exception $e) {
            $this->logger->warning(sprintf(
                'Failed to include database export to snapshot: %s',
                $dto->getFilePath()
            ));
        }

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($dto->getOffset());

        if ($dto->isFinished()) {
            $this->requestDto->getSteps()->finish();
            (new Filesystem)->delete($this->requestDto->getFilePath());
        }

        $this->logger->info(sprintf('Included %d bytes of Database Export', $steps->getCurrent()));
        return $this->generateResponse();
    }

    public function getCaches()
    {
        $caches = parent::getCaches();
        $caches[] = $this->exporter->getCacheIndex();
        $caches[] = $this->exporter->getCacheCompressed();
        return $caches;
    }

    public function getTaskName()
    {
        return self::TASK_NAME;
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
     * @inheritDoc
     */
    public function getStatusTitle(array $args = [])
    {
        return __(self::TASK_TITLE, 'wp-staging');
    }

    private function shouldExecute()
    {
        return $this->requestDto->getFilePath()
            && !$this->requestDto->getSteps()->isFinished()
        ;
    }
}
