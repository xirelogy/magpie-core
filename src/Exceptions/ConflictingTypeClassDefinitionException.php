<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to conflicting type class definition
 */
class ConflictingTypeClassDefinitionException extends ConflictException
{
    /**
     * Constructor
     * @param string $typeClass
     * @param string $baseClassName
     * @param Throwable|null $previous
     */
    public function __construct(string $typeClass, string $baseClassName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($typeClass, $baseClassName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $typeClass
     * @param string $baseClassName
     * @return string
     */
    protected static function formatMessage(string $typeClass, string $baseClassName) : string
    {
        return _format_safe(_l('Type class \'{{0}}\' for \'{{1}}\' has conflicting definition'), $typeClass, $baseClassName)
            ?? _l('Type class had conflicting definition');
    }
}