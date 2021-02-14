<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Task\TaskResponseDto;

class DatabaseExportResponseDto extends TaskResponseDto
{
    /** @var int|null */
    private $filePath;

    /**
     * @return int|null
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param int|null $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }
}