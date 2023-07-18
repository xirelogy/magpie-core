<?php

namespace Magpie\Codecs\Parsers\Exceptions;

use Magpie\Exceptions\ParseFailedException;
use Throwable;

/**
 * Must be one of the allowed values
 */
class MustBeValuesParseFailedException extends ParseFailedException
{
    /**
     * Constructor
     * @param array $values
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(array $values, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($values);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param array $values
     * @return string
     */
    private static function formatMessage(array $values) : string
    {
        try {
            $formattedValues = [];
            foreach ($values as $value) {
                $formattedValues[] = stringOf($value);
            }

            return _format(_l('Must be one of the allowed values: {{0}}'), implode(_l(', '), $formattedValues));
        } catch (Throwable) {
            return _l('Must be one of the allowed values');
        }
    }
}