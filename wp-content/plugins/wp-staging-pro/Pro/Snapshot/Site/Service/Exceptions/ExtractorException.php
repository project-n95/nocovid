<?php

namespace WPStaging\Pro\Snapshot\Site\Service\Exceptions;

class ExtractorException extends \Exception
{
    public static function fileAlreadyExists($filePath)
    {
        return new self(sprintf(
            __('Failed to write to file %s because a file with the same path already existed.', 'wp-staging'),
            $filePath
        ));
    }
}
