<?php

namespace Magpie\Consoles\Concepts;

use Magpie\General\Concepts\TypeClassable;

/**
 * May be displayed on console
 */
interface ConsoleDisplayable extends TypeClassable
{
    /**
     * Export for display
     * @return object
     * @internal
     */
    public function _export() : object;
}