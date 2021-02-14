<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Site\Service;


use DateTime;
use RuntimeException;
use WPStaging\Vendor\Symfony\Component\Filesystem\Exception\IOException;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\Queue;
use WPStaging\Framework\Queue\Storage\BufferedCacheStorage;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Filesystem\DiskFullException;
use WPStaging\Framework\Filesystem\FileScannerControl;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Core\WPStaging;

class Compressor
{
    use ResourceTrait;

    const FILE_INDEX = 'exporter_index';
    const FILE_COMPRESSED = 'exporter_compressed';

    const EXPORT_DIR_NAME = 'snapshots/site';
    const FILE_EXTENSION = 'wpstg';

    /** @var BufferedCacheStorage */
    private $storage;

    /** @var BufferedCache */
    private $cacheIndex;

    /** @var BufferedCache */
    private $cacheCompressed;

    /** @var Queue|null */
    private $queue;

    /** @var ExporterDto */
    private $dto;

    /** @var Directory */
    private $directory;

    /** @var int */
    private $compressedFileSize = 0;

    /** @var string|null */
    private $exportDirectory;

    // TODO telescoped
    public function __construct(BufferedCache $cache, BufferedCacheStorage $storage, Directory $directory)
    {
        $this->initiateStartTime();
        $this->dto = new ExporterDto;

        $this->dto->getFileHeaders()->setVersion(WPStaging::getVersion());

        $cache->setLifetime(DAY_IN_SECONDS);

        $this->directory = $directory;
        $this->storage = clone $storage;
        $this->cacheIndex = clone $cache;
        $this->cacheCompressed = clone $cache;

        $this->cacheIndex->setFilename(self::FILE_INDEX);
        $this->cacheCompressed->setFilename(self::FILE_COMPRESSED);

        $this->setQueueByName();

//        clearstatcache();
        if (file_exists($this->cacheCompressed->getFilePath())) {
            $this->compressedFileSize = filesize($this->cacheCompressed->getFilePath());
        }
    }

    public function __destruct()
    {
        if (!$this->dto->isFinished()) {
            $this->queue->push($this->dto->getRelativeFilePath());
        }
    }

    /**
     * @return ExporterDto
     */
    public function getDto()
    {
        if ($this->dto && $this->dto->getFileHeaders() && !$this->dto->getFileHeaders()->getDirUploads()) {
            $this->dto->getFileHeaders()->setDirUploads($this->directory->getUploadsDirectory());
        }
        return $this->dto;
    }

    /**
     * @param string $name
     */
    public function setQueueByName($name = FileScannerControl::DATA_CACHE_FILE)
    {
        $this->storage->setIsUsePrefix(false);

        $this->queue = new Queue;
        $this->queue->setName($name);
        $this->queue->setStorage($this->storage);
    }

    /**
     * Returns;
     * `true` -> finished
     * `false` -> not finished
     * `null` -> skip / didn't do anything
     * @return bool
     */
    public function export()
    {
        $filePath = $this->queue->last();
        if (!$filePath) {
            return true;
        }

        return $this->appendFile(ABSPATH . trim(rtrim($filePath, PHP_EOL)));
    }

    /**
     * @param string $fullFilePath
     * @return bool|null
     */
    public function appendFile($fullFilePath)
    {
        // We can use evil '@' as we don't check is_file || file_exists to speed things up.
        // Since in this case speed > anything else
        // However if @ is not used, depending on if file exists or not this can throw E_WARNING.
        $resource = @fopen($fullFilePath, 'rb');
        if (!$resource) {
            return null;
        }

        // RPoC; little insurance
        if (!$this->compressedFileSize && file_exists($this->cacheCompressed->getFilePath())) {
            $this->compressedFileSize = filesize($this->cacheCompressed->getFilePath());
        }

        $fileStats = fstat($resource);
        $this->initiateDtoByFilePath($fullFilePath, $fileStats);
        $writtenBytes = $this->appendToCompressedFile($resource, $fullFilePath);

        $this->compressedFileSize += $writtenBytes;
        $this->addIndex($writtenBytes);
        $this->dto->appendWrittenBytes($writtenBytes);

        $isFinished = $this->dto->isFinished();
        $this->dto->resetIfFinished();
        return $isFinished;
    }

    public function initiateDtoByFilePath($filePath, array $fileStats = [])
    {
        if ($filePath === $this->dto->getFilePath() && $fileStats['size'] === $this->dto->getFileSize()) {
            return;
        }

        $this->dto->setFilePath($filePath);
        $this->dto->setFileSize($fileStats['size']);
        $this->dto->setWrittenBytes($this->dto->getOffset());
        $this->dto->setIndexPositionCreated(false);
    }

    /**
     * Combines index and compressed file, renames / moves it to destination
     * @return string|null
     */
    public function combine()
    {
        $indexFile = $this->cacheIndex->getPath() . $this->cacheIndex->getFilename() . '.' . Cache::EXTENSION;
        $resource = fopen($indexFile, 'rb');
        if (!$resource) {
            throw new RuntimeException('Index file not found!');
        }

        $fileStats = fstat($resource);
        $this->initiateDtoByFilePath($indexFile, $fileStats);

        while (!$this->shouldStop()) {
            $writtenBytes = $this->appendToCompressedFile($resource, $indexFile);
            $this->dto->appendWrittenBytes($writtenBytes);
        }

        if (!$this->dto->isFinished()) {
            return null;
        }

        clearstatcache();
        $totalSize = filesize($this->cacheCompressed->getFilePath());

        $fileHeaders = $this->dto->getFileHeaders();
        $fileHeaders->setHeaderStart(($totalSize - $fileStats['size']));
        $fileHeaders->setHeaderEnd($totalSize);

        $this->cacheCompressed->append(json_encode($fileHeaders));
        $this->cacheIndex->delete();
        $this->dto->isFinished();

        return $this->renameExport();
    }

    /**
     * @return string
     */
    public function findDestinationDirectory()
    {
        if ($this->exportDirectory) {
            return $this->exportDirectory;
        }

        $dirname = sprintf('%s/%s/', $this->directory->getDomain(), self::EXPORT_DIR_NAME);
        $this->exportDirectory = (new Filesystem)->mkdir($this->directory->getUploadsDirectory() . $dirname);
        return $this->exportDirectory;
    }

    /**
     * @return string
     */
    public function generateDestination()
    {
        $fileName = sprintf(
            '%s_%s_%s.%s',
            parse_url(get_home_url())['host'],
            (new DateTime)->format('Y-m-d_H-i-s'),
            substr(md5(mt_rand()), 0, 6),
            self::FILE_EXTENSION
        );

        return $this->findDestinationDirectory() . $fileName;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getCacheIndex()
    {
        return $this->cacheIndex;
    }

    public function getCacheCompressed()
    {
        return $this->cacheCompressed;
    }

    /**
     * @param string $fullPath
     * @return ExportFileHeadersDto|null
     */
    public function findExportFileInfo($fullPath)
    {
        $dto = (new ExportFileHeadersDto)->hydrateByFilePath($fullPath);
        if (!$dto->getHeaderStart()) {
            return null;
        }

        $dto->setDirUploads($this->directory->getUploadsDirectory());
        return $dto;
    }

    private function renameExport()
    {
        $destination = $this->generateDestination();

        if (!rename($this->cacheCompressed->getFilePath(), $destination)) {
            throw new RuntimeException('Failed to generate destination');
        }

        return $destination;
    }

    private function shouldStop()
    {
        return $this->isThreshold() || $this->dto->isFinished();
    }

    /**
     * @param int $writtenBytes
     */
    private function addIndex($writtenBytes)
    {
        $start = $this->compressedFileSize - $this->dto->getFileSize();
        if ($start < 0) {
            $start = 0;
        }

        if (!$this->dto->isIndexPositionCreated()) {
            $info = $this->dto->getRelativeFilePath() . '|' . $start . ':' . $writtenBytes;
            $this->cacheIndex->append($info);
            $this->dto->setIndexPositionCreated(true);
            return;
        }

        $info = $this->cacheIndex->readLines(1, null, BufferedCache::POSITION_BOTTOM);
        if (!$info) {
            throw new RuntimeException('Failed to read export header file information');
        }

        $_writtenBytes = explode(':', reset($info));
        $_writtenBytes = end($_writtenBytes);

        $this->cacheIndex->deleteBottomBytes(strlen($_writtenBytes . PHP_EOL));
        $this->cacheIndex->append($writtenBytes + $_writtenBytes);
    }

    private function appendToCompressedFile($resource, $filePath)
    {
        try {
            return $this->cacheCompressed->appendFile(
                $resource,
                $this->dto->getOffset(),
                [$this, 'isThreshold']
            );
        } catch (IOException $e) {
            throw new IOException('Failed to export file ' . $filePath . '. Reason: ' . $e->getMessage());
//            $this->cacheIndex->delete();
//            $this->cacheCompressed->delete();
        }

        // Checking if the disk is potentially full
        $randomString = wp_generate_password(true);
        $filePath = $this->directory->getUploadsDirectory() . "test$randomString.wpstg";
        $bytesWritten = file_put_contents($filePath, '1');
        if (!$bytesWritten) {
            throw new DiskFullException;
        }

        @unlink($filePath);

        throw new IOException('Failed to export file ' . $filePath);
    }
}
