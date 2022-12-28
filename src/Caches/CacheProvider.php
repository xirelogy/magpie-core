<?php

namespace Magpie\Caches;

use Magpie\Caches\Concepts\CacheFormattable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Caches\Formats\PhpSerializeCacheFormatter;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\Kernel;

/**
 * Cache provider information
 */
class CacheProvider
{
    use StaticClass;


    /**
     * Default cache provider
     * @return CacheProvidable
     * @throws SafetyCommonException
     */
    public static function getDefaultProvider() : CacheProvidable
    {
        $provider = Kernel::current()->getProvider(CacheProvidable::class);
        if ($provider === null) throw new NullException();
        if (!$provider instanceof CacheProvidable) throw new NotOfTypeException($provider, CacheProvidable::class);

        return $provider;
    }


    /**
     * Default cache formatter
     * @return CacheFormattable
     */
    public static function getDefaultFormatter() : CacheFormattable
    {
        return new PhpSerializeCacheFormatter();
    }
}