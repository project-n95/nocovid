<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints

namespace WPStaging\Pro\Snapshot\Site\Service;

use JsonSerializable;
use WPStaging\Framework\Traits\HydrateTrait;
use WPStaging\Framework\Filesystem\File;

class ExportFileHeadersDto implements JsonSerializable
{
    use HydrateTrait;

    /** @var int */
    private $headerStart;

    /** @var int */
    private $headerEnd;

    /** @var string */
    private $version;

    /** @var array */
    private $directories;

    /** @var int */
    private $totalFiles;

    /** @var int */
    private $totalDirectories;

    /** @var bool */
    private $databaseIncluded;

    /** @var string */
    private $databaseFile;

    /** @var string */
    private $dirWpContent;

    /** @var string */
    private $dirUploads;

    /** @var string */
    private $dirPlugins;

    /** @var string */
    private $dirMuPlugins;

    /** @var string */
    private $dirThemes;

    /** @var string */
    private $siteUrl;

    /** @var string */
    private $abspath;

    /** @var string */
    private $prefix;

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $array = get_object_vars($this);
        $array['dirWpContent'] = $this->getDirWpContent();
        $array['dirPlugins'] = $this->getDirPlugins();
        $array['dirMuPlugins'] = $this->getDirMuPlugins();
        $array['dirThemes'] = $this->getDirThemes();
        return $array;
    }

    public function hydrateByFile(File $file)
    {
        $strJson = $file->readBottomLines(1);
        // has a new line hence the index key 1
        if (!$strJson || !$strJson[1]) {
            return $this;
        }

        $data = json_decode($strJson[1], true);
        return (new self)->hydrate($data);
    }

    public function hydrateByFilePath($filePath)
    {
        return $this->hydrateByFile(new File($filePath));
    }

    /**
     * @return int
     */
    public function getHeaderStart()
    {
        return $this->headerStart;
    }

    /**
     * @param int $headerStart
     */
    public function setHeaderStart($headerStart)
    {
        $this->headerStart = $headerStart;
    }

    /**
     * @return int
     */
    public function getHeaderEnd()
    {
        return $this->headerEnd;
    }

    /**
     * @param int $headerEnd
     */
    public function setHeaderEnd($headerEnd)
    {
        $this->headerEnd = $headerEnd;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * @param array $directories
     */
    public function setDirectories($directories)
    {
        $this->directories = $directories;
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
        $this->databaseFile = str_replace(ABSPATH, null, $databaseFile);
    }

    /**
     * @return string
     */
    public function getDirWpContent()
    {
        if (!$this->dirWpContent) {
            $this->setDirWpContent(WP_CONTENT_DIR);
        }
        return $this->dirWpContent;
    }

    /**
     * @param string $dirWpContent
     */
    public function setDirWpContent($dirWpContent)
    {
        $this->dirWpContent = str_replace(ABSPATH, null, $dirWpContent);
    }

    /**
     * @return string
     */
    public function getDirUploads()
    {
        return $this->dirUploads;
    }

    /**
     * @param string $dirUploads
     */
    public function setDirUploads($dirUploads)
    {
        $this->dirUploads = str_replace(ABSPATH, null, $dirUploads);
    }

    /**
     * @return string
     */
    public function getDirPlugins()
    {
        if (!$this->dirPlugins) {
            $this->setDirPlugins(WP_PLUGIN_DIR);
        }
        return $this->dirPlugins;
    }

    /**
     * @param string $dirPlugins
     */
    public function setDirPlugins($dirPlugins)
    {
        $this->dirPlugins = str_replace(ABSPATH, null, $dirPlugins);
    }

    /**
     * @return string
     */
    public function getDirMuPlugins()
    {
        if (!$this->dirMuPlugins) {
            $this->setDirMuPlugins(WPMU_PLUGIN_DIR);
        }
        return $this->dirMuPlugins;
    }

    /**
     * @param string $dirMuPlugins
     */
    public function setDirMuPlugins($dirMuPlugins)
    {
        $this->dirMuPlugins = str_replace(ABSPATH, null, $dirMuPlugins);
    }

    /**
     * @return string
     */
    public function getDirThemes()
    {
        if (!$this->dirThemes) {
            $this->setDirThemes(get_theme_root());
        }
        return $this->dirThemes;
    }

    /**
     * @param string $dirThemes
     */
    public function setDirThemes($dirThemes)
    {
        $this->dirThemes = str_replace(ABSPATH, null, $dirThemes);
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    /**
     * @param string $siteUrl
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * @return string
     */
    public function getAbspath()
    {
        return $this->abspath;
    }

    /**
     * @param string $abspath
     */
    public function setAbspath($abspath)
    {
        $this->abspath = $abspath;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }



}
