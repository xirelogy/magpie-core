<?php

namespace Magpie\General\Randoms;

use Magpie\General\Concepts\Randomable;
use Magpie\General\Traits\CommonRandomable;
use Magpie\System\Kernel\Kernel;

/**
 * A common random provider
 */
abstract class RandomProvider implements Randomable
{
    use CommonRandomable;


    /**
     * @inheritDoc
     */
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(Randomable::class, $this);
    }
}