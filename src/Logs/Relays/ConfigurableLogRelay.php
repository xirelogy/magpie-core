<?php

namespace Magpie\Logs\Relays;

use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogRelay;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Configurable log relay
 */
abstract class ConfigurableLogRelay extends LogRelay implements TypeClassable
{
    use EnvTypeConfigurable;

    /**
     * Configuration from environment payload
     */
    protected const ENV_PAYLOAD_CONFIG = 'config';


    /**
     * Create configuration from environment variables
     * @param LogConfig $config
     * @param string|null $prefix
     * @return static|null
     * @throws ArgumentException
     */
    public static function fromEnv(LogConfig $config, ?string $prefix = null) : ?static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('LOG', $prefix);

        if (!$parserHost->has($envKey->key('TYPE'))) return null;

        return static::fromEnvType($parserHost, $envKey, [
            static::ENV_PAYLOAD_CONFIG => $config,
        ]);
    }
}