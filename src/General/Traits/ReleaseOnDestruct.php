<?php

namespace Magpie\General\Traits;

use Magpie\General\Concepts\Releasable;

/**
 * Release the current releasable during destruction
 */
trait ReleaseOnDestruct
{
    /**
     * Destructor
     */
    public function __destruct()
    {
        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if ($this instanceof Releasable) $this->release();
    }
}