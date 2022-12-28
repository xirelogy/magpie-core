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
     * @var Throwable|null The exception/error that was caught in scope
     */
    protected ?Throwable $ex = null;
    /**
     * @var bool If resource already released
     */
    protected bool $isReleased = false;



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