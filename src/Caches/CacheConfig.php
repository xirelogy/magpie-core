<?php

namespace Magpie\Caches;

use Exception;
use Magpie\Caches\Concepts\CacheFormattable;
use Magpie\Caches\Concepts\CacheProvidable;
use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Cache configuration
 */
abstract class CacheConfig implements Configurable, EnvConfigurable, TypeClassable, SystemBootable
{
    use CommonConfigurable;
    use CommonTypeConfigurable;
    use EnvTypeConfigurable;


    /**
     * Create cache provider instance
     * @param CacheFormattable|null $formatter
     * @return CacheProvidable
     * @throws Exception
     */
    public abstract function createProvider(?CacheFormattable $formatter = null) : CacheProvidable;


    /**
     * @inheritDoc
     */
    public static final function fromEnv(?string ...$prefixes) : static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['CACHE'], $prefixes));
        return static::fromConfig($provider, $selection);
    }
}