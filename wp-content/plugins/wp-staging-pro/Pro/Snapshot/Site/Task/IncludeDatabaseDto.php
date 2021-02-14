<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class IncludeDatabaseDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var string|null */
    private $filePath;

    /**
     * @return string|null
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string|null $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }
}