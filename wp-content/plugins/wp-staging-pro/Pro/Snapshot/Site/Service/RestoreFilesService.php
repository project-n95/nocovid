<?php

namespace WPStaging\Pro\Snapshot\Site\Service;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Vendor\Symfony\Component\Finder\Finder;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;

// TODO Dry
class RestoreFilesService
{

    const DISABLED_DIR_SUFFIX = '.disabled';
    const OPTIMIZER_MU_PLUGIN_FILE = 'wp-staging-optimizer.php';
    const WP_CONTENT_CACHE_DIR_NAME = 'cache';

    /** @var Directory */
    private $directory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Directory $directory, LoggerInterface $logger)
    {
        $this->directory = $directory;
        $this->logger = $logger;
    }

    public function deleteUploads(callable $shouldStop = null)
    {
        return (new Filesystem)
            ->setShouldStop($shouldStop)
            ->addNotPath($this->directory->getDomain())
            ->addNotPath($this->directory->getSlug())
            ->delete($this->directory->getUploadsDirectory())
        ;
    }

    public function restoreUploads($oldPath, callable $shouldStop = null)
    {
        return (new Filesystem)
            ->setPath($oldPath)
            ->setShouldStop($shouldStop)
            ->setLogger($this->logger)
            ->addNotPath($this->directory->getDomain())
            ->addNotPath($this->directory->getSlug())
            ->rename($this->directory->getUploadsDirectory())
        ;
    }

    public function deleteThemes($oldPath, callable $shouldStop = null)
    {
        $backupThemes = (new Finder)->in($oldPath)->depth(0)->directories();
        $dirThemes = trailingslashit(get_theme_root());
        $fs = (new Filesystem)->setShouldStop($shouldStop);
        foreach ($backupThemes as $backupTheme) {
            $dirTheme = $dirThemes . $backupTheme->getRelativePathname();
            if ($fs->exists($dirTheme) && !$fs->delete($dirTheme)) {
                return false;
            }
        }
        return true;
    }

    public function restoreThemes($oldPath, callable $shouldStop = null)
    {
        return (new Filesystem)
            ->setPath($oldPath)
            ->setShouldStop($shouldStop)
            ->setLogger($this->logger)
            ->rename(trailingslashit(get_theme_root()))
        ;
    }

    public function deleteWpContent($oldPath, callable $shouldStop = null)
    {
        // Don't delete dirs: cache, themes, plugins, mu_plugins, uploads and upgrades
        $backupDirs = (new Finder)
            ->in($oldPath)
            ->depth(0)
            ->exclude(self::WP_CONTENT_CACHE_DIR_NAME)
            ->exclude(trim(str_replace(WP_CONTENT_DIR, null, trailingslashit(get_theme_root())), '/'))
            ->exclude(trim(str_replace(WP_CONTENT_DIR, null, WP_PLUGIN_DIR), '/'))
            ->exclude(trim(str_replace(WP_CONTENT_DIR, null, WPMU_PLUGIN_DIR), '/'))
            ->exclude(trim(str_replace(WP_CONTENT_DIR, null, $this->directory->getUploadsDirectory()), '/'))
            ->exclude(trim(str_replace(WP_CONTENT_DIR, null, 'upgrade')), '/')
            ->directories()
        ;

        $destinationRoot = trailingslashit(WP_CONTENT_DIR);
        $fs = (new Filesystem)->setShouldStop($shouldStop);
        foreach ($backupDirs as $backupDir) {
            $destination = $destinationRoot . $backupDir->getRelativePathname();
            if ($fs->exists($destination) && !$fs->delete($destination)) {
                $this->logger->warning('Failed to delete ' . $destination);
            }
        }
        return true;
    }

    public function restoreWpContent($oldPath, array $notPaths = [], callable $shouldStop = null)
    {
        $notPaths = array_map(static function($path) use ($oldPath) {
            return trim(str_replace($oldPath, null, $path), '/\\');
        }, $notPaths);
        $notPaths[] = self::WP_CONTENT_CACHE_DIR_NAME;

        return (new Filesystem)
            ->setPath($oldPath)
            ->setShouldStop($shouldStop)
            ->setLogger($this->logger)
            ->setNotPath($notPaths)
            ->rename(trailingslashit(WP_CONTENT_DIR))
        ;
    }

    public function restorePlugins($oldPath)
    {
        return $this->restoreBasePlugin($oldPath, WP_PLUGIN_DIR);
    }

    public function restoreMuPlugins($target)
    {
        return $this->restoreBasePlugin($target, WPMU_PLUGIN_DIR);
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * This method will ignore `fixture` on dev environments
     * Not ignoring `fixture` or plugin directory (like `wp-staging` or `wp-staging-pro`) generate entry on
     * error log on dev environment due to `fixture` directory being controlled by docker.
     * Do not remove that piece of code or ajax calls will fail if docker is controlling these directories.
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     */
    private function restoreBasePlugin($oldPath, $newPath)
    {
        // Disable all plugins
        $this->disablePlugins($newPath);

        // Move snapshot plugins && Get current plugins list (only first level dir names)
        $backupPlugins = (new Finder)
            ->in($oldPath)
            ->depth(0)
            /*
             * It's important to add untrailingslashit here because Symfony Finder treats
             * strings with equal delimiters as Regex for the following methods:
             * path, notPath, name, notName, contains, notContains.
             *
             * /var/www/single/wp-content/plugins/wp-staging-dev/ (Regex)
             * /var/www/single/wp-content/plugins/wp-staging-dev (String)
             *
             * We want the string version.
             */
            ->notPath(untrailingslashit(WPSTG_PLUGIN_DIR))
            ->notName(self::OPTIMIZER_MU_PLUGIN_FILE)
            ->directories()
        ;

        if (isset($_ENV['APP']) && $_ENV['APP'] === 'dev') {
            $backupPlugins->notPath('fixture');
        }

        $activePluginDirNames = [];
        $basePath = trailingslashit($newPath);
        $fs = (new Filesystem)->setLogger($this->logger);
        foreach ($backupPlugins as $backupPlugin) {
            $activePluginDirNames[] = $backupPlugin->getRelativePathname();
            $fs->renameDirect($backupPlugin->getPathname(), $basePath . $backupPlugin->getRelativePathname());
        }

        // Get old plugins and exclude the currently existing directories and move them to active plugins dir
        $disabledNewPath = trailingslashit($newPath . self::DISABLED_DIR_SUFFIX);
        $disabledPlugins = (new Finder)->in($disabledNewPath)->depth(0)->directories()->exclude($activePluginDirNames);
        foreach ($disabledPlugins as $disabledPlugin) {
            $fs->renameDirect($disabledPlugin->getPathname(), str_replace($disabledNewPath, $basePath, $disabledPlugin->getRealPath()));
        }

        $fs = new Filesystem;
        // Delete the disabled plugins dir to make sure we don't have this dir when it runs next time.
        if (!$fs->delete($disabledNewPath)) {
            $this->logger->warning(sprintf('Can not delete directory %s', $disabledNewPath));
            // return true to make sure that this does not repeat infinite times if there is an error
            return true;
        }

        return $fs->delete($oldPath);
    }

    /**
     * @param string plugin absolute path
     */
    private function disablePlugins($path)
    {
        $iterator = (new Finder)
            ->in($path)
            ->depth(0)
            /* @see \WPStaging\Pro\Snapshot\Site\Service\RestoreFilesService::restoreBasePlugin For untrailingslashit notes. */
            ->notPath(untrailingslashit(WPSTG_PLUGIN_DIR))
            ->notName(self::OPTIMIZER_MU_PLUGIN_FILE)
            ->directories()
        ;

        if (isset($_ENV['APP']) && $_ENV['APP'] === 'dev') {
            $iterator->notPath('fixture');
        }

        $fs = (new Filesystem)->setLogger($this->logger);
        $pathDisabled = $fs->mkdir($path . self::DISABLED_DIR_SUFFIX);
        foreach ($iterator as $plugin) {
            if (!$plugin->isReadable()) {
                $this->logger->warning(sprintf('Plugin directory %s is not readable', $plugin->getPathname()));
                continue;
            }
            $fs->renameDirect($plugin->getPathname(), $pathDisabled . $plugin->getRelativePathname());
        }
    }
}
