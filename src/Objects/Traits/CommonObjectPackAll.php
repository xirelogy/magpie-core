<?php

namespace Magpie\Objects\Traits;

use Exception;
use Magpie\General\Packs\PackContext;

/**
 * Implementation of onPack() that packs all properties
 * @requires \Magpie\General\Traits\CommonPackable
 */
trait CommonObjectPackAll
{
    /**
     * Pack into object
     * @param object $ret Target to pack into
     * @param PackContext $context Associated pack context
     * @return void
     * @throws Exception
     */
    protected final function onPack(object $ret, PackContext $context) : void
    {
        _used($context);

        foreach ($this as $objKey => $objValue) {
            $ret->{$objKey} = $objValue;
        }
    }
}