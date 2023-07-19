<?php

namespace Magpie\Commands;

use Magpie\Codecs\ParserHosts\ArrayParserHost;
use Magpie\HttpServer\Concepts\Collectable;

/**
 * Collection
 */
abstract class Collection extends ArrayParserHost implements Collectable
{
    /**
     * @inheritDoc
     */
    public function getKeys() : iterable
    {
        foreach (array_keys($this->arr) as $inKey) {
            yield $this->formatKey($inKey);
        }
    }


    /**
     * @inheritDoc
     */
    public function all() : iterable
    {
        foreach ($this->arr as $inKey => $value) {
            yield $this->formatKey($inKey) => $value;
        }
    }


    /**
     * @inheritDoc
     */
    public function fullKey(int|string $key) : string
    {
        if (is_empty_string($this->prefix)) return $key;
        return $this->prefix . '.' . $key;
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(int|string|null $key) : ?string
    {
        if (is_empty_string($this->prefix)) return $key;
        if (is_empty_string($key)) return $this->prefix;

        return $this->prefix . '.' . $key;
    }
}