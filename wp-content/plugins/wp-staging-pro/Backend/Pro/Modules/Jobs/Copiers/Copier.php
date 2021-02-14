<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Copiers;

use WPStaging\Framework\Filesystem\Filesystem;

/**
 * Class Copier
 *
 * Abstract class for copying Plugins and Themes.
 *
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
abstract class Copier
{
    const PREFIX_TEMP   = 'wpstg-tmp-';
    const PREFIX_BACKUP = 'wpstg-bak-';

    /** @var string The path to the themes directory. */
    protected $themesDir;

    /** @var string The path to the plugins directory. */
    protected $pluginsDir;

    /** @var array */
    protected $errors = [];

    /**
     * @var Filesystem The filesystem is private so that only the Copier can use it.
     *                 This allows the copier to run safety checks before passing the
     *                 commands to the Filesystem.
     */
    private $filesystem;

    public function __construct(Filesystem $fileSystem)
    {
        $this->filesystem = $fileSystem;
        $this->themesDir  = trailingslashit(get_theme_root());
        $this->pluginsDir = trailingslashit(WP_PLUGIN_DIR);
    }

    /** @return array */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Make sure we are renaming or removing only sub directories of
     * wp-content/plugins or wp-content/themes.
     *
     * @param string $path The full path to be renamed or removed.
     *
     * @return bool Whether given path is allowed to be renamed or removed.
     */
    protected function isAllowedToRenameOrRemove($path)
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            return false;
        }

        $isInPluginsFolder = strpos($realPath, $this->pluginsDir) === 0;
        $isInThemesFolder  = strpos($realPath, $this->themesDir) === 0;

        return $isInPluginsFolder || $isInThemesFolder;
    }

    /**
     * @param $fullPath
     *
     * @return bool Whether given path is a directory and is writable.
     */
    protected function isWritableDir($fullPath)
    {
        return $this->filesystem->isWritableDir($fullPath);
    }

    /**
     * @param string $fullPath
     */
    protected function rmDir($fullPath)
    {
        if ( ! $this->isAllowedToRenameOrRemove($fullPath)) {
            $this->errors[] = 'Trying to remove a file/folder that is outside the expected path: ' . $fullPath;

            return;
        }

        try {
            $this->filesystem->delete($fullPath);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();

            return;
        }
    }

    /**
     * @param $from string
     * @param $to   string
     *
     * @return bool Whether the rename was successful or not.
     */
    protected function rename($from, $to)
    {
        if ( ! $this->isAllowedToRenameOrRemove($from)) {
            $this->errors[] = 'Trying to rename a file/folder that is outside the expected path: ' . $from;

            return false;
        }

        return @rename($from, $to);
    }
}
