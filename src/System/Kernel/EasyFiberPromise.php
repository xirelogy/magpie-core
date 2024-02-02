<?php

namespace Magpie\System\Kernel;

use Fiber;
use FiberError;
use Magpie\Exceptions\InvalidStateException;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Concepts\ExceptionContextLocalizable;
use Magpie\System\Impls\EasyFiberTrackInstance;
use Throwable;

/**
 * Fiber execution as a promise
 * @template T
 */
final class EasyFiberPromise
{
    /**
     * @var bool If the current fiber is started
     */
    private bool $isStarted = false;
    /**
     * @var bool If the current fiber is running
     */
    private bool $isRunning = false;
    /**
     * @var Fiber|null Fiber waiting for current promise to be fulfilled
     */
    private ?Fiber $waitFiber = null;
    /**
     * @var T|null Return value from executing the fiber
     */
    private mixed $returnValue = null;
    /**
     * @var Throwable|null Return exception caused in the fiber
     */
    private ?Throwable $returnException = null;


    /**
     * Constructor
     * @param bool $isBlocking
     * @param callable():T $fn
     * @param array $args
     * @throws FiberError
     */
    protected function __construct(bool $isBlocking, callable $fn, array $args)
    {
        $fiber = new Fiber($this->hostFiber(...));

        try {
            $fiber->start($fn, $args);
        } catch (FiberError $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            $this->returnException = $ex;
        } finally {
            $this->isStarted = true;
        }

        if ($isBlocking) {
            EasyFiberTrackInstance::registerBlockingFiber($this);
        }
    }


    /**
     * If current fiber is still fiber
     * @return bool
     */
    public function isRunning() : bool
    {
        return $this->isRunning;
    }


    /**
     * Get the execution result
     * @param T|null $default Default value when fiber is still running
     * @return T
     * @throws Throwable
     */
    public function getResult(mixed $default = null) : mixed
    {
        // Filter for no result
        if (!$this->isStarted) return $default;
        if ($this->isRunning) return $default;

        // Return result or throw the related exception
        if ($this->returnException !== null) throw $this->returnException;
        return $this->returnValue;
    }


    /**
     * Wait for completion of the promise on current fiber
     * @param T|null $default
     * @return T
     * @throws InvalidStateException
     * @throws Throwable
     */
    public function wait(mixed $default = null) : mixed
    {
        $currentFiber = Fiber::getCurrent();
        if ($currentFiber === null) throw new InvalidStateException();

        // When completed, returns directly
        if ($this->isStarted && !$this->isRunning) {
            return $this->getResult($default);
        }

        // Subscribe to notification of fiber termination
        if ($this->waitFiber !== null) throw new InvalidStateException();
        $this->waitFiber = $currentFiber;

        // Suspend for return
        return Fiber::suspend();
    }


    /**
     * Run and host the fiber
     * @param callable $fn
     * @param array $args
     * @return void
     */
    private function hostFiber(callable $fn, array $args) : void
    {
        $this->isRunning = true;
        try {
            $this->returnValue = $fn(...$args);
        } catch (Throwable $ex) {
            if ($ex instanceof ExceptionContextLocalizable) $ex = $ex->exceptionLocalize();
            $this->returnException = $ex;
        } finally {
            $this->isRunning = false;
        }

        // Relay result to waiting fiber, if needed
        if ($this->waitFiber !== null) {
            Excepts::noThrow(function () {
                if ($this->returnException !== null) {
                    $this->waitFiber->throw($this->returnException);
                } else {
                    $this->waitFiber->resume($this->returnValue);
                }
            });
        }
    }


    /**
     * Create a new fiber, run the fiber and return as a promise.
     * @param callable():T $fn
     * @param mixed ...$args
     * @return static
     * @throws FiberError
     */
    public static function create(callable $fn, mixed ...$args) : static
    {
        return new static(true, $fn, $args);
    }


    /**
     * Create a new fiber, run the fiber and return as a promise.
     * The executing fiber is non-blocking and does not need a main loop to maintain
     * @param callable():T $fn
     * @param mixed ...$args
     * @return static
     * @throws FiberError
     */
    public static function createNonBlocking(callable $fn, mixed ...$args) : static
    {
        return new static(false, $fn, $args);
    }


    /**
     * Maintain the main loop until all (blocking) promises are fulfilled
     * @return void
     * @throws InvalidStateException
     */
    public static function loop() : void
    {
        EasyFiberTrackInstance::loopUntilPromisesFulfilled();
    }
}