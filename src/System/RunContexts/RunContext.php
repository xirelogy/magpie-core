<?php

namespace Magpie\System\RunContexts;

use Exception;
use Magpie\General\Concepts\Runnable;
use Magpie\System\Concepts\Capturable;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;
use Magpie\System\Traits\SafeRunnable;

/**
 * Context of execution
 */
abstract class RunContext implements Runnable, Capturable
{
    use SafeRunnable;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Destructor
     */
    public function __destruct()
    {
        if (Kernel::hasCurrent()) Kernel::current()->_notifyContextDestructing();
    }


    /**
     * @inheritDoc
     */
    public final static function capture() : static
    {
        try {
            return static::onCapture();
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Handle capturing from current context
     * @return static
     * @throws Exception
     */
    protected abstract static function onCapture() : static;
}