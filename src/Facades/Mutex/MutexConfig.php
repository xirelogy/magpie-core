<?php

namespace Magpie\Facades\Mutex;

use Exception;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\Facades\Mutex\Concepts\MutexProvidable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Mutex configuration
 */
abstract class MutexConfig implements TypeClassable, SystemBootable
{
    use EnvTypeConfigurable;


    /**
     * Create mutex provider instance
     * @return MutexProvidable
     * @throws Exception
     */
    public abstract function createProvider() : MutexProvidable;


    /**
     * Create configuration from environment variables
     * @param string|null $prefix
     * @return static
     * @throws ArgumentException
     */
    public static function fromEnv(?string $prefix = null) : static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('MUTEX', $prefix);

        return static::fromEnvType($parserHost, $envKey);
    }
}