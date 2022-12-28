<?php

namespace Magpie\Objects;

use Magpie\General\Concepts\Releasable;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Multiple releasable items
 */
class ReleasableCollection implements Releasable
{
    use ReleaseOnDestruct;

    /**
     * @var array<Releasable> All releasable items
     */
    protected array $items = [];


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        foreach ($this->items as $item) {
            $item->release();
        }

        $this->items = [];
    }


    /**
     * Add an item
     * @param Releasable $item
     * @return void
     */
    public function add(Releasable $item) : void
    {
        $this->items[] = $item;
    }


    /**
     * Add an item (if it is releasable)
     * @param mixed $item
     * @return void
     */
    public function addIfReleasable(mixed $item) : void
    {
        if (!$item instanceof Releasable) return;
        $this->items[] = $item;
    }
}