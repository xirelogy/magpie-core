<?php

namespace Magpie\Exceptions;

use Magpie\Locales\Concepts\Localizable;
use Throwable;

/**
 * Exception due to missing argument
 */
class MissingArgumentException extends ArgumentException
{
    /**
     * Constructor
     * @param string|null $argName
     * @param Localizable|string|null $argType
     * @param Throwable|null $previous
     */
    public function __construct(?string $argName = null, Localizable|string|null $argType = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($argName, $argType);

        parent::__construct($argName, $message, $previous);
    }


    /**
     * Format message
     * @param string|null $argName
     * @param Localizable|string|null $argType
     * @return string
     */
    protected static function formatMessage(?string $argName, Localizable|string|null $argType) : string
    {
        try {
            $argType = $argType ?? _l('argument');
            if (empty($argName)) return _format(_l('Missing {{0}}'), $argType);
            return _format(_l('Missing {{0}} \'{{1}}\''), $argType, $argName);
        } catch (Throwable) {
            return _l('Missing argument');
        }
    }
}