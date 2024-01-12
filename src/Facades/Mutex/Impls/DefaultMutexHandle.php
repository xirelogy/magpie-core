<?php

namespace Magpie\Facades\Mutex\Impls;

use Exception;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\Facades\Mutex\MutexHandle;
use Magpie\General\DateTimes\Duration;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;

/**
 * An actual mutex handle
 * @internal
 */
final class DefaultMutexHandle extends MutexHandle
{
    /**
     * Constructor
     * @param string $key
     * @param Duration $ttl
     */
    private function __construct(string $key, Duration $ttl)
    {
        parent::__construct($key, $ttl);
    }


    /**
     * @inheritDoc
     */
    protected function onAcquire(?Duration $timeout) : bool
    {
        return static::getProvider()->acquire($this->key, $this->ttl, $timeout);
    }


    /**
     * @inheritDoc
     */
    protected function onRelease() : void
    {
        static::getProvider()->release($this->key);
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
}