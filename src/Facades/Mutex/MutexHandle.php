<?php

namespace Magpie\Facades\Mutex;

use Exception;
use Magpie\Exceptions\OperationFailedException;
use Magpie\General\Concepts\Releasable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Handle to a single mutex instance
 */
abstract class MutexHandle implements Releasable
{
    use ReleaseOnDestruct;

    /**
     * @var array<string, string> Currently acquired keys
     */
    private static array $acquiredKeys = [];

    /**
     * @var string Associated mutex key
     */
    protected readonly string $key;
    /**
     * @var Duration TTL before key is automatically released
     */
    protected readonly Duration $ttl;
    /**
     * @var bool If mutex is acquired
     */
    private bool $isAcquired = false;
    /**
     * @var bool If mutex is acquired from this handle
     */
    private bool $isOwned = false;


    /**
     * Constructor
     * @param string $key
     * @param Duration $ttl
     */
    protected function __construct(string $key, Duration $ttl)
    {
        $this->key = $key;
        $this->ttl = $ttl;
    }


    /**
     * Try to acquire the mutex
     * @param Duration|null $timeout
     * @return bool
     * @throws OperationFailedException
     */
    public final function acquire(?Duration $timeout = null) : bool
    {
        // Do not re-acquire
        if ($this->isAcquired) return true;

        // Allow re-entrant on the same key
        if (array_key_exists($this->key, static::$acquiredKeys)) {
            $this->isAcquired = true;
            return true;
        }

        // Attempt to acquire
        if (!$this->onAcquire($timeout)) return false;

        // Update states
        static::$acquiredKeys[$this->key] = $this->key;
        $this->isAcquired = true;
        $this->isOwned = true;

        return true;
    }


    /**
     * Try to acquire the mutex from the specific provider or implementation
     * @param Duration|null $timeout
     * @return bool
     * @throws OperationFailedException
     */
    protected abstract function onAcquire(?Duration $timeout) : bool;


    /**
     * @inheritDoc
     */
    public final function release() : void
    {
        if (!$this->isAcquired) return;

        try {
            if (!$this->isOwned) return;
            unset(static::$acquiredKeys[$this->key]);

            $this->onRelease();
        } catch (Exception) {
            // Ignored
        } finally {
            $this->isAcquired = false;
        }
    }


    /**
     * Release the mutex from the specific provider or implementation
     * @return void
     * @throws Exception
     */
    protected abstract function onRelease() : void;


    /**
     * If mutex is acquired
     * @return bool
     */
    public final function isAcquired() : bool
    {
        return $this->isAcquired;
    }


    /**
     * Sleep for some time, to be used in-between attempts to acquire mutex
     * @return void
     */
    public static function sleep() : void
    {
        usleep(100);
    }
}