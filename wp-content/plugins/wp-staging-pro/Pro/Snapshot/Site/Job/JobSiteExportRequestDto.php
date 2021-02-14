<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Snapshot\Site\Job;

use WPStaging\Component\Dto\AbstractDto;

class JobSiteExportRequestDto extends AbstractDto
{
    /** @var string|null */
    private $name;

    /** @var string|null */
    private $notes;

    /** @var array|null */
    private $directories;

    /** @var array|null */
    private $excludedDirectories;

    /** @var bool */
    private $exportDatabase;

    /** @var bool */
    private $includeOtherFilesInWpContent;

    /** @var array|null */
    private $includedDirectories;

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
     * @return array|null
     */
    public function getExcludedDirectories()
    {
        return $this->excludedDirectories;
    }

    public function setExcludedDirectories(array $excludedDirectories = null)
    {
        $this->excludedDirectories = $excludedDirectories;
    }

    /**
     * @return bool
     */
    public function isExportDatabase()
    {
        return $this->exportDatabase;
    }

    /**
     * @param bool $exportDatabase
     */
    public function setExportDatabase($exportDatabase)
    {
        $this->exportDatabase = $exportDatabase === true || $exportDatabase === 'true';
    }

    /**
     * @return bool
     */
    public function isIncludeOtherFilesInWpContent()
    {
        return (bool)$this->includeOtherFilesInWpContent;
    }

    /**
     * @param bool $includeOtherFilesInWpContent
     */
    public function setIncludeOtherFilesInWpContent($includeOtherFilesInWpContent)
    {
        $this->includeOtherFilesInWpContent = $includeOtherFilesInWpContent === true || $includeOtherFilesInWpContent === 'true';
    }

    /**
     * @return array|null
     */
    public function getIncludedDirectories()
    {
        return $this->includedDirectories;
    }

    /**
     * @param array|null $includedDirectories
     */
    public function setIncludedDirectories($includedDirectories)
    {
        $this->includedDirectories = $includedDirectories;
    }
}
