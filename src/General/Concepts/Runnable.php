<?php

namespace Magpie\General\Concepts;

use Exception;

/**
 * Anything runnable
 */
interface Runnable
{
    /**
     * Start running
     * @return void
     * @throws Exception
     */
    public function run() : void;
}