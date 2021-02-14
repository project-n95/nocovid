<?php

// TODO PHP7.x; declare(strict_types=1);

namespace WPStaging\Framework\Utils;

// TODO PHP7.1; constant visibility
class WpDefaultDirectories
{
    const WP_ADMIN = 'wp-admin';
    const WP_INCLUDES = 'wp-includes';
    const WP_CONTENT = 'wp-content';
    const SITES = 'sites';
    const MULTI_OLD_UPLOADS_DIR = 'blogs.dir';
    const MULTI_UPLOADS_DIR = 'sites';

    /**
     * @var Strings
     */
    private $strUtils;

    public function __construct()
    {
        // @todo inject using DI
        $this->strUtils = new Strings();
    }

    /**
     * Get path to the uploads folder, relatively to the wp root folder.
     * Allows custom uploads folders.
     * For instance, returned strings can be:
     *  `
     * `custom-upload-folder`
     * `wp-content/uploads`
     * `wp-content/uploads/sites/2`
     *
     * Result will not have any appending or prepending slashes! Directory separator will be forward slash always for Microsoft IIS compatibility
     *
     * @return string
     */
    public function getRelativeUploadPath()
    {
        $relPath = str_replace($this->strUtils->sanitizeDirectorySeparator(ABSPATH), null, $this->getUploadPath());

        return trim($relPath, '/');
    }

    /*
     * Get the absolute path of upload directory
     * @return string
     */
    public function getUploadPath($refreshCache = false)
    {
        // Get upload directory information. Default is ABSPATH . 'wp-content/uploads'
        // Could have been customized by populating the db option upload_path or the constant UPLOADS in wp-config
        // If both are defined WordPress will uses the value of the UPLOADS constant
        // First two parameters in wp_upload_dir are default parameter and last parameter is to refresh the cache
        // Setting the 3rd and last parameter to true will refresh the cache return the latest value. Set to true for tests
        $uploads = wp_upload_dir(null, true, $refreshCache);

        // Adding slashes at before and end of absolute path to WordPress uploads directory
        $uploadsAbsPath = trailingslashit($uploads['basedir']);

        return $this->strUtils->sanitizeDirectorySeparator($uploadsAbsPath);
    }

    /**
     * Get the relative path of wp content directory
     * @return string
     */
    public function getRelativeWpContentPath()
    {
        $relPath = str_replace($this->strUtils->sanitizeDirectorySeparator(ABSPATH), null, WP_CONTENT_DIR);

        return trim($relPath, '/');
    }

    /**
     * Get the relative path of plugins directory
     * @return string
     */
    public function getRelativePluginPath()
    {
        $relPath = str_replace($this->strUtils->sanitizeDirectorySeparator(ABSPATH), null, WP_PLUGIN_DIR);

        return trim($relPath, '/');
    }

    /**
     * Get the relative path of themes directory
     * @return string
     */
    public function getRelativeThemePath()
    {
        $relWpContentPath = $this->getRelativeWpContentPath();
        $relPath = $relWpContentPath . "/" . "themes";
        return $this->strUtils->sanitizeDirectorySeparator($relPath);
    }
}
