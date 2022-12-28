<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid format in data
 */
class InvalidDataFormatException extends InvalidDataException
{
    /**
     * Constructor
     * @param string|null $reason
     * @param Throwable|null $previous
     */
    public function __construct(?string $reason = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($reason);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string|null $reason
     * @return string
     */
    protected static function formatMessage(?string $reason) : string
    {
        $defaultMessage = _l('Invalid data format');

        if ($reason == null) return $defaultMessage;

        return _format_safe(_l('Invalid data format: {{0}}'), $reason) ?? $defaultMessage;
    }
}