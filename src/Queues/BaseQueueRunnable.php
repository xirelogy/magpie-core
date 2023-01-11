<?php

namespace Magpie\Queues;

use Exception;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Contexts\ScopedCollection;
use Magpie\Queues\Concepts\Queueable;
use Magpie\Queues\Concepts\QueueRunnable;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Common implementation for runnable that can be dispatched to queue
 */
abstract class BaseQueueRunnable implements QueueRunnable
{
    /**
     * Start running, ignoring any exceptions
     * @return void
     */
    public final function safeRun() : void
    {
        try {
            $this->run();
        } catch (Exception $ex) {
            ExceptionHandler::ignoredAndWarn(static::class, 'run()', $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public final function run() : void
    {
        // Setup scope
        $scoped = new ScopedCollection($this->getScopedItems());

        try {
            $this->onRun();
            $scoped->succeeded();
        } catch (Exception $ex) {
            $scoped->crash($ex);
            throw $ex;
        } finally {
            $scoped->release();
        }
    }


    /**
     * Actual running
     * @return void
     * @throws Exception
     */
    protected abstract function onRun() : void;


    /**
     * All scoped items
     * @return iterable<Scoped>
     */
    protected function getScopedItems() : iterable
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function queueDispatch() : Queueable
    {
        return new PendingQueueable($this);
    }
}