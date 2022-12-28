<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to invalid JSON format in data
 */
class InvalidJsonDataFormatException extends InvalidDataFormatException
{
    public function __construct(?string $reason = null, ?Throwable $previous = null)
    {
        parent::__construct($reason, $previous);
    }


    /**
     * Format message
     * @param string|null $reason
     * @return string
     */
    protected static function formatMessage(?string $reason) : string
    {
        $defaultMessage = _l('Invalid JSON data format');

        if ($reason == null) return $defaultMessage;

        return _format_safe(_l('Invalid JSON data format: {{0}}'), $reason) ?? $defaultMessage;
    }
}