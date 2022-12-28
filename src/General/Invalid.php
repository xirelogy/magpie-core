<?php

namespace Magpie\General;

use Magpie\General\Traits\SingletonInstance;
use Stringable;

/**
 * Representation of something invalid, usable whe null may be meaningful
 */
final class Invalid implements Stringable
{
    use SingletonInstance;


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return '<invalid>';
    }
}