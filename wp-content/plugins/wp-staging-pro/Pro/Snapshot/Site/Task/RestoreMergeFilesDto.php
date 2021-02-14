<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class RestoreMergeFilesDto extends RestoreFilesDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var bool */
    private $mergeFiles;

    /**
     * @return bool
     */
    public function isMergeFiles()
    {
        return $this->mergeFiles;
    }

    /**
     * @param bool $mergeFiles
     */
    public function setMergeFiles($mergeFiles)
    {
        $this->mergeFiles = $mergeFiles;
    }
}