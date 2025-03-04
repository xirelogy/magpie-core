<?php

namespace Magpie\General;

use Closure;

/**
 * A lazy array that is evaluated on use
 * @template T
 */
class LazyArray
{
    /**
     * @var Closure():iterable<T> Items iteration function
     */
    protected readonly Closure $itemsFn;
    /**
     * @var array<T>|null Cached return
     */
    protected ?array $cachedItems = null;


    /**
     * Constructor
     * @param iterable<T> $items
     */
    public function __construct(iterable $items)
    {
        $this->itemsFn = function () use ($items) {
            yield from $items;
        };
    }


    /**
     * Get items
     * @return array<T>
     */
    public function getItems() : array
    {
        if ($this->cachedItems === null) {
            $this->cachedItems = iter_flatten(($this->itemsFn)(), false);
        }

        return $this->cachedItems;
    }
}