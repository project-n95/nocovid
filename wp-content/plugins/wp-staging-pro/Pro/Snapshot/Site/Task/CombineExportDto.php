<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Snapshot\Site\Task;

use WPStaging\Component\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class CombineExportDto extends AbstractRequestDto
{
    //const DEFAULT_NAME = 'snapshot';

    use HydrateTrait;
    use ArrayableTrait;

    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $notes;

    /** @var array|null */
    private $directories;

    /** @var bool */
    private $databaseIncluded;

    /** @var string */
    private $databaseFile;

    /** @var int */
    private $totalFiles;

    /** @var int */
    private $totalDirectories;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
     * @return array|null
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    public function setDirectories(array $directories = null)
    {
        $this->directories = $directories;
    }

    /**
     * @return bool
     */
    public function isDatabaseIncluded()
    {
        return $this->databaseIncluded;
    }

    /**
     * @param bool $databaseIncluded
     */
    public function setDatabaseIncluded($databaseIncluded)
    {
        $this->databaseIncluded = $databaseIncluded;
    }

    /**
     * @return string
     */
    public function getDatabaseFile()
    {
        return $this->databaseFile;
    }

    /**
     * @param string $databaseFile
     */
    public function setDatabaseFile($databaseFile)
    {
        $this->databaseFile = $databaseFile;
    }

    /**
     * @return int
     */
    public function getTotalFiles()
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalFiles
     */
    public function setTotalFiles($totalFiles)
    {
        $this->totalFiles = $totalFiles;
    }

    /**
     * @return int
     */
    public function getTotalDirectories()
    {
        return $this->totalDirectories;
    }

    /**
     * @param int $totalDirectories
     */
    public function setTotalDirectories($totalDirectories)
    {
        $this->totalDirectories = $totalDirectories;
    }
}