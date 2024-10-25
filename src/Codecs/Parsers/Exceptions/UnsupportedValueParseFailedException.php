<?php

namespace Magpie\Codecs\Parsers\Exceptions;

use Magpie\Exceptions\ParseFailedException;
use Throwable;

/**
 * Given value is not supported
 */
class UnsupportedValueParseFailedException extends ParseFailedException
{
    /**
     * Constructor
     * @param mixed $value
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(mixed $value, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($value);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param mixed $value
     * @return string
     */
    private static function formatMessage(mixed $value) : string
    {
        try {
            $formattedValue = stringOf($value);
            return _format(_l('Value {{0}} is unsupported'), $formattedValue);
        } catch (Throwable) {
            return _l('Value unsupported');
        }
    }
}