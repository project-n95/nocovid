<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Site\Service;

use WPStaging\Framework\Filesystem\File;

class ExtractorFileDto
{
    /** @var string */
    private $line;

    /** @var string */
    private $relativePath;

    /** @var string */
    private $position;

    /** @var int */
    private $start;

    /** @var int */
    private $totalBytes;

    /** @var int */
    private $writtenBytes;

    public function __construct(File $file, ExtractorDto $restoreDto)
    {
        $this->writtenBytes = (int) $restoreDto->getSeekToFile();
        $this->line = $file->fgets();
        $arr = explode('|', trim($this->line, PHP_EOL));
        list($this->relativePath, $this->position) = $arr;
        list($this->start, $this->totalBytes) = array_map(static function($item) {
            return (int) $item;
        }, explode(':', $this->position));
    }

    public function findSeekTo()
    {
        if (!$this->writtenBytes) {
            return $this->start;
        }
        return $this->start + $this->writtenBytes;
    }

    public function findReadTo()
    {
        $remainingLength = $this->totalBytes - $this->writtenBytes;
        if ($remainingLength > File::MAX_LENGTH_PER_IOP) {
            return File::MAX_LENGTH_PER_IOP;
        }
        return $remainingLength;
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getTotalBytes()
    {
        return $this->totalBytes;
    }

    /**
     * @return int
     */
    public function getWrittenBytes()
    {
        return $this->writtenBytes;
    }

    /**
     * @param int $writtenBytes
     */
    public function setWrittenBytes($writtenBytes)
    {
        $this->writtenBytes = $writtenBytes;
    }

    public function addWrittenBytes($writtenBytes)
    {
        if ($this->writtenBytes === null) {
            $this->writtenBytes = $writtenBytes;
            return;
        }
        $this->writtenBytes += $writtenBytes;
    }

    public function isFinished()
    {
        return $this->writtenBytes >= $this->totalBytes;
    }
}
