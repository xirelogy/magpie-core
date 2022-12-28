<?php

namespace Magpie\System\Traits;

use Exception;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Runnable with exception handled within
 * @requires \Magpie\General\Concepts\Runnable
 */
trait SafeRunnable
{
    /**
     * Start running, with exception handled within (will not throw)
     * @return void
     */
    public function safeRun() : void
    {
        try {
            $this->run();
        } catch (Exception $ex) {
            ExceptionHandler::handle($ex);
        }
    }
}