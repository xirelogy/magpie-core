<?php

namespace Magpie\General;

use Magpie\General\Traits\SingletonInstance;
use Stringable;

/**
 * Explicitly specified null
 */
final class ExplicitNull implements Stringable
{
    use SingletonInstance;


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return '<null>';
    }
}