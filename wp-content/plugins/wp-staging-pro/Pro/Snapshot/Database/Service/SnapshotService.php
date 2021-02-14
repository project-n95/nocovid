<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Snapshot\Database\Service;

use Exception;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Snapshot\Database\Command\CreateSnapshotCommand;
use WPStaging\Pro\Snapshot\Database\Command\DeleteSnapshotCommand;
use WPStaging\Pro\Snapshot\Database\Command\Dto\ExportDto;
use WPStaging\Pro\Snapshot\Database\Command\Dto\SnapshotDto;
use WPStaging\Pro\Snapshot\Database\Command\Exception\SnapshotCommandException;
use WPStaging\Pro\Snapshot\Database\Command\ExportSnapshotCommand;
use WPStaging\Pro\Snapshot\Entity\Snapshot;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Core\WPStaging;

class SnapshotService
{
    const EXPORT_DIR_NAME = 'snapshots/database';

    const PREFIX_AUTOMATIC = 'wpsa';
    const PREFIX_MANUAL = 'wpsm';
    const PREFIX_TMP = 'wpstgtmp';

    /** @var DatabaseHelper */
    private $databaseHelper;

    /** @var AdapterHelper */
    private $adapterHelper;

    /** @var Directory */
    private $directory;

    public function __construct(DatabaseHelper $databaseHelper, AdapterHelper $adapterHelper, Directory $directory)
    {
        $this->databaseHelper = $databaseHelper;
        $this->adapterHelper = $adapterHelper;
        $this->directory = $directory;
    }

    /**
     * @param SnapshotDto $dto
     * @return Snapshot|null
     */
    public function create(SnapshotDto $dto)
    {
        $command = new CreateSnapshotCommand($this);
        $command->setDto($dto);
        $command->execute();

        return $command->getSnapshot();
    }

    /**
     * @param string $prefix
     * @param bool $skipValidation
     */
    public function delete($prefix, $skipValidation = false)
    {
        $dto = new SnapshotDto;
        $dto->setTargetPrefix($prefix);

        $command = new DeleteSnapshotCommand($this);
        $command->setDto($dto);
        $command->setSkipValidation($skipValidation);

        try {
            $command->execute();
        } catch (SnapshotCommandException $e) {
            // TODO log?
            $this->getDatabaseHelper()->getRepository()->deleteById($prefix);
        }
    }

    /**
     * @param string|null $prefix
     *
     * @return string
     * @throws Exception
     * @throws NotCompatibleException
     */
    public function export($prefix = null)
    {
        if (!class_exists('PDO')) {
            throw new NotCompatibleException;
        }

        if ($prefix === null) {
            $prefix = $this->databaseHelper->getDatabase()->getPrefix();
        }

        $exportDirectory = trailingslashit(path_join($this->directory->getPluginUploadsDirectory(), self::EXPORT_DIR_NAME));
        $fs = new Filesystem;
        $fs->delete($exportDirectory);
        $fs->mkdir($exportDirectory, true);

        $dto = (new ExportDto)->hydrate([
            'prefix' => $prefix,
            'directory' => $exportDirectory,
            'format' => $this->provideExportFormat(),
            'version' => WPStaging::getVersion(),
        ]);

        $tableService = $this->databaseHelper->getTableService();
        $logger = $this->adapterHelper->getLogger();

        $command = new ExportSnapshotCommand($dto, $tableService, $logger);
        $command->execute();
        return $dto->getFullPath();
    }

    /**
     * @return DatabaseHelper
     */
    public function getDatabaseHelper()
    {
        return $this->databaseHelper;
    }

    /**
     * @return AdapterHelper
     */
    public function getAdapterHelper()
    {
        return $this->adapterHelper;
    }

    /**
     * @return string
     */
    private function provideExportFormat()
    {
        if (!function_exists('gzwrite')) {
            return ExportSnapshotCommand::FORMAT_SQL;
        }
        return ExportSnapshotCommand::FORMAT_GZIP;
    }
}
