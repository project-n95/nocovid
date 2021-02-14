<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Copiers;

/**
 * Class PluginsCopier
 *
 * Copies plugins.
 *
 * @package WPStaging\Backend\Pro\Modules\Jobs\Copiers
 */
class PluginsCopier extends Copier
{
    /** @var string */
    private $pluginName;

    /** @var string */
    private $basePath;

    /** @var string */
    private $tmpPath;

    /** @var string */
    private $backupPath;

    /**
     * @todo Make this compatible with single-file plugins.
     * @see \WPStaging\Backend\Pro\Modules\Jobs\ScanDirectories::getStagingPlugins
     */
    public function copy()
    {
        $allPlugins = array_keys(get_plugins());

        foreach ($allPlugins as $plugin) {
            if (strpos($plugin, Copier::PREFIX_TEMP) === 0) {
                $this->pluginName = $this->getPluginName($plugin);
                $this->basePath   = $this->pluginsDir . $this->pluginName;
                $this->tmpPath    = $this->pluginsDir . Copier::PREFIX_TEMP . $this->pluginName;
                $this->backupPath = $this->pluginsDir . Copier::PREFIX_BACKUP . $this->pluginName;

                if ( ! $this->backupPlugin()) {
                    $this->errors[] = 'Plugin Handler: Skipping plugin ' . $this->pluginName . '. Please copy it manually from staging to live via FTP!';
                    $this->removeTmpPlugin();
                    continue;
                }
                if ( ! $this->activateTmpPlugin()) {
                    $this->errors[] = 'Plugin Handler: Skipping plugin ' . $this->pluginName . ' Can not activate it. Please copy it manually from staging to live via FTP.';
                    $this->restoreBackupPlugin();
                    continue;
                }
                if ( ! $this->removeBackupPlugin()) {
                    $this->errors[] = 'Plugin Handler: Can not remove backup plugin: ' . $this->pluginName . '. Please remove it manually via wp-admin > plugins or via FTP.';
                    continue;
                }
            }
        }
    }

    /** @return string */
    private function getPluginName($plugin)
    {
        $pluginTmpName = dirname($plugin);

        return str_replace(Copier::PREFIX_TEMP, '', $pluginTmpName);
    }

    /** @return bool */
    private function activateTmpPlugin()
    {
        if ( ! $this->isWritableDir($this->tmpPath)) {
            $this->errors[] = 'Plugin Handler: TMP Plugin Directory does not exist or is not writable: ' . $this->tmpPath;

            return false;
        }

        if ( ! $this->isWritableDir($this->tmpPath) || ! $this->rename($this->tmpPath, $this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not activate plugin: ' . Copier::PREFIX_TEMP . $this->pluginName . ' to ' . $this->pluginName;

            return false;
        }

        return true;
    }

    /**
     * @param string wpstg-bak-plugin-dir/plugin.php
     *
     * @return boolean
     * @todo Allow user to delete all wpstg-bak plugins after pushing
     */
    private function backupPlugin()
    {
        // Nothing to backup on prod site
        if ( ! is_dir($this->basePath)) {
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);
        }

        if ( ! $this->isWritableDir($this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not backup plugin: ' . $this->pluginName . ' to ' . Copier::PREFIX_BACKUP . $this->pluginName . ' Plugin folder not writeable.';

            return false;
        }
        if ( ! $this->rename($this->basePath, $this->backupPath)) {
            $this->errors[] = 'Plugin Handler: Can not rename plugin: ' . $this->pluginName . ' to ' . Copier::PREFIX_BACKUP . $this->pluginName . ' Unknown error.';

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function restoreBackupPlugin()
    {
        if ( ! $this->isWritableDir($this->backupPath)) {
            $this->errors[] = 'Plugin Handler: Can not restore backup plugin: ' . Copier::PREFIX_BACKUP . $this->pluginName . ' ' . $this->backupPath . ' is not writeable.';

            return false;
        }
        if ( ! $this->rename($this->backupPath, $this->basePath)) {
            $this->errors[] = 'Plugin Handler: Can not restore plugin: ' . Copier::PREFIX_BACKUP . $this->pluginName . 'Unknown error.';

            return false;
        }

        return true;
    }

    /**
     */
    private function removeTmpPlugin()
    {
        if ($this->isWritableDir($this->tmpPath)) {
            $this->rmDir($this->tmpPath);

            return true;
        }
        $this->errors[] = 'Plugin Handler: Can not remove temp plugin: ' . Copier::PREFIX_TEMP . $this->pluginName . ' Folder ' . $this->tmpPath . ' is not writeable. Remove it manually via FTP.';

        return false;
    }

    /**
     * @param $pluginName string
     */
    private function removeBackupPlugin()
    {
        // No backup to delete on prod site
        if ( ! is_dir($this->backupPath)) {
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);

            return true;
        }
        $this->errors[] = 'Plugin Handler: Can not remove backup plugin: ' . Copier::PREFIX_BACKUP . $this->pluginName . ' Folder ' . $this->backupPath . ' is not writeable. Remove it manually via FTP.';

        return false;
    }
}
