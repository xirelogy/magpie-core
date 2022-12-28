<?php

namespace Magpie\General\Concepts;

/**
 * Anything releasable
 */
interface Releasable
{
    /**
     * Release any resources/dependencies held
     * @return void
     */
    public function release() : void;
}