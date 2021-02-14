<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Snapshot\Database\Task\Dto;

use WPStaging\Component\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;
use WPStaging\Pro\Snapshot\Database\Task\CreateSnapshotTask;

class SnapshotCreateDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $notes;

    // Target prefix
    /** @var string|null */
    private $target;

    // Source prefix
    /** @var string|null */
    private $source;

    /** @var string */
    private $type;

    /** @var boolean */
    private $reset;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string|null $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type?: CreateSnapshotTask::AUTOMATIC;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, [CreateSnapshotTask::AUTOMATIC, CreateSnapshotTask::MANUAL], true)) {
            $type = CreateSnapshotTask::AUTOMATIC;
        }
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isReset()
    {
        return $this->reset;
    }

    /**
     * @param bool $reset
     */
    public function setReset($reset)
    {
        if (!is_bool($reset)) {
            $reset = $reset === 'true' || $reset === '1' || $reset === 1;
        }
        $this->reset = $reset;
    }
}
