<?php

namespace Magpie\System\Impls;

use Fiber;
use Magpie\Exceptions\InvalidStateException;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\EasyFiberPromise;
use Magpie\System\Kernel\MainLoop;

/**
 * Instance to maintain track for fiber
 * @internal
 */
class EasyFiberTrackInstance
{
    use StaticClass;

    /**
     * @var array<EasyFiberPromise> Currently tracked promises
     */
    private static array $blockingPromises = [];


    /**
     * Add a promise to be tracked
     * @param EasyFiberPromise $fiberPromise
     * @return void
     */
    public static function registerBlockingFiber(EasyFiberPromise $fiberPromise) : void
    {
        static::$blockingPromises[] = $fiberPromise;
    }


    /**
     * Run and maintain a main loop until all promises are fulfilled
     * @param Duration|null $checkDuration Fulfillment check duration
     * @return void
     * @throws InvalidStateException
     */
    public static function loopUntilPromisesFulfilled(?Duration $checkDuration = null) : void
    {
        // Must not be running from a fiber
        if (Fiber::getCurrent() !== null) throw new InvalidStateException();

        // If everything fulfilled, return
        if (static::isPromisesFulfilled()) return;

        // Create the checking fiber
        $checkDuration = $checkDuration ?? Duration::inMilliseconds(20);
        Excepts::noThrow(function () use ($checkDuration) {
            EasyFiber::run(function (Duration $checkDuration) {
                for (;;) {
                    EasyFiber::sleep($checkDuration);
                    if (static::isPromisesFulfilled()) {
                        MainLoop::terminate();
                        return;
                    }
                }
            }, $checkDuration);
        });

        MainLoop::run();
    }


    /**
     * Check if all promises fulfilled
     * @return bool
     */
    protected static function isPromisesFulfilled() : bool
    {
        foreach (static::$blockingPromises as $blockingPromise) {
            if ($blockingPromise->isRunning()) return false;
        }

        return true;
    }
}