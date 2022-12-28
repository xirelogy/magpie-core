<?php

namespace Magpie\General\Concepts;

/**
 * Anything that can be dispatched
 */
interface Dispatchable
{
    /**
     * Dispatch the current item
     * @return void
     */
    public function dispatch() : void;
}