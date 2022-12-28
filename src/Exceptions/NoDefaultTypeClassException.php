<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to default type class is not defined for given base class
 */
class NoDefaultTypeClassException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string $baseClassName
     * @param Throwable|null $previous
     */
    public function __construct(string $baseClassName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($baseClassName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $baseClassName
     * @return string
     */
    protected static function formatMessage(string $baseClassName) : string
    {
        return _format_safe(_l('There is no default type class for \'{{0}}\''), $baseClassName)
            ?? _l('There is no default type class');
    }
}