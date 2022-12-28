<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to file not found
 */
class FileNotFoundException extends SafetyCommonException
{
    /**
     * @var string The path causing the exception
     */
    public readonly string $path;


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
        return _format_safe(_l('File not found: {{0}}'), $path) ?? _l('File not found');
    }
}