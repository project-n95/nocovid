<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

class RestoreWpContentDto extends RestoreFilesDto
{
    /** @var array */
    protected $excludedDirs;

    /**
     * @return array
     */
    public function getExcludedDirs()
    {
        return $this->excludedDirs;
    }

    /**
     * @param array $excludedDirs
     */
    public function setExcludedDirs($excludedDirs)
    {
        $this->excludedDirs = $excludedDirs;
    }
}