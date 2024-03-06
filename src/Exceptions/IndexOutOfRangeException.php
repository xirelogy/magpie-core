<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to the given index is out of allowable range
 */
class IndexOutOfRangeException extends OutOfRangeException
{
    /**
     * Constructor
     * @param int|null $index
     * @param Throwable|null $previous
     */
    public function __construct(?int $index = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($index);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param int|null $index
     * @return string
     */
    protected static function formatMessage(?int $index) : string
    {
        if ($index === null) return _l('Index out of range');

        return _format_safe(_l('Index {{0}} is out of range'), $index) ?? _l('Index out of range');
    }
}