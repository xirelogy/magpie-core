<?php

namespace Magpie\HttpServer\Headers;

use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\ParserHosts\ArrayCollection;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;

/**
 * Header value separated by (semi)colon
 * @implements ObjectParseable<ColonSeparatedHeaderValue>
 */
class ColonSeparatedHeaderValue extends ArrayCollection implements ObjectParseable
{
    /**
     * Constructor
     * @param array $arr
     * @param string|null $prefix
     */
    protected function __construct(array $arr, ?string $prefix = null)
    {
        parent::__construct($arr, $prefix);
    }


    /**
     * @inheritDoc
     */
    public static final function createParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $value = StringParser::create()->parse($value, $hintName);
            return new static(static::explodeValues($value), $hintName);
        });
    }


    /**
     * Explode the values
     * @param string $line
     * @return array<string, string>
     */
    private static function explodeValues(string $line) : array
    {
        $ret = [
            '' => '',
        ];
        $hasEmpty = false;
        foreach (explode(';', $line) as $keyValue) {
            if (trim($keyValue) === '') continue;

            $equalPos = strpos($keyValue, '=');
            if ($equalPos === false) {
                // No equal sign, this is empty key value
                $hasEmpty = true;
                $ret[''] = static::decodeValue($keyValue);
            } else {
                // With equal sign, normal key value
                $key = substr($keyValue, 0, $equalPos);
                $value = substr($keyValue, $equalPos + 1);
                $ret[static::decodeKey($key)] = static::decodeValue($value);
            }
        }

        if (!$hasEmpty) unset($ret['']);
        return $ret;
    }


    /**
     * Decode a key
     * @param string $key
     * @return string
     */
    protected static function decodeKey(string $key) : string
    {
        return trim($key);
    }


    /**
     * Decode a value
     * @param string $value
     * @return string
     */
    protected static function decodeValue(string $value) : string
    {
        $ret = trim($value);
        if (str_starts_with($ret, '"') && str_ends_with($ret, '"')) {
            $ret = substr($ret, 1, -1);
        }
        return rawurldecode($ret);
    }
}