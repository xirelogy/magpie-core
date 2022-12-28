<?php

namespace Magpie\General\Simples;

use JsonException;
use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\General\Bitmask;
use Magpie\General\Traits\StaticClass;

/**
 * JSON encoding/decoding, simplified to support commonly used flags
 */
class SimpleJSON
{
    use StaticClass;


    /**
     * Option to force decoding as array
     */
    public const OPT_DECODE_AS_ARRAY = 1;


    /**
     * Encode in JSON format (safe)
     * @param mixed $value
     * @param int $options
     * @return string|null
     */
    public static function safeEncode(mixed $value, int $options = 0) : ?string
    {
        try {
            return static::encode($value, $options);
        } catch (InvalidJsonDataFormatException) {
            return null;
        }
    }


    /**
     * Encode in JSON format
     * @param mixed $value
     * @param int $options
     * @return string
     * @throws InvalidJsonDataFormatException
     */
    public static function encode(mixed $value, int $options = 0) : string
    {
        try {
            _used($options);

            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new InvalidJsonDataFormatException($ex->getMessage());
        }
    }


    /**
     * Decode from JSON (safe)
     * @param string $encoded
     * @param int $options
     * @param mixed|null $default
     * @return mixed
     */
    public static function safeDecode(string $encoded, int $options = 0, mixed $default = null) : mixed
    {
        try {
            return static::decode($encoded, $options);
        } catch (InvalidJsonDataFormatException) {
            return $default;
        }
    }


    /**
     * Decode from JSON
     * @param string $encoded
     * @param int $options
     * @return mixed
     * @throws InvalidJsonDataFormatException
     */
    public static function decode(string $encoded, int $options = 0) : mixed
    {
        try {
            $isAssociative = Bitmask::isSet($options, static::OPT_DECODE_AS_ARRAY);

            return json_decode($encoded, $isAssociative, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new InvalidJsonDataFormatException($ex->getMessage());
        }
    }
}