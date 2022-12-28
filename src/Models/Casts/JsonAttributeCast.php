<?php

namespace Magpie\Models\Casts;

use Magpie\Exceptions\UnsupportedFromDbValueException;
use Magpie\General\Simples\SimpleJSON;
use Magpie\Models\Concepts\AttributeCastable;

/**
 * Cast for values encoded as JSON string
 */
class JsonAttributeCast implements AttributeCastable
{
    /**
     * @inheritDoc
     */
    public static function fromDb(string $key, mixed $value) : mixed
    {
        if (is_string($value)) return SimpleJSON::decode($value);

        throw new UnsupportedFromDbValueException($value);
    }


    /**
     * @inheritDoc
     */
    public static function toDb(string $key, mixed $value) : string
    {
        return SimpleJSON::encode($value);
    }
}