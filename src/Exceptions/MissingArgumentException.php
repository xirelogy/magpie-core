<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to missing argument
 */
class MissingArgumentException extends ArgumentException
{
    /**
     * Constructor
     * @param string|null $argName
     * @param Throwable|null $previous
     */
    public function __construct(?string $argName = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($argName);

        parent::__construct($argName, $message, $previous);
    }


    /**
     * Format message
     * @param string|null $argName
     * @return string
     */
    protected static function formatMessage(?string $argName) : string
    {
        if (empty($argName)) return _l('Missing argument');

        return _format_safe(_l('Missing argument \'{{0}}\''), $argName) ?? _l('Missing argument');
    }
}