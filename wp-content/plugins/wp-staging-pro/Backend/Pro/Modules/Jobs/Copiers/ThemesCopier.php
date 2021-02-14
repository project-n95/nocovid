<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Copiers;

/**
 * Class ThemesCopier
 *
 * Copies themes.
 *
 * @package WPStaging\Backend\Pro\Modules\Jobs\Copiers
 */
class ThemesCopier extends Copier
{
    /** @var string */
    private $themeName;

    /** @var string */
    private $basePath;

    /** @var string */
    private $tmpPath;

    /** @var string */
    private $backupPath;

    public function copy()
    {
        // Force WordPress to refresh it's list of themes
        search_theme_directories(true);
        $allThemes = array_keys(wp_get_themes());

        foreach ($allThemes as $theme) {
            if (strpos($theme, Copier::PREFIX_TEMP) === 0) {
                $this->themeName  = $this->getThemeName($theme);
                $this->basePath   = $this->themesDir . $this->themeName;
                $this->tmpPath    = $this->themesDir . Copier::PREFIX_TEMP . $this->themeName;
                $this->backupPath = $this->themesDir . Copier::PREFIX_BACKUP . $this->themeName;

                if ( ! $this->backupTheme()) {
                    $this->errors[] = 'Theme Copier: Skipping theme ' . $this->themeName . '. Please copy it manually from staging to live via FTP!';
                    $this->removeTmpTheme();
                    continue;
                }
                if ( ! $this->activateTmpTheme()) {
                    $this->errors[] = 'Theme Copier: Skipping theme ' . $this->themeName . ' Can not activate it. Please copy it manually from staging to live via FTP.';
                    $this->restoreBackupTheme();
                    continue;
                }
                if ( ! $this->removeBackupTheme()) {
                    $this->errors[] = 'Theme Copier: Can not remove backup theme: ' . $this->themeName . '. Please remove it manually via wp-admin > themes or via FTP.';
                    continue;
                }
            }
        }
    }

    /** @return string */
    private function getThemeName($tempTheme)
    {
        return str_replace(Copier::PREFIX_TEMP, '', $tempTheme);
    }

    /** @return bool */
    private function activateTmpTheme()
    {
        if ( ! $this->isWritableDir($this->tmpPath)) {
            $this->errors[] = 'Theme Copier: TMP theme Directory does not exist or is not writable: ' . $this->tmpPath;

            return false;
        }

        if ( ! $this->isWritableDir($this->tmpPath) || ! $this->rename($this->tmpPath, $this->basePath)) {
            $this->errors[] = 'Theme Copier: Can not activate theme: ' . Copier::PREFIX_TEMP . $this->themeName . ' to ' . $this->themeName;

            return false;
        }

        return true;
    }

    /**
     * @param string wpstg-bak-theme-dir
     *
     * @return boolean
     */
    private function backupTheme()
    {
        // Nothing to backup on prod site
        if ( ! is_dir($this->basePath)) {
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);
        }

        if ( ! $this->isWritableDir($this->basePath)) {
            $this->errors[] = 'Theme Copier: Can not backup theme: ' . $this->themeName . ' to ' . Copier::PREFIX_BACKUP . $this->themeName . ' Theme folder not writeable.';

            return false;
        }
        if ( ! $this->rename($this->basePath, $this->backupPath)) {
            $this->errors[] = 'Theme Copier: Can not rename theme: ' . $this->themeName . ' to ' . Copier::PREFIX_BACKUP . $this->themeName . ' Unknown error.';

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function restoreBackupTheme()
    {
        if ( ! $this->isWritableDir($this->backupPath)) {
            $this->errors[] = 'Theme Copier: Can not restore backup theme: ' . Copier::PREFIX_BACKUP . $this->themeName . ' ' . $this->backupPath . ' is not writeable.';

            return false;
        }
        if ( ! $this->rename($this->backupPath, $this->basePath)) {
            $this->errors[] = 'Theme Copier: Can not restore theme: ' . Copier::PREFIX_BACKUP . $this->themeName . 'Unknown error.';

            return false;
        }

        return true;
    }

    /**
     */
    private function removeTmpTheme()
    {
        if ($this->isWritableDir($this->tmpPath)) {
            $this->rmDir($this->tmpPath);

            return true;
        }
        $this->errors[] = 'Theme Copier: Can not remove temp theme: ' . Copier::PREFIX_TEMP . $this->themeName . ' Folder ' . $this->tmpPath . ' is not writeable. Remove it manually via FTP.';

        return false;
    }

    private function removeBackupTheme()
    {
        // No backup to delete on prod site
        if ( ! is_dir($this->backupPath)) {
            return true;
        }

        if ($this->isWritableDir($this->backupPath)) {
            $this->rmDir($this->backupPath);

            return true;
        }
        $this->errors[] = 'Theme Copier: Can not remove backup theme: ' . Copier::PREFIX_BACKUP . $this->themeName . ' Folder ' . $this->backupPath . ' is not writeable. Remove it manually via FTP.';

        return false;
    }
}
