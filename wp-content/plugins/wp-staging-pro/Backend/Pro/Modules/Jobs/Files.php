<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use WPStaging\Core\Utils\Logger;
use WPStaging\Backend\Modules\Jobs\JobExecutable;
use WPStaging\Backend\Modules\Jobs\Cleaners\WpContentCleaner;
use WPStaging\Backend\Pro\Modules\Jobs\Copiers\Copier;
use WPStaging\Backend\Pro\Modules\Jobs\Copiers\PluginsCopier;
use WPStaging\Backend\Pro\Modules\Jobs\Copiers\ThemesCopier;
use WPStaging\Backend\Pro\Modules\Jobs\Backups\BackupUploadsDir;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Utils\Strings;
use SplFileObject;
/**
 * Class Files
 *
 * @see \WPStaging\Backend\Modules\Jobs\Files Todo
 *
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class Files extends JobExecutable
{
    /** @var SplFileObject */
    private $file;

    /**
     * @var int
     */
    private $maxFilesPerRun;

    /**
     * @var string
     */
    private $destination;


    /**
     * Initialization
     */
    public function initialize()
    {
        if (empty($this->options->clone)) {
            $this->returnException('Fatal Error: Files - Can not detect staging site sub folder');
        }

        $this->destination = ABSPATH;

        $filePath = $this->cache->getCacheDir() . "files_to_copy." . $this->cache->getCacheExtension();

        if (is_file($filePath)) {
            $this->file = new SplFileObject($filePath, 'r');
        }

        // Informational logs
        // step 0 is for backing up and cleaning themes, plugins and uploads dir
        if ($this->options->currentStep === 1) {
            $this->log("Files: Copying files...");
        }

        $this->settings->batchSize = $this->settings->batchSize * 1000000;
        $this->maxFilesPerRun = ($this->settings->cpuLoad === 'low') ? 50 : 1;

        // Finished - We need this here as well as in the execute() method because execute() is not run at all if totalSteps == 0 (e.g. excluding all folders). Otherwise job never ends
        if ($this->isFinished()) {
            $this->prepareResponse(true, false);
            return false;
        }
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps()
    {
        // Add an extra step for backing up and cleaning themes, plugins and uploads dir
        $this->options->totalSteps = ceil($this->options->totalFiles / $this->maxFilesPerRun) + 1;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute()
    {
        // Finished
        if ($this->isFinished()) {
            // Todo: Inject using DI
            $pluginsCopier = new PluginsCopier(new Filesystem);
            $themesCopier  = new ThemesCopier(new Filesystem);

            $this->log('Files: Copying plugins...');
            $pluginsCopier->copy();

            foreach ($pluginsCopier->getErrors() as $error) {
                $this->log($error, Logger::TYPE_ERROR);
            }

            $this->log('Files: Copying themes...');
            $themesCopier->copy();
            foreach ($themesCopier->getErrors() as $error) {
                $this->log($error, Logger::TYPE_ERROR);
            }

            $this->log('Files: Copy process finished. Continue next step...');
            $this->prepareResponse(true, false);
            return false;
        }

        // Backing up and cleaning wp-content directories: uploads, themes and plugins
        if (!$this->backupWpContent()) {
            $this->prepareResponse(false, false);
            return false;
        }

        // Get files and copy'em
        if (!$this->getFilesAndCopy()) {
            $this->prepareResponse(false, false);
            return false;
        }

        // Prepare and return response
        $this->prepareResponse();

        // Not finished
        return true;
    }

    /**
     * @return bool
     */
    private function backupWpContent()
    {
        if ($this->options->currentStep !== 0) {
            return true;
        }

        // @todo inject using DI if possible
        $backupUploads = new BackupUploadsDir($this);
        $contentCleaner = new WpContentCleaner($this);

        $result = $backupUploads->backupWpUploadsDir();
        foreach($backupUploads->getLogs() as $log) {
            if ($log['type'] === Logger::TYPE_ERROR) {
                $this->log($log['msg'], $log['type']);
            } else {
                $this->log($log['msg'], $log['type']);
            }
        }

        if (!$result) {
            return false;
        }

        $result = $contentCleaner->tryCleanWpContent(ABSPATH);
        foreach ($contentCleaner->getLogs() as $log) {
            if ($log['type'] === Logger::TYPE_ERROR) {
                $this->log($log['msg'], $log['type']);
                $this->returnException($log['msg']);
            } else {
                $this->debugLog($log['msg'], $log['type']);
            }
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Get files and copy
     * @return bool
     */
    private function getFilesAndCopy()
    {
        if ($this->options->currentStep === 0) {
            return true;
        }

        if ($this->isOverThreshold()) {
            $this->prepareResponse(false, false);
            $this->saveOptions();
            return false;
        }

        if (isset($this->options->copiedFiles) && $this->options->copiedFiles != 0) {
            $this->file->seek($this->options->copiedFiles - 1);
        }

        $this->file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        for ($i = 0; $i < $this->maxFilesPerRun; $i++) {
            $this->options->copiedFiles++;

            if ($this->file->eof()) {
                break;
            }

            $file = trim(str_replace(PHP_EOL, null, $this->file->fgets()));

            $this->copyFile($file);
        }

        if ($this->options->copiedFiles % 50 == 0) {
            $this->log(sprintf('Total %s files processed', $this->options->copiedFiles));
        }

        return true;
    }

    /**
     * Checks Whether There is Any Job to Execute or Not
     * @return bool
     */
    private function isFinished()
    {
        if (
            $this->options->totalSteps == 1 ||
            $this->options->currentStep > $this->options->totalSteps ||
            $this->options->copiedFiles >= $this->options->totalFiles) {
            return true;
        }
        return false;
    }

    /**
     * @param string
     *
     * @return bool
     */
    private function copyFile($file)
    {
        // Add missing path
        $fullPath = trim($this->options->path . $file);

        $directory = dirname($fullPath);

        // Directory is excluded
        if ($this->isDirectoryExcluded($directory)) {
            $this->debugLog("Files: Skipping file/directory by rule: {$fullPath}", Logger::TYPE_INFO);
            return false;
        }

        // Invalid file, skipping it as if succeeded
        if (!is_file($fullPath)) {
            $this->debugLog("Not a file {$fullPath}");
            return true;
        }
        // Invalid file, skipping it as if succeeded
        if (!is_readable($fullPath)) {
            $this->log("Can't read file {$fullPath}", Logger::TYPE_WARNING);
            return true;
        }

        // File is excluded
        if ((new Filesystem)->isFilenameExcluded($fullPath, $this->options->excludedFiles)) {
            $this->debugLog("Files: Skipping file by rule: {$fullPath}", Logger::TYPE_INFO);
            return false;
        }


        // Get file size
        $fileSize = filesize($fullPath);

        // File is over maximum allowed file size (8MB)
        if ($fileSize >= $this->settings->maxFileSize * 1000000) {
            $this->debugLog("Files: Skipping big file: {$fullPath}", Logger::TYPE_INFO);
            return false;
        }

        // Failed to get destination
        if (($destination = $this->getDestination($fullPath)) === false) {
            $this->log("Files: Can't get the destination of {$fullPath}", Logger::TYPE_WARNING);
            return false;
        }

        // File is over batch size
        if ($fileSize >= $this->settings->batchSize) {
            return $this->copyBig($fullPath, $destination, $this->settings->batchSize);
        }

        // Attempt to copy
        if (!@copy($fullPath, $destination)) {
            $errors = error_get_last();
            $this->log("Files: Failed to copy file to destination. Error: {$errors['message']} {$fullPath} -> {$destination}", Logger::TYPE_ERROR);
            return false;
        }

        // Set file permissions
        @chmod($destination, wpstg_get_permissions_for_file());

        $this->setDirPermissions($destination);

        $this->debugLog('Files: Copy file ' . $fullPath, Logger::TYPE_DEBUG);

        return true;
    }

    /**
     * Set directory permissions
     * @param string $file
     * @return boolean
     */
    private function setDirPermissions($file)
    {
        $dir = dirname($file);
        if (is_dir($dir)) {
            @chmod($dir, wpstg_get_permissions_for_directory());
        }
        return false;
    }

    /**
     * Gets destination file and checks if the directory exists, if it does not attempt to create it.
     * If creating destination directory fails, it returns false, gives destination full path otherwise
     * @param string $file
     * @return bool|string
     */
    private function getDestination($file)
    {
        $relativePath = str_replace($this->options->path, null, $file);

        // Change all plugins dir from 'plugin-name' to 'wpstg-tmp-plugin-name'
        $destinationPath = preg_replace(
            '#wp-content/(plugins|themes)/([A-Za-z0-9-_]+)#',
            'wp-content/$1/' . Copier::PREFIX_TEMP . '$2',
            $this->destination . $relativePath
        );

        $destinationDirectory = dirname($destinationPath);

        if (!is_dir($destinationDirectory) && !(new Filesystem)->mkdir($destinationDirectory)) {
            $this->log("Files: Can not create directory {$destinationDirectory}", Logger::TYPE_ERROR);
            return false;
        }

        return $this->sanitizeDirectorySeparator($destinationPath);
    }


    /**
     * Copy bigger files than $this->settings->batchSize
     * @param string $src
     * @param string $dst
     * @param int $buffersize
     * @return boolean
     */
    private function copyBig($src, $dst, $buffersize)
    {
        $src = fopen($src, 'rb');
        $dest = fopen($dst, 'wb');

        if (!$src || !$dest) {
            return false;
        }

        // Try first method:
        while (!feof($src)) {
            if (fwrite($dest, fread($src, $buffersize)) === false) {
                $error = true;
            }
        }
        // Try second method if first one failed
        if (isset($error) && ($error === true)) {
            while (!feof($src)) {
                if (stream_copy_to_stream($src, $dest, 1024) === false) {
                    $this->log("Can not copy file; {$src} -> {$dest}");
                    fclose($src);
                    fclose($dest);
                    return false;
                }
            }
        }
        // Close any open handler
        fclose($src);
        fclose($dest);
        return true;
    }


    /**
     * Replace forward slash with current directory separator
     * Windows Compatibility Fix
     * @param string $path Path
     *
     * @return string
     */
    private function sanitizeDirectorySeparator($path)
    {
        return (new Strings())->sanitizeDirectorySeparator($path);
    }

    /**
     * Check if directory is excluded from copying
     * @param string $directory
     * @return bool
     */
    private function isDirectoryExcluded($directory)
    {
        $directory = $this->sanitizeDirectorySeparator($directory);
        foreach ($this->options->excludedDirectories as $excludedDirectory) {
            $excludedDirectory = $this->sanitizeDirectorySeparator($excludedDirectory);
            if (strpos(trailingslashit($directory), trailingslashit($excludedDirectory)) === 0) {
                return true;
            }
        }

        return false;
    }

}
