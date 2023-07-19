<?php

namespace Magpie\Codecs\ParserHosts;

use Magpie\Exceptions\MissingArgumentException;
use Magpie\General\Sugars\Quote;

/**
 * Parse host based on an array
 */
class ArrayParserHost extends CommonParserHost
{
    /**
     * @var array Host array
     */
    public readonly array $arr;


    /**
     * Constructor
     * @param array $arr
     * @param string|null $prefix
     */
    public function __construct(array $arr, ?string $prefix = null)
    {
        parent::__construct($prefix);

        $this->arr = $arr;
    }


    /**
     * @inheritDoc
     */
    protected function hasInternal(string|int $inKey) : bool
    {
        return array_key_exists($inKey, $this->arr);
    }


    /**
     * @inheritDoc
     */
    protected function obtainRaw(int|string $key, int|string $inKey, bool $isMandatory, mixed $default) : mixed
    {
        if (!array_key_exists($inKey, $this->arr)) {
            if ($isMandatory) throw new MissingArgumentException($this->fullKey($key), argType: $this->argType);
            return $default;
        }

        return $this->arr[$inKey];
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        return $this->prefix . Quote::square($key);
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        if (is_empty_string($this->prefix)) return $key;
        if (is_empty_string($key)) return $this->prefix;

        return $this->prefix . Quote::square($key);
    }
}