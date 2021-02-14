<?php
namespace WPStaging\Pro\Snapshot\Site\Service;

use RuntimeException;

class ExtractorDto
{
    /** @var ExportFileHeadersDto */
    private $fileHeadersDto;

    /** @var int */
    private $id;

    /** @var string */
    private $fullPath;

    /** @var int */
    private $seekToHeader;

    /** @var int */
    private $seekToFile;

    /** @var int */
    private $processedFiles;

    /**
     * @return ExportFileHeadersDto
     */
    public function getFileHeadersDto()
    {
        return $this->fileHeadersDto;
    }

    /**
     * @param ExportFileHeadersDto $fileHeadersDto
     */
    public function setFileHeadersDto($fileHeadersDto)
    {
        $this->fileHeadersDto = $fileHeadersDto;
    }

    /**
     * @return int
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = time();
        }
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
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * @param string $fullPath
     */
    public function setFullPath($fullPath)
    {
        $this->fullPath = $fullPath;

        $this->fileHeadersDto = (new ExportFileHeadersDto)->hydrateByFilePath($fullPath);
        if (!$this->fileHeadersDto->getHeaderStart()) {
            throw new RuntimeException('Failed to get File Headers');
        }
    }

    /**
     * @return int
     */
    public function getSeekToHeader()
    {
        if ($this->seekToHeader) {
            return $this->seekToHeader;
        }

        if (!$this->fileHeadersDto || !$this->fileHeadersDto->getHeaderStart()) {
            throw new RuntimeException('Failed to get header start');
        }

        $this->seekToHeader = $this->fileHeadersDto->getHeaderStart();
        return $this->seekToHeader;
    }

    /**
     * @param int|null $seekToHeader
     */
    public function setSeekToHeader($seekToHeader)
    {
        $this->seekToHeader = $seekToHeader;
    }

    public function addSeekToHeader($lineLength)
    {
        if ($this->seekToHeader === null) {
            $this->seekToHeader = 0;
        }
        $this->seekToHeader += $lineLength;
    }

    /**
     * @return int|null
     */
    public function getSeekToFile()
    {
        return $this->seekToFile;
    }

    /**
     * @param int|null $seekToFile
     */
    public function setSeekToFile($seekToFile)
    {
        $this->seekToFile = $seekToFile;
    }

    public function addSeekToFile($writtenBytes)
    {
        if ($this->seekToFile === null) {
            $this->seekToFile = 0;
        }
        $this->seekToFile += $writtenBytes;
    }

    /**
     * @return int
     */
    public function getProcessedFiles()
    {
        return $this->processedFiles;
    }

    /**
     * @param int $processedFiles
     */
    public function setProcessedFiles($processedFiles)
    {
        $this->processedFiles = $processedFiles;
    }

    public function incrementProcessedFiles()
    {
        if (!$this->processedFiles) {
            $this->processedFiles = 0;
        }
        $this->processedFiles++;
    }

    public function isFinished()
    {
        return $this->seekToHeader >= $this->fileHeadersDto->getHeaderEnd();
    }
}