<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use RuntimeException;
use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Snapshot\Site\Service\Extractor;
use WPStaging\Pro\Snapshot\Site\Service\ExtractorDto;

class ExtractFilesTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.extract.files';
    const REQUEST_DTO_CLASS = ExtractFilesDto::class;
    const TASK_NAME = 'snapshot_site_extract_files';
    const TASK_TITLE = 'Extracting Files';

    /** @var ExtractFilesDto */
    protected $requestDto;

    /** @var Extractor */
    protected $service;

    public function __construct(Extractor $service, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->service = $service;
    }

    public function execute()
    {
        $this->prepare();

        try {
            $extractDto = $this->provideExtractDto();
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());
            return $this->generateResponse();
        }

        $steps = $this->requestDto->getSteps();

        $steps->setTotal($extractDto->getFileHeadersDto()->getTotalFiles());

        $this->service->setDto($extractDto);
        $this->service->extract([$this, 'isThreshold']);
        $steps->setCurrent($extractDto->getProcessedFiles());

        $this->logger->info(sprintf('Extracted %d/%d files', $steps->getCurrent(), $steps->getTotal()));

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

    public function getTmpDirectory() {
        return $this->service->getTmpDirectory();
    }

    private function provideExtractDto()
    {
        $extractDto = new ExtractorDto;
        $extractDto->setId($this->requestDto->getId());
        $extractDto->setFullPath($this->requestDto->getFilePath());
        $extractDto->setSeekToHeader($this->requestDto->getHeaderStartsAt());
        $extractDto->setSeekToFile($this->requestDto->getFileStartsAt());

        return $extractDto;
    }
}
