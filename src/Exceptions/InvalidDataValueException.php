<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid data with given value
 */
class InvalidDataValueException extends InvalidDataException
{
    /**
     * Constructor
     * @param mixed $target
     * @param string|null $purpose
     * @param Throwable|null $previous
     */
    public function __construct(mixed $target, ?string $purpose = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($target, $purpose);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param mixed $target
     * @param string|null $purpose
     * @return string
     */
    protected static function formatMessage(mixed $target, ?string $purpose) : string
    {
        $defaultMessage = _l('Invalid data');

        if ($purpose !== null) {
            return _format_safe(_l('Invalid {{1}} data \'{{0}}\''), stringOf($target), $purpose) ?? $defaultMessage;
        } else {
            return _format_safe(_l('Invalid data \'{{0}}\''), stringOf($target)) ?? $defaultMessage;
        }
    }
}