<?php

namespace Magpie\Models\Enums;

use Magpie\Exceptions\UnsupportedValueException;

/**
 * Common SQL relation operators
 */
enum CommonOperator : string
{
    /**
     * Equals
     */
    case EQUAL = '=';
    /**
     * Not equals
     */
    case NOT_EQUAL = '<>';
    /**
     * Less than
     */
    case LESS_THAN = '<';
    /**
     * Less than or equal
     */
    case LESS_THAN_EQUAL = '<=';
    /**
     * Greater than
     */
    case GREATER_THAN = '>';
    /**
     * Greater than or equal
     */
    case GREATER_THAN_EQUAL = '>=';
    /**
     * 'LIKE' a pattern
     */
    case LIKE = 'like';
    /**
     * Not 'LIKE' a pattern
     */
    case NOT_LIKE = 'not like';
    /**
     * 'IN' list of values (or sub-query)
     */
    case IN = 'in';
    /**
     * Not 'IN' list of values (or sub-query)
     */
    case NOT_IN = 'not in';


    /**
     * Negate an operator
     * @param CommonOperator $value
     * @return static
     * @throws UnsupportedValueException
     */
    public static function negate(self $value) : self
    {
        return match ($value) {
            static::EQUAL => static::NOT_EQUAL,
            static::NOT_EQUAL => static::EQUAL,
            static::LESS_THAN => static::GREATER_THAN_EQUAL,
            static::LESS_THAN_EQUAL => static::GREATER_THAN,
            static::GREATER_THAN => static::LESS_THAN_EQUAL,
            static::GREATER_THAN_EQUAL => static::LESS_THAN,
            static::LIKE => static::NOT_LIKE,
            static::NOT_LIKE => static::LIKE,
            static::IN => static::NOT_IN,
            static::NOT_IN => static::IN,
            default => throw new UnsupportedValueException($value),
        };
    }
}