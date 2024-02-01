<?php

namespace Magpie\Exceptions;

use Magpie\Locales\Concepts\Localizable;
use Throwable;

/**
 * Exception due to file operation failure
 */
class FileOperationFailedException extends OperationFailedException
{
    /**
     * Constructor
     * @param string $path
     * @param string|null $ops
     * @param Throwable|null $previous
     */
    public function __construct(string $path, ?string $ops = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($path, $ops);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $path
     * @param string|null $ops
     * @return string
     */
    protected static function formatMessage(string $path, ?string $ops) : string
    {
        if ($ops !== null) {
            return _format_safe(_l('File {{0}} operation failed: {{1}}'), $ops, $path) ?? _l('File operation failed');
        } else {
            return _format_safe(_l('File operation failed: {{0}}'), $path) ?? _l('File operation failed');
        }
    }


    /**
     * Common operation: read
     * @return Localizable
     */
    public static function readOperation() : Localizable
    {
        return _l('read');
    }


    /**
     * Common operation: write
     * @return Localizable
     */
    public static function writeOperation() : Localizable
    {
        return _l('write');
    }
}