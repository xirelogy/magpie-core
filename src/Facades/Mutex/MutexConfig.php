<?php

namespace Magpie\Facades\Mutex;

use Exception;
use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Mutex configuration
 */
abstract class MutexConfig implements Configurable, EnvConfigurable, TypeClassable, SystemBootable
{
    use CommonConfigurable;
    use CommonTypeConfigurable;
    use EnvTypeConfigurable;


    /**
     * Create mutex provider instance
     * @return MutexProvidable
     * @throws Exception
     */
    public abstract function createProvider() : MutexProvidable;


    /**
     * @inheritDoc
     */
    public static function fromEnv(?string ...$prefixes) : static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['MUTEX'], $prefixes));

        return static::fromConfig($provider, $selection);
    }
}