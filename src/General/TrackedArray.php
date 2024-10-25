<?php

namespace Magpie\General;

use Magpie\Facades\Random;
use Magpie\General\Randoms\RandomCharset;

/**
 * Array of items whereby ownership is tracked
 * @template T
 */
final class TrackedArray
{
    /**
     * @var array<string, T> All tracked items
     */
    private array $items = [];


    /**
     * Add an item
     * @param T $item
     * @return string
     */
    public function add(mixed $item) : string
    {
        $key = $this->generateKey();
        $this->items[$key] = $item;
        return $key;
    }


    /**
     * Remove an item based on given key
     * @param string $key
     * @return bool
     */
    public function remove(string $key) : bool
    {
        if (!array_key_exists($key, $this->items)) return false;

        unset($this->items[$key]);
        return true;
    }


    /**
     * All items
     * @return iterable<string, T>
     */
    public function getItems() : iterable
    {
        yield from $this->items;
    }


    /**
     * Generate a tracking key (guaranteed to be unique)
     * @return string
     */
    protected function generateKey() : string
    {
        while (true) {
            $ret = Random::string(8, RandomCharset::LOWER_ALPHANUM);
            if (!array_key_exists($ret, $this->items)) return $ret;
        }
    }
}