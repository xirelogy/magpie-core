<?php

namespace Magpie\Queues\Impls\Caches;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Magpie\Caches\Traits\CommonCacheable;
use Magpie\General\DateTimes\Duration;

/**
 * When workers are restarted at
 * @internal
 * @deprecated
 */
class WorkersRestartedAt extends BaseCacheable
{
    use CommonCacheable;

    /**
     * Cache key
     */
    protected const KEY = 'timestamp';
    /**
     * @var int When restarted
     */
    public readonly int $timestamp;


    /**
     * Constructor
     * @param int $timestamp
     */
    protected function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }


    /**
     * @inheritDoc
     */
    public function getCacheKey() : string
    {
        return static::KEY;
    }


    /**
     * Check against provided timestamp where worker is started, and decide if the worker shall be restarted
     * @param CarbonInterface $workerStarted
     * @return bool
     * @throws Exception
     */
    public static function shallRestart(CarbonInterface $workerStarted) : bool
    {
        $instance = static::onCacheFind(static::KEY);
        if ($instance === null) return false;

        return $workerStarted->getTimestamp() < $instance->timestamp;
    }


    /**
     * Create an instance
     * @param Duration|null $timeout
     * @return static
     * @throws Exception
     */
    public static function create(?Duration $timeout = null) : static
    {
        $timestamp = Carbon::now()->getTimestamp();
        $instance = new static($timestamp);

        $timeout = $timeout ?? Duration::inSeconds(1800);

        return static::onCacheCreate($instance, $timeout);
    }
}