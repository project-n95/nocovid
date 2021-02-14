<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Database\Ajax;

use Exception;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Database\Service\NotCompatibleException;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;

class Export extends AbstractTemplateComponent
{
    /** @var SnapshotService */
    private $service;

    public function __construct(SnapshotService $service, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->service = $service;
    }

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;

        try {
            $result = $this->pathToUrl($this->service->export($id));

            // Trigger __destruct
            unset($this->service);

            wp_send_json_success($result);
        } catch (NotCompatibleException $e) {

            // Trigger __destruct
            unset($this->service);

            wp_send_json([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {

            // Trigger __destruct
            unset($this->service);
            
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Failed to export the backup tables %s', 'wp-staging'), $id),
            ]);
        }
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    private function pathToUrl($dir)
    {
        $relativePath = str_replace(ABSPATH, null, $dir);
        return site_url() . '/' . $relativePath;
    }
}
