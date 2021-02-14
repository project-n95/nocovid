<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Multisite;

use WPStaging\Framework\Utils\Strings;
use WPStaging\Core\Iterators\RecursiveDirectoryIterator;
use WPStaging\Backend\Pro\Modules\Filters\RecursiveFilterExclude;
use Exception;
use WPStaging\Backend\Modules\Jobs\JobExecutable;
use RecursiveIteratorIterator;

/**
 * Class ScanDirectories
 * Scan the file system for all files and folders to copy
 * @todo This class is similar to \WPStaging\Backend\Modules\Jobs\Multisite\Directories, check which one is being used and remove one of them if it's dead code
 * @see \WPStaging\Backend\Pro\Modules\Jobs\ScanDirectories Can we unify this?
 * @package WPStaging\Backend\Modules\Directories
 */
class ScanDirectories extends JobExecutable
{

    /**
     * @var array
     */
    private $files = [];

    /**
     * Total steps to do
     * @var int
     */
    private $total = 3;

    /**
     * File name of the caching file
     * @var string
     */
    private $filename;

    /**
     * Initialize
     */
    public function initialize() {
        $this->filename = $this->cache->getCacheDir() . "files_to_copy." . $this->cache->getCacheExtension();
    }

    /**
     * Calculate Total Steps in This Job and Assign It to $this->options->totalSteps
     * @return void
     */
    protected function calculateTotalSteps() {

        $this->options->totalSteps = $this->total + count( $this->options->extraDirectories );
    }

    /**
     * Start Module
     * @return object
     */
    public function start() {

        // Execute steps
        $this->run();

        // Save option, progress
        $this->saveProgress();

        return ( object ) $this->response;
    }

    /**
     * Step 0
     * Get Plugin Files
     */
    public function getStagingPlugins()
    {
        $path = trailingslashit($this->options->path) . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;

        if ($this->isDirectoryExcluded($path)) {
            return true;
        }

        $files = $this->open($this->filename, 'a');

        $excludeFolders = [
            'cache',
            'wps-hide-login',
            'node_modules',
            'nbproject',
            'wp-staging',
        ];

        try {
            $iterator = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveFilterExclude($iterator, apply_filters('wpstg_push_excl_folders_custom', $excludeFolders));
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);

            $this->log(sprintf('Scanning %s for its sub-directories and files', $path));

            foreach($iterator as $item) {
                // Skip any file under wp-content/plugins/* (e.g. index.php) and include only valid plugins in subfolders
                if($path === trailingslashit($item->getPath())){
                    continue;
                }
                if ($item->isFile()) {
                    if ($this->write($files, 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $iterator->getSubPathName() . PHP_EOL)) {
                        $this->options->totalFiles++;
                        $this->options->totalFileSize += $iterator->getSize();
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->returnException('Error: ' . $e->getMessage());
        }

        $this->close( $files );
        return true;
    }

    /**
     * Step 1
     * Get Themes Files
     */
    public function getStagingThemes()
    {
        $path = trailingslashit($this->options->path) . 'wp-content' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;

        if ($this->isDirectoryExcluded($path)) {
            return true;
        }

        $files = $this->open($this->filename, 'a');

        try {
            $iterator = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveFilterExclude($iterator, []);
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);

            $this->log(sprintf('Scanning %s for its sub-directories and files', $path));

            foreach($iterator as $item) {
                if ($item->isFile()) {
                    if ($this->write($files, 'wp-content' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $iterator->getSubPathName() . PHP_EOL)) {
                        $this->options->totalFiles++;
                        $this->options->totalFileSize += $iterator->getSize();
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->returnException('Error: ' . $e->getMessage());
        }

        $this->close( $files );
        return true;
    }

    /**
     * Step 2
     * Get Media Files
     */
    public function getStagingUploads() {
        // Detect the method, old or new one. Older WP Staging sites used a fixed upload location wp-content/uploads/2019 where the new approach keep the multisites
        $folder = trailingslashit( $this->options->path ) . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'sites';
        if( is_dir( $folder ) ) {
            $this->getStagingUploadsNew();
        } else {
            $this->getStagingUploadsOld();
        }
    }

    /**
     * Get all files from the upload folder. Old approach 2018
     * This is used only for compatibility reasons and will be removed in the future
     * @deprecated since version 2.7.6
     * @return boolean
     */
    public function getStagingUploadsOld()
    {
        $path = trailingslashit($this->options->path) . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        if ($this->isDirectoryExcluded($path)) {
            return true;
        }

        if (!is_dir($path)) {
            return true;
        }

        $files = $this->open($this->filename, 'a');

        $excludeFolders = [
            'wp-staging',
            'node_modules',
            'nbproject',
        ];

        try {
            $iterator = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveFilterExclude($iterator, $excludeFolders);
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);

            $this->log(sprintf('Scanning %s for its sub-directories and files', $path));

            foreach($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $newFile = $this->getRelUploadPath() . $iterator->getSubPathName() . PHP_EOL;
                if ($this->write($files, $newFile)) {
                    $this->options->totalFiles++;
                    $this->options->totalFileSize += $iterator->getSize();
                }
            }
        }
        catch(Exception $e) {
            $this->returnException('Error: ' . $e->getMessage());
        }

        $this->close($files);
        return true;
    }

    /**
     * Get all files from the upload folder. New approach 2019
     * @return boolean
     */
    private function getStagingUploadsNew()
    {
        $path = trailingslashit($this->options->path) . $this->getRelUploadDir();

        if ($this->isDirectoryExcluded($path)) {
            return true;
        }

        if (!is_dir($path)) {
            return true;
        }

        $files = $this->open($this->filename, 'a');

        $excludeFolders = [
            'wp-staging',
            'node_modules',
            'nbproject',
        ];

        try {
            $iterator = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveFilterExclude($iterator, $excludeFolders);
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);

            $this->log(sprintf('Scanning %s for its sub-directories and files', $path));

            foreach($iterator as $item) {
                if (!$item->isFile()) {
                    continue;
                }

                $newFile = $this->getRelUploadDir() . $iterator->getSubPathName() . PHP_EOL;

                if ($this->write($files, $newFile)) {
                    $this->options->totalFiles++;
                    $this->options->totalFileSize += $iterator->getSize();
                }
            }
        }
        catch(Exception $e) {
            $this->returnException('Error: ' . $e->getMessage());
        }

        $this->close($files);
        return true;
    }

    /**
     * Get relative path to upload dir of staging site e.g. /wp-content/uploads/sites/2 for childsites or wp-content/uploads for main network site
     */
    private function getRelUploadDir() {
        $uploads     = wp_upload_dir();
        $basedir     = $uploads['basedir'];
        $relativeDir = str_replace( ABSPATH, '', wpstg_replace_windows_directory_separator( $basedir ) );
        return trailingslashit( $relativeDir );
    }

    /**
     * Get the relative path to uploads folder of the multisite live site e.g. wp-content/blogs.dir/ID/files or wp-content/upload/sites/ID or wp-content/uploads
     * @param string $pathUploadsFolder Absolute path to the uploads folder
     * @param string $subPathName
     * @return string
     */
    private function getRelUploadPath() {
        // Check first which structure is used
        $uploads = wp_upload_dir();
        $basedir = $uploads['basedir'];
        $blogId  = get_current_blog_id();
        if( strpos( $basedir, 'blogs.dir' ) === false ) {
            // Since WP 3.5
            $getRelUploadPath = $blogId > 1 ?
                    'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . get_current_blog_id() . DIRECTORY_SEPARATOR :
                    'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        } else {
            // old blog structure before WP 3.5
            $getRelUploadPath = $blogId > 1 ?
                    'wp-content' . DIRECTORY_SEPARATOR . 'blogs.dir' . DIRECTORY_SEPARATOR . get_current_blog_id() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR :
                    'wp-content' . DIRECTORY_SEPARATOR;
        }
        return $getRelUploadPath;
    }

    /**
     * Step 4 - x
     * Get extra folders of the wp root level
     * Does not collect wp-includes, wp-admin and wp-content folder
     */
    private function getExtraFiles($folder)
    {
        $folder = rtrim($folder, DIRECTORY_SEPARATOR);

        if (!is_dir($folder)) {
            return true;
        }

        $files = $this->open($this->filename, 'a');

        try {
            $iterator = new RecursiveDirectoryIterator($folder);
            $iterator = new RecursiveFilterExclude($iterator, []);
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD);

            $this->log(sprintf('Scanning %s for its sub-directories and files', $folder));

            foreach($iterator as $item) {
                if ($item->isFile()) {
                    $newPath = trailingslashit(str_replace($this->options->path, '', $folder)) . $iterator->getSubPathName() . PHP_EOL;
                    if ($this->write($files, ltrim($newPath, DIRECTORY_SEPARATOR))) {
                        $this->options->totalFiles++;
                        $this->options->totalFileSize += $iterator->getSize();
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->returnException('Error: ' . $e->getMessage());
        }

        $this->close($files);
        return true;
    }

    /**
     * Closes a file handle
     *
     * @param  resource $handle File handle to close
     * @return boolean
     */
    public function close( $handle ) {
        return @fclose( $handle );
    }

    /**
     * Opens a file in specified mode
     *
     * @param  string   $file Path to the file to open
     * @param  string   $mode Mode in which to open the file
     * @return resource
     * @throws Exception
     */
    public function open( $file, $mode ) {

        $file_handle = @fopen( $file, $mode );
        if( $file_handle === false ) {
            $this->returnException( sprintf( __( 'Unable to open %s with mode %s', 'wp-staging' ), $file, $mode ) );
        }

        return $file_handle;
    }

    /**
     * Write contents to a file
     *
     * @param  resource $handle  File handle to write to
     * @param  string   $content Contents to write to the file
     * @return integer
     * @throws Exception
     * @throws Exception
     */
    public function write( $handle, $content ) {
        $write_result = @fwrite( $handle, $content );
        if( $write_result === false ) {
            if( ( $meta = \stream_get_meta_data( $handle ) ) ) {
                //$this->returnException(sprintf(__('Unable to write to: %s', 'wp-staging'), $meta['uri']));
                throw new \Exception( sprintf( __( 'Unable to write to: %s', 'wp-staging' ), $meta['uri'] ) );
            }
        } elseif( $write_result !== strlen( $content ) ) {
            //$this->returnException(__('Out of disk space.', 'wp-staging'));
            throw new \Exception( __( 'Out of disk space.', 'wp-staging' ) );
        }

        return $write_result;
    }

    /**
     * Execute the Current Step
     * Returns false when over threshold limits are hit or when the job is done, true otherwise
     * @return bool
     */
    protected function execute() {

        // No job left to execute
        if( $this->isFinished() ) {
            $this->prepareResponse( true, false );
            return false;
        }


//        if ($this->options->currentStep == 0) {
//            $this->getStagingWpRootFiles();
//            $this->prepareResponse(false, true);
//            return false;
//        }

        if( $this->options->currentStep == 0 ) {
            $this->getStagingPlugins();
            $this->prepareResponse( false, true );
            return false;
        }
        if( $this->options->currentStep == 1 ) {
            $this->getStagingThemes();
            $this->prepareResponse( false, true );
            return false;
        }
        if( $this->options->currentStep == 2 ) {
            $this->getStagingUploads();
            $this->prepareResponse( false, true );
            return false;
        }

        if( isset( $this->options->extraDirectories[$this->options->currentStep - $this->total] ) ) {
            $this->getExtraFiles( $this->options->extraDirectories[$this->options->currentStep - $this->total] );
            $this->prepareResponse( false, true );
            return false;
        }

//        if ($this->options->currentStep == 3) {
//            $this->getStagingWpAdminFiles();
//            $this->prepareResponse(false, true);
//            return false;
//        }
        // Not finished - Prepare response
        $this->prepareResponse( false, true );
        return true;
    }

    /**
     * Checks Whether There is Any Job to Execute or Not
     * @return bool
     */
    protected function isFinished() {
        if( $this->options->currentStep > $this->options->totalSteps ) {
            return true;
        }

        return false;
    }

    /**
     * Save files
     * @return bool
     */
    protected function saveProgress() {
        return $this->saveOptions();
    }

    /**
     * Get files
     * @return void
     */
    protected function getFiles() {
        $fileName = $this->cache->getCacheDir() . "files_to_copy." . $this->cache->getCacheExtension();

        if( ($this->files = @file_get_contents( $fileName )) === false ) {
            $this->files = [];
            return;
        }

        $this->files = explode( PHP_EOL, $this->files );
    }

    /**
     * Replace forward slash with current directory separator
     * Windows Compatibility Fix
     * @param string $path Path
     *
     * @return string
     */
    private function sanitizeDirectorySeparator( $path ) {
        $string = str_replace( "/", "\\", $path );
        return str_replace( '\\\\', '\\', $string );
    }

    /**
     * Check if directory is excluded from scanning
     * @param string $directory
     * @return bool
     */
    protected function isDirectoryExcluded( $directory ) {
        $directory = $this->sanitizeDirectorySeparator( $directory );
        foreach ( $this->options->excludedDirectories as $excludedDirectory ) {
            $excludedDirectory = $this->sanitizeDirectorySeparator( $excludedDirectory );
            if( strpos( trailingslashit( $directory ), trailingslashit( $excludedDirectory ) ) === 0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get relative path to WP media folder for staging site e.g. /wp-content/uploads
     *
     * @return string
     */
    protected function getStagingUploadFolder() {
        $uploads    = wp_upload_dir();
        //$relBaseDir = str_replace( \WPStaging\Core\WPStaging::getWPpath(), '', $uploads['basedir'] );
        $relBaseDir = str_replace( \WPStaging\Core\WPStaging::getWPpath(), '', $uploads['basedir'] );
        $path       = str_replace( '/sites/' . get_current_blog_id(), '', $relBaseDir );
        return $path;
    }

    /**
     * Get relative path to WP media upload folder for live site e.g. /wp-content/uploads/sites/7
     *
     * @return string
     */
    protected function getLiveUploadFolder() {
        $uploads    = wp_upload_dir();
        $relBaseDir = str_replace( \WPStaging\Core\WPStaging::getWPpath(), '', $uploads['basedir'] );
        return $relBaseDir;
    }

}
