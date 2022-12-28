<?php

namespace Magpie\Exceptions;

use Magpie\General\Tags\ClassNameString;
use Throwable;

/**
 * Exception due to target is not of the type that was expected
 */
class NotOfTypeException extends SafetyCommonException
{
    /**
     * Constructor
     * @param mixed $target Target that causes the exception
     * @param string $type Type that was expected
     * @param Throwable|null $previous
     */
    public function __construct(mixed $target, string $type, ?Throwable $previous = null)
    {
        $message = static::formatMessage($target, $type);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param mixed $target
     * @param string $type
     * @return string
     */
    protected static function formatMessage(mixed $target, string $type) : string
    {
        return _format_safe(_l('{{0}} is not of expected type \'{{1}}\''), static::formatTarget($target), $type)
            ?? _l('Target is not of expected type');
    }


    /**
     * Format target
     * @param mixed $target
     * @return string
     */
    protected static function formatTarget(mixed $target) : string
    {
        if ($target instanceof ClassNameString) {
            return _format_safe(_l('Class \'{{0}}\''), $target->className) ?? $target->className;
        }

        return stringOf($target);
    }
}