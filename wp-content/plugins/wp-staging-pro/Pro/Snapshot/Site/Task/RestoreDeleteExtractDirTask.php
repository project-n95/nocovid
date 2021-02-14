<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Task\AbstractTask;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Filesystem\Filesystem;

class RestoreDeleteExtractDirTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION = 'snapshot.site.extract.deleteTarget';
    const REQUEST_DTO_CLASS = RestoreFilesDto::class;
    const TASK_NAME = 'snapshot_site_extract_deleteTarget';
    const TASK_TITLE = 'Cleaning Import Directory';

    /** @var RestoreFilesDto */
    protected $requestDto;

    public function execute()
    {
        $this->prepare();

        $result = (new Filesystem)
            ->setShouldStop([$this, 'isThreshold'])
            ->delete($this->requestDto->getSource())
        ;

        if ($result) {
            $this->logger->info(sprintf('Extract target %s deleted', $this->requestDto->getSource()));
            return $this->generateResponse();
        }

        // Get the response and current step will be incremented automatically
        $response = $this->generateResponse();
        $response->setStatus(false);
        $this->requestDto->getSteps()->setCurrent(0);
        $this->logger->info('Deleting extract target ' . $this->requestDto->getSource());
        return $response;
    }

    public function findRequestDto()
    {
        parent::findRequestDto();
        if ($this->requestDto->getSteps()->getTotal() !== null) {
            return;
        }

        $this->requestDto->getSteps()->setTotal(1);
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
