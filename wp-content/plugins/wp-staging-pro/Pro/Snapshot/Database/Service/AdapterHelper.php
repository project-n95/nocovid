<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints

namespace WPStaging\Pro\Snapshot\Database\Service;

use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Core\Utils\Logger;

class AdapterHelper
{
    /** @var Directory */
    private $directory;

    /** @var Logger */
    private $logger;

    public function __construct(Directory $directory, Logger $logger)
    {
        $this->directory = $directory;
        $this->logger = $logger;
    }

    /**
     * @return Directory
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
