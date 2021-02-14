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

class SiteExportTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.export';
    const REQUEST_DTO_CLASS = SiteExportDto::class;
    const TASK_NAME = 'snapshot_site_export';
    const TASK_TITLE = 'Exporting %d Files';

    /** @var SiteExportDto */
    protected $requestDto;

    /** @var Compressor */
    private $exporter;

    public function __construct(Compressor $exporter, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
    }

    public function execute()
    {
        $this->prepare();

        while($this->shouldContinue()) {
            $this->export();
        }

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($this->requestDto->getSteps()->getCurrent());
        $this->logger->info(sprintf('Exported %d files', $steps->getCurrent()));
        return $this->generateResponse();
    }

    public function export()
    {
        $dto = $this->exporter->getDto();
        $dto->setOffset($this->requestDto->getOffset());
        $dto->setFilePath($this->requestDto->getCurrentFile());

        $status = false;
        try {
            $status = $this->exporter->export();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        if ($status !== false) {
            $this->requestDto->getSteps()->incrementCurrentStep();
        }

        $this->requestDto->setOffset($dto->getOffset());
        $this->requestDto->setCurrentFile($dto->getRelativeFilePath());
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
        if ($this->requestDto && $this->requestDto->getSteps()) {
            $total = $this->requestDto->getSteps()->getTotal();
        } else {
            $total = isset($args[0])? (int) $args[0] : 0;
        }

        return sprintf(__(self::TASK_TITLE, 'wp-staging'), $total);
    }

    public function getCaches()
    {
        $caches = parent::getCaches();
        /** @noinspection NullPointerExceptionInspection */
        $caches[] = $this->exporter->getQueue()->getStorage()->getCache();
        $caches[] = $this->exporter->getCacheIndex();
        $caches[] = $this->exporter->getCacheCompressed();
        return $caches;
    }

    /**
     * @return bool
     */
    private function shouldContinue()
    {
        return !$this->isThreshold() && !$this->requestDto->getSteps()->isFinished();
    }
}
