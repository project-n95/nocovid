<?php

namespace WPStaging\Pro\Snapshot\Site\Ajax;

use Exception;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Filesystem\File;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\TemplateEngine\TemplateEngine;

class Upload extends AbstractTemplateComponent
{
    /** @var Directory */
    private $directory;

    public function __construct(Directory $directory, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->directory = $directory;
    }

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $fullPath = $this->directory->getPluginUploadsDirectory() . $_POST['filename'];
        $fullPath = (new Filesystem)->safePath($fullPath);
        if (!$fullPath) {
            wp_send_json_error(sprintf('Directory %s does not exist', dirname($fullPath)));
            return;
        }

        $data = $this->findChunk();
        if (!$data) {
            wp_send_json_error('Invalid file data');
        }

        try {
            $file = $this->getFile($fullPath);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
            return;
        }

        if (!$file->flock(LOCK_NB|LOCK_EX)) {
            wp_send_json_error('Failed to lock file ' . $fullPath);
            return;
        }

        $writtenBytes = $file->fwrite($data);
        $file->flock(LOCK_UN);
        if (!$writtenBytes) {
            wp_send_json_error('Failed to write to ' . $fullPath);
            return;
        }

        wp_send_json_success();
    }

    private function getFile($path)
    {
        $fs = new Filesystem;
        if (!empty($_POST['reset']) && $_POST['reset'] === '1' && $fs->exists($path)) {
            $fs->delete($path);
        }

        return (new File($path, File::MODE_APPEND));
    }

    private function findChunk()
    {
        $data = explode(';base64,', $_POST['data']);
        if (!$data || !isset($data[1])) {
            return null;
        }

        $data = base64_decode($data[1]);
        return $data ?: null;
    }
}
