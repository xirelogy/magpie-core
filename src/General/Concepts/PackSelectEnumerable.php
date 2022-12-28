<?php

namespace Magpie\General\Concepts;

/**
 * May enumerate all selected selectors
 */
interface PackSelectEnumerable
{
    /**
     * All selected selectors
     * @return iterable<string>
     */
    public function getSelectors() : iterable;
}