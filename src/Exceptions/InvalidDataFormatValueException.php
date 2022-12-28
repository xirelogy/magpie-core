<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid format in data with given value
 */
class InvalidDataFormatValueException extends InvalidDataException
{
    /**
     * Constructor
     * @param mixed $target
     * @param string|null $reason
     * @param Throwable|null $previous
     */
    public function __construct(mixed $target, ?string $reason = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($target, $reason);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param mixed $target
     * @param string|null $reason
     * @return string
     */
    protected static function formatMessage(mixed $target, ?string $reason) : string
    {
        $defaultMessage = _l('Invalid data format');

        if ($reason !== null) {
            return _format_safe(_l('Invalid data format {{0}}: {{1}}'), stringOf($target), $reason) ?? $defaultMessage;
        } else {
            return _format_safe(_l('Invalid data format {{0}}'), stringOf($target)) ?? $defaultMessage;
        }
    }
}