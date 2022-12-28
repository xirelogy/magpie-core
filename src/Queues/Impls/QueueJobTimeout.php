<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace Magpie\Queues\Impls;

use Magpie\General\Concepts\Releasable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Controls queue job timeout using asynchronous signals
 * @internal
 */
class QueueJobTimeout implements Releasable
{
    use ReleaseOnDestruct;

    /**
     * @var bool If 'pcntl' extension supported
     */
    protected readonly bool $isSupported;
    /**
     * @var bool If released
     */
    protected bool $isReleased = false;
    /**
     * @var bool If releasable
     */
    protected bool $isReleasable = false;


    /**
     * Constructor
     * @param Duration|null $timeout
     * @param callable():void $fn
     */
    public function __construct(?Duration $timeout, callable $fn)
    {
        $this->isSupported = extension_loaded('pcntl');
        if (!$this->isSupported) return;

        $timeoutSec = $timeout?->getSeconds() ?? 0;
        if ($timeoutSec <= 0) return;

        pcntl_signal(SIGALRM, $fn);
        pcntl_alarm($timeoutSec);

        $this->isReleasable = true;
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if ($this->isReleased) return;
        if (!$this->isSupported) return;
        if (!$this->isReleasable) return;

        $this->isReleased = true;
        pcntl_alarm(0);
    }
}