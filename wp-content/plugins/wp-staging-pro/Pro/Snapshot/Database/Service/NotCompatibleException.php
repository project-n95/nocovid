<?php

namespace WPStaging\Pro\Snapshot\Database\Service;

use RuntimeException;

class NotCompatibleException extends RuntimeException
{
    public function __construct($message = null)
    {
        if (!$message) {
            $message = __('PHP PDO extension not found. ', 'wp-staging');
        }

        parent::__construct($message);
    }
}