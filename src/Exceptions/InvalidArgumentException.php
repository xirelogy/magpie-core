<?php

namespace Magpie\Exceptions;

use Exception;
use Throwable;

/**
 * Exception due to invalid argument (most likely during parsing)
 */
class InvalidArgumentException extends ArgumentException
{
    /**
     * Constructor
     * @param string|null $argName
     * @param Exception|string|null $reason
     * @param Throwable|null $previous
     */
    public function __construct(?string $argName = null, Exception|string|null $reason = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($argName, $reason);

        parent::__construct($argName, $message, $previous);
    }


    /**
     * Format message
     * @param string|null $argName
     * @param Exception|string|null $reason
     * @return string
     */
    protected static function formatMessage(?string $argName, Exception|string|null $reason) : string
    {
        $defaultMessage = _l('Invalid argument');

        // When reason provided, show the reason
        if ($reason instanceof Exception) {
            $reason = $reason->getMessage();
        }

        if (!empty($reason)) {
            if (!empty($argName)) {
                return _format_safe(_l('Invalid argument \'{{0}}\': {{1}}'), $argName, $reason) ?? $defaultMessage;
            } else {
                return _format_safe(_l('Invalid argument: {{0}}'), $reason) ?? $defaultMessage;
            }
        }

        if (!empty($argName)) {
            return _format_safe(_l('Invalid argument \'{{0}}\''), $argName) ?? $defaultMessage;
        } else {
            return $defaultMessage;
        }
    }
}