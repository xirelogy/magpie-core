<?php

namespace Magpie\HttpServer;

use Magpie\Codecs\ParserHosts\ArrayParserHost;
use Magpie\HttpServer\Concepts\Collectable;

/**
 * Common collection
 */
abstract class Collection extends ArrayParserHost implements Collectable
{
    /**
     * Constructor
     * @param iterable<string, mixed> $keyValues
     * @param string|null $prefix
     */
    protected function __construct(iterable $keyValues, ?string $prefix = null)
    {
        parent::__construct(iter_flatten($keyValues), $prefix);
    }


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