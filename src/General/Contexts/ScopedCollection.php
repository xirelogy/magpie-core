<?php

namespace Magpie\General\Contexts;

use Throwable;

/**
 * Collection of Multiple context in scope
 */
class ScopedCollection extends Scoped
{
    /**
     * @var array<Scoped> All sub items
     */
    protected array $items = [];


    /**
     * Constructor
     * @param iterable<Scoped> $items
     */
    public function __construct(iterable $items)
    {
        $this->items = iter_flatten($items, false);
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        foreach ($this->items as $item) {
            $item->release();
        }
    }


    /**
     * @inheritDoc
     */
    protected function onSucceeded() : void
    {
        foreach ($this->items as $item) {
            $item->succeeded();
        }
    }


    /**
     * @inheritDoc
     */
    protected function onCrash(Throwable $ex) : void
    {
        foreach ($this->items as $item) {
            $item->crash($ex);
        }
    }
}