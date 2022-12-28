<?php

namespace Magpie\Models\Casts;

use Magpie\Models\Concepts\AttributeCastable;

/**
 * Default cast implementation that does nothing
 */
class DefaultAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : mixed
    {
        return $value;
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : mixed
    {
        return $value;
    }
}