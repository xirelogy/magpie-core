<?php

namespace Magpie\Exceptions;

use Exception;
use Magpie\Locales\Concepts\Localizable;
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
     * @param Localizable|string|null $argType
     * @param Throwable|null $previous
     */
    public function __construct(?string $argName = null, Exception|string|null $reason = null, Localizable|string|null $argType = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($argName, $reason, $argType);

        parent::__construct($argName, $message, $previous);
    }


    /**
     * Format message
     * @param string|null $argName
     * @param Exception|string|null $reason
     * @param Localizable|string|null $argType
     * @return string
     */
    protected static function formatMessage(?string $argName, Exception|string|null $reason, Localizable|string|null $argType) : string
    {
        $defaultMessage = _l('Invalid argument');

        try {
            $argType = $argType ?? _l('argument');

            // When reason provided, show the reason
            if ($reason instanceof Exception) {
                $reason = $reason->getMessage();
            }

            if (!empty($reason)) {
                if (!empty($argName)) {
                    return _format(_l('Invalid {{0}} \'{{1}}\': {{2}}'), $argType, $argName, $reason);
                } else {
                    return _format(_l('Invalid {{0}}: {{1}}'), $argType, $reason);
                }
            }

            if (!empty($argName)) {
                return _format(_l('Invalid {{0}} \'{{1}}\''), $argType, $argName);
            } else {
                return $defaultMessage;
            }
        } catch (Throwable) {
            return $defaultMessage;
        }
    }
}