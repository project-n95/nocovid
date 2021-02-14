<?php
namespace WPStaging\Pro\Snapshot\Site\Task;

class RestoreDatabaseDto extends RestoreFilesDto
{
    /** @var string */
    private $file;

    /** @var array */
    private $search;

    /** @var array */
    private $replace;

    /** @var string */
    private $sourceAbspath;

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setSearch(array $search = null)
    {
        $this->search = $search;
    }

    public function getReplace()
    {
        return $this->replace;
    }

    public function setReplace(array $replace = null)
    {
        $this->replace = $replace;
    }

    /**
     * @return string
     */
    public function getSourceAbspath()
    {
        return $this->sourceAbspath;
    }

    /**
     * @param string $sourceAbspath
     */
    public function setSourceAbspath($sourceAbspath)
    {
        $this->sourceAbspath = $sourceAbspath;
    }


}