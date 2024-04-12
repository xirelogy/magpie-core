<?php

namespace Magpie\Exceptions;

use Magpie\General\Sugars\Quote;
use Throwable;

/**
 * Exception due to the given index is not found
 * @note This is applicable for collections where indices are not consecutive
 */
class IndexNotFoundException extends OutOfRangeException
{
    /**
     * Constructor
     * @param string|int|null $index
     * @param Throwable|null $previous
     */
    public function __construct(string|int|null $index = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($index);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string|int|null $index
     * @return string
     */
    protected static function formatMessage(string|int|null $index) : string
    {
        if ($index === null) return _l('Index not found');

        return _format_safe(_l('Index {{0}} not found'), static::formatIndex($index)) ?? _l('Index not found');
    }


    /**
     * Format index value
     * @param string|int $index
     * @return string
     */
    protected static function formatIndex(string|int $index) : string
    {
        if (is_int($index)) return "$index";
        return Quote::single($index);
    }
}