<?php

namespace Magpie\Commands\Concepts;

use Exception;
use Magpie\Commands\Request;

/**
 * Runnable as a command
 */
interface CommandRunnable
{
    /**
     * Start running
     * @param Request $request
     * @return int
     * @throws Exception
     */
    public function run(Request $request) : int;
}