<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to an expected method in given host object not found
 */
class MethodNotFoundException extends SimplifiedCommonException
{
    /**
     * Constructor
     * @param object $host
     * @param string $methodName
     * @param Throwable|null $previous
     */
    public function __construct(object $host, string $methodName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($host, $methodName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param object $host
     * @param string $methodName
     * @return string
     */
    protected static function formatMessage(object $host, string $methodName) : string
    {
        return _format_safe(_l('Method \'{{1}}\' not found in object of class \'{{0}}\''), $host::class, $methodName)
            ?? _l('Method not found in object');
    }
}