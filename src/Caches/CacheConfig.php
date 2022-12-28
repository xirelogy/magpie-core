<?php

namespace Magpie\Caches;

use Exception;
use Magpie\Caches\Concepts\CacheFormattable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Cache configuration
 */
abstract class CacheConfig implements TypeClassable, SystemBootable
{
    use EnvTypeConfigurable;


    /**
     * Create cache provider instance
     * @param CacheFormattable|null $formatter
     * @return CacheProvidable
     * @throws Exception
     */
    public abstract function createProvider(?CacheFormattable $formatter = null) : CacheProvidable;


    /**
     * Create configuration from environment variables
     * @param string|null $prefix
     * @return static
     * @throws ArgumentException
     */
    public static function fromEnv(?string $prefix = null) : static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('CACHE', $prefix);

        return static::fromEnvType($parserHost, $envKey);
    }
}