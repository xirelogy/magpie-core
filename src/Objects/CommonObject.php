<?php

namespace Magpie\Objects;

use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * A common object
 */
abstract class CommonObject implements Packable
{
    use CommonPackable;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        // Default NOP
    }
}