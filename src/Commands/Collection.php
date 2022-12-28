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
}