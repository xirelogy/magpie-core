<?php

namespace Magpie\Consoles;

use Magpie\Consoles\Concepts\Consolable;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\Kernel;

/**
 * Console provider
 */
class ConsoleProvider
{
    use StaticClass;


    /**
     * Get current default console
     * @return Consolable|null
     */
    public static function default() : ?Consolable
    {
        if (!Kernel::hasCurrent()) return null;

        $provider = Kernel::current()->getProvider(Consolable::class);
        if (!$provider instanceof Consolable) return null;

        return $provider;
    }
}