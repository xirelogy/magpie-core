<?php

namespace Magpie\General\Contexts;

use Magpie\General\Concepts\Releasable;
use Magpie\General\Traits\ReleaseOnDestruct;
use Throwable;

/**
 * Provide support for a specific context in scope
 */
abstract class Scoped implements Releasable
{
    use ReleaseOnDestruct;


    /**
     * @var bool Success state
     */
    protected bool $isSuccessful = false;
    /**
     * @var Throwable|null The exception/error that was caught in scope
     */
    protected ?Throwable $ex = null;
    /**
     * @var bool If resource already released
     */
    protected bool $isReleased = false;


    /**
     * Run within scope
     * @param callable():T $fn Target function
     * @return T
     * @template T
     * @throws Throwable
     */
    public function run(callable $fn) : mixed
    {
        try {
            $ret = $fn();
            $this->succeeded();
            return $ret;
        } catch (Throwable $ex) {
            $this->crash($ex);
            throw $ex;
        }
    }
    

    /**
     * @inheritDoc
     */
    public final function release() : void
    {
        if ($this->isReleased) return;
        $this->isReleased = true;

        $this->onRelease();
    }


    /**
     * Actually releasing any resources/dependencies held
     * @return void
     */
    protected abstract function onRelease() : void;


    /**
     * Notify that the execution within scope is successful
     * @return void
     */
    public final function succeeded() : void
    {
        // No longer successful after crashed
        if ($this->ex !== null) return;

        $this->isSuccessful = true;
        $this->onSucceeded();
    }


    /**
     * Any additional action when execution within scope is successful
     * @return void
     */
    protected function onSucceeded() : void
    {
        // Default NOP
    }


    /**
     * Notify that an exception had been thrown within scope
     * @param Throwable $ex
     * @return void
     */
    public final function crash(Throwable $ex) : void
    {
        $this->ex = $ex;
        $this->onCrash($ex);
    }


    /**
     * Any additional action when an exception had been thrown within scope
     * @param Throwable $ex
     * @return void
     */
    protected function onCrash(Throwable $ex) : void
    {
        // Default NOP
    }
}