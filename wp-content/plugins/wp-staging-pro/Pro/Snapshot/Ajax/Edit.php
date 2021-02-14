<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Snapshot\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Snapshot\Database\Command\Dto\SnapshotDto;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Pro\Snapshot\Repository\SnapshotRepository;
use WPStaging\Framework\Utils\Strings;

class Edit extends AbstractTemplateComponent
{
    /** @var SnapshotRepository */
    private $repository;

    public function __construct(SnapshotRepository $repository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->repository = $repository;
    }

    public function render()
    {
        if ( ! $this->canRenderAjax()) {
            return;
        }

        $id = sanitize_text_field(isset($_POST['id']) ? $_POST['id'] : '');
        $name = sanitize_text_field(isset($_POST['name']) ? $_POST['name'] : '');
        $notes = (new Strings)->sanitizeTextareaField(isset($_POST['notes']) ? $_POST['notes'] : '');

        $snapshots = $this->repository->findAll();
        if (!$snapshots) {
            wp_send_json([
                'error' => true,
                'message' => __('No snapshots exist in the system', 'wp-staging'),
            ]);
            return;
        }

        /** @var Snapshot|null $snapshot */
        $snapshot = $snapshots->findById($id);
        if (!$snapshot) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Snapshot ID: %s not found', 'wp-staging'), $id),
            ]);
            return;
        }

        $snapshot->setName($name?: SnapshotDto::SNAPSHOT_DEFAULT_NAME);
        $snapshot->setNotes($notes?: null);

        if (!$this->repository->save($snapshots)) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Failed to update snapshot ID: %s', 'wp-staging'), $id),
            ]);
            return;
        }

        wp_send_json(true);
    }
}
