<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to given path being invalid
 */
class InvalidPathException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string $path
     * @param Throwable|null $previous
     */
    public function __construct(string $path, ?Throwable $previous = null)
    {
        $message = static::formatMessage($path);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $path
     * @return string
     */
    protected static function formatMessage(string $path) : string
    {
        return _format_l('Invalid path', 'Invalid path: {{0}}', $path);
    }
}