<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;

class ConfirmDelete extends AbstractTemplateComponent
{

    /** @var SnapshotRepository  */
    private $snapshotRepository;

    public function __construct(SnapshotRepository $snapshotRepository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->snapshotRepository = $snapshotRepository;
    }

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $id = isset($_POST['id'])? sanitize_text_field($_POST['id']) : '';
        $snapshot = $this->snapshotRepository->find($id);
        if (!$snapshot) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Snapshot %s not found.', 'wp-staging'), $id),
                ]);
        }

        if ($snapshot->getType() === Snapshot::TYPE_DATABASE) {
            $this->renderDatabase($snapshot);
            return;
        }

        $this->renderSite($snapshot);
    }

    private function renderDatabase(Snapshot $snapshot)
    {
        $tables = (new TableService)->findTableStatusStartsWith($snapshot->getId());
        if (!$tables || $tables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(
                    __('Database tables for snapshot %1$s not found. You can still <a href="#" id="wpstg-snapshot-force-delete" data-id="%1$s">delete the listed snapshot entry</a>.', 'wp-staging'),
                    $snapshot->getId()
                ),
            ]);
        }

        $result = $this->templateEngine->render(
            'Pro/Snapshot/Database/template/confirm-delete.php',
            [
                'snapshot' => $snapshot,
                'tables' => $tables,
            ]
        );
        wp_send_json($result);
    }

    private function renderSite(Snapshot $snapshot)
    {
        $result = $this->templateEngine->render(
            'Pro/Snapshot/Site/template/confirm-delete.php',
            [
                'snapshot' => $snapshot,
            ]
        );
        wp_send_json($result);
    }
}
