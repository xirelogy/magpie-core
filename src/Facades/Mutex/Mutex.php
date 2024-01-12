<?php

namespace Magpie\Facades\Mutex;

use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\OperationTimeoutException;
use Magpie\Facades\Mutex\Impls\DefaultMutexHandle;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Stopwatch;

/**
 * Mutex (mutual exclusion) context
 */
abstract class Mutex
{
    /**
     * @var Duration Current TTL
     */
    protected Duration $ttl;


    /**
     * Constructor
     * @param Duration|null $ttl
     */
    protected function __construct(?Duration $ttl = null)
    {
        $this->ttl = $ttl ?? static::getDefaultTtl();
    }


    /**
     * Run the callable in current mutex's context
     * @param callable():T $fn
     * @param Duration|null $timeout
     * @return T
     * @throws OperationFailedException
     * @throws Exception
     * @template T
     */
    public final function run(callable $fn, ?Duration $timeout = null) : mixed
    {
        $lock = $this->acquire($timeout);

        try {
            return $fn();
        } finally {
            $lock->release();
        }
    }


    /**
     * Acquire a lock for current mutex
     * @param Duration|null $timeout
     * @return MutexLock
     * @throws OperationFailedException
     */
    public final function acquire(?Duration $timeout = null) : MutexLock
    {
        return $this->tryAcquire($timeout) ?? throw new OperationTimeoutException();
    }


    /**
     * Try to acquire a lock for current mutex
     * @param Duration|null $timeout
     * @return MutexLock|null
     * @throws OperationFailedException
     */
    public final function tryAcquire(?Duration $timeout = null) : ?MutexLock
    {
        $handles = $this->_createHandles();
        if (!static::tryAcquireHandles($handles, $timeout)) return null;

        return new class($handles) extends MutexLock {
            /**
             * Constructor
             * @param array<MutexHandle> $handles Associated handles
             */
            public function __construct(
                protected array $handles,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function release() : void
            {
                foreach ($this->handles as $handle) {
                    $handle->release();
                }
            }
        };
    }


    /**
     * Try to acquire a lock for current mutex using given handles
     * @param array<MutexHandle> $handles
     * @param Duration|null $timeout
     * @return bool
     * @throws OperationFailedException
     */
    private static function tryAcquireHandles(array $handles, ?Duration $timeout) : bool
    {
        $totalHandles = count($handles);

        if ($totalHandles === 0) throw new OperationFailedException();

        // Single handle: simple delegation
        if ($totalHandles === 1) {
            return $handles[0]->acquire($timeout);
        }

        // Multiple handles: composite acquire
        $sw = Stopwatch::create();
        while (true) {
            if (static::tryAcquireHandlesImmediately($handles)) return true;

            // Any partially acquired are released
            foreach ($handles as $handle) {
                $handle->release();
            }

            // Check timeout
            if ($sw->isTimeout($timeout)) return false;

            // Pause before next attempt
            MutexHandle::sleep();
        }
    }


    /**
     * Try to acquire a lock for current mutex using given handles, without any timeout
     * @param array<MutexHandle> $handles
     * @return bool
     * @throws OperationFailedException
     */
    private static function tryAcquireHandlesImmediately(array $handles) : bool
    {
        $zero = Duration::inSeconds(0);

        foreach ($handles as $handle) {
            if (!$handle->acquire($zero)) return false;
        }

        return true;
    }


    /**
     * Current mutex lock key
     * @return string
     */
    public abstract function getMutexKey() : string;


    /**
     * Create associated handles
     * @return array<MutexHandle>
     * @internal
     */
    protected function _createHandles() : array
    {
        return [
            $this->createHandle(),
        ];
    }


    /**
     * Create associated handle
     * @return MutexHandle
     */
    protected function createHandle() : MutexHandle
    {
        return DefaultMutexHandle::create(static::class, $this->getMutexKey(), $this->ttl);
    }


    /**
     * Default TTL for mutex
     * @return Duration
     */
    public static function getDefaultTtl() : Duration
    {
        return Duration::inSeconds(120);
    }
}