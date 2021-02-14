<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Database\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Collection\Collection;

class ConfirmRestore extends AbstractTemplateComponent
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

        $tblService = new TableService;

        $prodTables = $tblService->findTableStatusStartsWith();
        if (!$prodTables || $prodTables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => __('Production (live) database tables not found.', 'wp-staging'),
            ]);
        }

        $snapshotTables = $tblService->findTableStatusStartsWith($id);
        if (!$snapshotTables || $snapshotTables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Database tables for snapshot %s not found.', 'wp-staging'), $id),
            ]);
        }

        // TODO RPoC; perhaps just check; isNotSame
        $prefixProd = (new Database)->getPrefix();
        $result = $this->templateEngine->render(
            'Pro/Snapshot/Database/template/confirm-restore.php',
            [
                'snapshot' => $snapshot,
                'snapshotTables' => $snapshotTables,
                'prodTables' => $prodTables,
                'isTableChanged' => static function(TableDto $table, Collection $oppositeCollection) use($id, $prefixProd) {
                    $tableName = str_replace([$id, $prefixProd], null, $table->getName());
                    /** @var TableDto $item */
                    foreach($oppositeCollection as $item) {
                        $itemName = str_replace([$id, $prefixProd], null, $item->getName());
                        if ($tableName !== $itemName) {
                            continue;
                        }

                        return $item->getSize() !== $table->getSize();
                    }
                    return false;
                },
            ]
        );
        wp_send_json($result);
    }
}
