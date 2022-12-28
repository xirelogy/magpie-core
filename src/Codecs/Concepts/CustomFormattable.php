<?php

namespace Magpie\Codecs\Concepts;

use Magpie\General\Concepts\PackSelectEnumerable;

/**
 * Implement this interface to provide custom (specific) formatting
 */
interface CustomFormattable
{
    /**
     * Apply formatting
     * @param string|null $formatterClass The formatter class applying formatting (if available)
     * @param PackSelectEnumerable|null $packSelectors Associated global pack selectors (if available)
     * @return mixed
     */
    public function format(?string $formatterClass = null, ?PackSelectEnumerable $packSelectors = null) : mixed;
}