<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints

namespace WPStaging\Pro\Snapshot\Site\Service;

class ExporterDto
{
    /** @var string */
    private $filePath;

    /** @var int */
    private $writtenBytes;

    /** @var int */
    private $offset;

    /** @var int */
    private $fileSize;

    /** @var bool */
    private $indexPositionCreated;

    // TODO RPoC? Temp Name
    /** @var ExportFileHeadersDto */
    private $fileHeaders;

    /**
     * @return string
     */
    public function getRelativeFilePath()
    {
        return str_replace(ABSPATH, null, $this->filePath);
    }

    public function setRelativeFilePath($filePath)
    {
        $this->setFilePath(ABSPATH . $filePath);
    }

    public function appendWrittenBytes($bytes)
    {
        $this->writtenBytes += (int) $bytes;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->fileSize <= $this->writtenBytes;
    }

    public function resetIfFinished()
    {
        if (!$this->isFinished()) {
            return;
        }

        $this->setFileSize(null);
        $this->setFilePath(null);
        $this->setWrittenBytes(0);
        $this->setOffset(0);
        $this->setIndexPositionCreated(false);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return int
     */
    public function getWrittenBytes()
    {
        /** @noinspection UnnecessaryCastingInspection */
        return (int) $this->writtenBytes;
    }

    /**
     * @param int $writtenBytes
     */
    public function setWrittenBytes($writtenBytes)
    {
        $this->writtenBytes = $writtenBytes;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        /** @noinspection UnnecessaryCastingInspection */
        return (int) $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return bool
     */
    public function isIndexPositionCreated()
    {
        return $this->indexPositionCreated;
    }

    /**
     * @param bool $indexPositionCreated
     */
    public function setIndexPositionCreated($indexPositionCreated)
    {
        $this->indexPositionCreated = $indexPositionCreated;
    }

    /**
     * @return ExportFileHeadersDto
     */
    public function getFileHeaders()
    {
        if (!$this->fileHeaders) {
            $this->fileHeaders = new ExportFileHeadersDto;
        }
        return $this->fileHeaders;
    }

    /**
     * @param ExportFileHeadersDto $fileHeaders
     */
    public function setFileHeaders(ExportFileHeadersDto $fileHeaders)
    {
        $this->fileHeaders = $fileHeaders;
    }
}