<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Component\Task\Filesystem;

use WPStaging\Component\Dto\AbstractRequestDto;

class DirectoryScannerRequestDto extends AbstractRequestDto
{
    /** @var array */
    private $included = [];

    /** @var array */
    private $excluded = [];

    /**
     * @return array
     */
    public function getIncluded()
    {
        return (array)$this->included;
    }

    public function setIncluded(array $included = [])
    {
        $this->included = $included;
    }

    /**
     * @return array
     */
    public function getExcluded()
    {
        return (array)$this->excluded;
    }

    public function setExcluded(array $excluded = null)
    {
//        $this->excluded = array_map(static function($dir) {
//            return str_replace(ABSPATH, null, $dir);
//        }, $excluded);
        $this->excluded = (array)$excluded;
    }
}
