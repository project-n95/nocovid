<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class RestoreFilesDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var int */
    protected $id;

    /** @var string */
    protected $source;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = trailingslashit(ABSPATH . str_replace(ABSPATH, null, realpath($source)));
    }


}