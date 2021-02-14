<?php

namespace WPStaging\Pro\Snapshot\Site\Job;

use WPStaging\Component\Job\QueueJobDto;

class JobSiteExportDto extends QueueJobDto
{
    /** @var int */
    private $totalDirectories;

    /** @var int */
    private $totalFiles;

    /** @var string */
    private $databaseExportPath;

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
     * @return string
     */
    public function getDatabaseExportPath()
    {
        return $this->databaseExportPath;
    }

    /**
     * @param string $databaseExportPath
     */
    public function setDatabaseExportPath($databaseExportPath)
    {
        $this->databaseExportPath = $databaseExportPath;
    }
}