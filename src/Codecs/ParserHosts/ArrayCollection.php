<?php

namespace Magpie\Codecs\ParserHosts;

use Magpie\Codecs\Concepts\Collectable;

/**
 * General array-based collection
 */
abstract class ArrayCollection extends ArrayParserHost implements Collectable
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