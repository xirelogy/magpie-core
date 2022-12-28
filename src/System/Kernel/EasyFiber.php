<?php

namespace Magpie\System\Kernel;

use Exception;
use Fiber;
use Magpie\Exceptions\InvalidStateException;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\ExceptionContextLocalizable;
use Magpie\System\Impls\TimerAsyncHandle;
use Magpie\System\Impls\TimerAsyncPoll;

/**
 * Fiber utilities for more convenient usage
 */
class EasyFiber
{
    use StaticClass;


    /**
     * Create a new fiber and run the fiber
     * @param callable(mixed):void $fn
     * @param mixed ...$args
     * @return mixed
     * @throws Exception
     */
    public static function run(callable $fn, mixed ...$args) : mixed
    {
        $fiber = new Fiber($fn);

        return Excepts::convertThrowable(fn () => $fiber->start(...$args));
    }


    /**
     * Suspend current fiber
     * @param mixed|null $value
     * @return mixed
     * @throws Exception
     */
    public static function suspend(mixed $value = null) : mixed
    {
        try {
            return Excepts::convertThrowable(fn () => Fiber::suspend($value));
        } catch (Exception $ex) {
            if ($ex instanceof ExceptionContextLocalizable) throw $ex->exceptionLocalize();
            throw $ex;
        }
    }


    /**
     * Resume to another fiber
     * @param Fiber $fiber
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public static function resume(Fiber $fiber, mixed $value) : mixed
    {
        return Excepts::convertThrowable(fn () => $fiber->resume($value));
    }


    /**
     * Resume to another fiber by throwing exception
     * @param Fiber $fiber
     * @param Exception $ex
     * @return mixed
     * @throws Exception
     */
    public static function throw(Fiber $fiber, Exception $ex) : mixed
    {
        return Excepts::convertThrowable(fn () => $fiber->throw($ex));
    }


    /**
     * Sleep asynchronously
     * @param Duration|int $duration
     * @return void
     * @throws Exception
     */
    public static function sleep(Duration|int $duration) : void
    {
        $fiber = Fiber::getCurrent();
        if ($fiber === null) throw new InvalidStateException();

        $handle = new TimerAsyncHandle($duration);

        try {
            TimerAsyncPoll::instance()->registerHandle($handle);
            $handle->asyncWait($fiber);
        } finally {
            TimerAsyncPoll::instance()->deregisterHandle($handle);
        }
    }
}