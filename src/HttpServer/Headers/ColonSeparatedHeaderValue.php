<?php

namespace Magpie\HttpServer\Headers;

use Closure;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\ParserHosts\ArrayCollection;
use Magpie\Codecs\Parsers\StringParser;

/**
 * Header value separated by (semi)colon
 * @implements ObjectParseable<ColonSeparatedHeaderValue>
 */
class ColonSeparatedHeaderValue extends ArrayCollection implements ObjectParseable
{
    /**
     * @var bool If keys are case-sensitive
     */
    protected readonly bool $isCaseSensitive;


    /**
     * Constructor
     * @param array $arr
     * @param bool $isCaseSensitive
     * @param string|null $prefix
     */
    protected function __construct(array $arr, bool $isCaseSensitive, ?string $prefix = null)
    {
        parent::__construct($arr, $prefix);

        $this->isCaseSensitive = $isCaseSensitive;
    }


    /**
     * @inheritDoc
     */
    protected function acceptKey(int|string $key) : string|int
    {
        if (!$this->isCaseSensitive && is_string($key)) return strtolower($key);

        return parent::acceptKey($key);
    }


    /**
     * @inheritDoc
     */
    protected function formatKey(int|string $key) : string|int
    {
        if (!$this->isCaseSensitive && is_string($key)) return strtolower($key);

        return parent::formatKey($key);
    }


    /**
     * @inheritDoc
     */
    public static final function createParser() : ColonSeparatedHeaderValueParser
    {
        $fn = function (mixed $value, ?string $hintName, bool $isCaseSensitive) : static {
            $value = StringParser::create()->parse($value, $hintName);
            return new static(static::explodeValues($value, $isCaseSensitive), $isCaseSensitive, $hintName);
        };

        return new class($fn) extends ColonSeparatedHeaderValueParser {
            /**
             * Constructor
             * @param Closure $fn
             */
            public function __construct(
                protected Closure $fn,
            ) {
                parent::__construct();
            }


            /**
             * @inheritDoc
             */
            protected function onParse(mixed $value, ?string $hintName) : ColonSeparatedHeaderValue
            {
                return ($this->fn)($value, $hintName, $this->isCaseSensitive);
            }
        };
    }


    /**
     * Explode the values
     * @param string $line
     * @param bool $isCaseSensitive
     * @return array<string, string>
     */
    private static function explodeValues(string $line, bool $isCaseSensitive) : array
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
                $ret[static::decodeKey($key, $isCaseSensitive)] = static::decodeValue($value);
            }
        }

        if (!$hasEmpty) unset($ret['']);
        return $ret;
    }


    /**
     * Decode a key
     * @param string $key
     * @param bool $isCaseSensitive
     * @return string
     */
    protected static function decodeKey(string $key, bool $isCaseSensitive) : string
    {
        $ret = trim($key);
        if (!$isCaseSensitive) $ret = strtolower($ret);
        return $ret;
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