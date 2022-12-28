<?php

namespace Magpie\Facades\Mutex\Impls;

use Exception;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\General\Concepts\Releasable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;

/**
 * An actual mutex handle
 * @internal
 */
final class MutexHandle implements Releasable
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
    private function __construct(string $key, Duration $ttl)
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
    public function acquire(?Duration $timeout = null) : bool
    {
        // Do not re-acquire
        if ($this->isAcquired) return true;

        // Allow re-entrant on the same key
        if (array_key_exists($this->key, static::$acquiredKeys)) {
            $this->isAcquired = true;
            return true;
        }

        // Attempt to acquire
        if (!static::getProvider()->acquire($this->key, $this->ttl, $timeout)) return false;

        // Update states
        static::$acquiredKeys[$this->key] = $this->key;
        $this->isAcquired = true;
        $this->isOwned = true;

        return true;
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if (!$this->isAcquired) return;

        try {
            if (!$this->isOwned) return;
            unset(static::$acquiredKeys[$this->key]);

            static::getProvider()->release($this->key);
        } catch (Exception) {
            // Ignored
        } finally {
            $this->isAcquired = false;
        }
    }


    /**
     * If mutex is acquired
     * @return bool
     */
    public function isAcquired() : bool
    {
        return $this->isAcquired;
    }


    /**
     * Get the associated provider
     * @return MutexProvidable
     */
    protected static function getProvider() : MutexProvidable
    {
        try {
            $provider = Kernel::current()->getProvider(MutexProvidable::class);
            if (!$provider instanceof MutexProvidable) throw new NotOfTypeException($provider, MutexProvidable::class);
            return $provider;
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Create an instance
     * @param string $className
     * @param string $key
     * @param Duration $ttl
     * @return static
     */
    public static function create(string $className, string $key, Duration $ttl) : static
    {
        return new static("$className::$key", $ttl);
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