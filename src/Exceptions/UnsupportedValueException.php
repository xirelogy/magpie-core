<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to unsupported value
 */
class UnsupportedValueException extends UnsupportedException
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
    protected static function formatMessage(mixed $target, ?string $purpose = null) : string
    {
        $defaultMessage = _l('Unsupported value');

        if ($purpose !== null) {
            return _format_safe(_l('Unsupported {{1}} value {{0}}'), stringOf($target), $purpose) ?? $defaultMessage;
        } else {
            return _format_safe(_l('Unsupported value {{0}}'), stringOf($target)) ?? $defaultMessage;
        }
    }
}