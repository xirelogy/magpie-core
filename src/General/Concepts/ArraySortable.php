<?php

namespace Magpie\General\Concepts;

/**
 * May sort an array according to given rules
 * @template T
 */
interface ArraySortable
{
    /**
     * Sort the given array of values
     * @param array<T> $values
     * @return array<T>
     */
    public function sort(array $values) : array;
}