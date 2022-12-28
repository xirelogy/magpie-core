<?php

namespace Magpie\Queues\Impls\Caches;

use Magpie\Caches\Concepts\Cacheable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Caches\Traits\CommonCacheable;
use Magpie\Queues\Providers\QueueCreator;

/**
 * Cacheable items for queue implementation
 * @internal
 */
abstract class BaseCacheable implements Cacheable
{
    use CommonCacheable;


    /**
     * @inheritDoc
     */
    protected static function getCacheProvider() : CacheProvidable
    {
        return QueueCreator::instance()->getCacheProvider();
    }
}