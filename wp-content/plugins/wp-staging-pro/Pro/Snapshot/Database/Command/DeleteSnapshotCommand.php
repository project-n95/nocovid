<?php

namespace WPStaging\Pro\Snapshot\Database\Command;

use WPStaging\Pro\Snapshot\Database\Command\Exception\SnapshotCommandException;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Snapshot\Database\Service\SnapshotService;

class DeleteSnapshotCommand extends AbstractSnapshotCommand
{
    /** @var bool */
    private $skipValidation;

    /**
     * @param bool $skipValidation
     */
    public function setSkipValidation($skipValidation)
    {
        $this->skipValidation = $skipValidation;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function execute()
    {
        $this->validateSnapshot();

        $tables = (new TableService)->findTableStatusStartsWith($this->dto->getTargetPrefix());

        if (!$tables || $tables->count() < 1) {
            throw new SnapshotCommandException('Delete backup tables do not exist: ' . $this->dto->getTargetPrefix());
        }

        $this->database->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach($tables as $table) {
            $this->database->exec('DROP TABLE IF EXISTS ' . $table->getName());
        }
        $this->database->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->saveSnapshots();
    }

    protected function saveSnapshots()
    {
        $this->service
            ->getDatabaseHelper()
            ->getRepository()
            ->deleteById($this->dto->getTargetPrefix())
        ;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function validateSnapshot()
    {
        parent::validateSnapshot();

        if ($this->skipValidation) {
            return;
        }

        if (!$this->snapshots->doesIncludeId($this->dto->getTargetPrefix())) {
            throw new SnapshotCommandException(
                'DeleteSnapshot prefix does not exist: ' . $this->dto->getTargetPrefix()
            );
        }
    }
}
