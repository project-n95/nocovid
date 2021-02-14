<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Snapshot\Database\Task\Dto;

use WPStaging\Component\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class SnapshotExportDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var string|null */
    private $prefix;

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}
