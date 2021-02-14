<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Site\Ajax;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Site\Service\Compressor;

class FileInfo extends AbstractTemplateComponent
{
    /** @var Compressor */
    private $exporter;
    /**
     * @var Directory
     */
    private $directory;

    public function __construct(Compressor $exporter, Directory $directory, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->exporter = $exporter;
        $this->directory = $directory;
    }

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        // Replace & add ABSPATH back is in a way a security measure to not fiddle with other directories
        $path = $this->directory->getPluginUploadsDirectory();
        $file = $path . str_replace($path, null, $_POST['filePath']);
        $info = $this->exporter->findExportFileInfo($file);

        $result = $this->templateEngine->render(
            'Pro/Snapshot/Site/template/info.php',
            [
                'info' => $info,
            ]
        );

        wp_send_json($result);
    }
}
