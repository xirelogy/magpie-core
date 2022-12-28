<?php

namespace Magpie\General\Concepts;

use Exception;

/**
 * Pack target into a relatively static (non-dynamic data) state.
 */
interface Packable
{
    /**
     * Pack into object
     * @param PackSelectEnumerable|null $globalSelectors Global selectors to be applied and transferred during pack
     * @param string|null ...$selectorSpecs Specific selections to be applied during this pack
     * @return object
     * @throws Exception
     */
    public function pack(?PackSelectEnumerable $globalSelectors = null, ?string... $selectorSpecs) : object;
}