<?php

namespace Magpie\Logs\Relays;

use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogRelay;
use Magpie\System\Traits\EnvTypeConfigurable;
use Throwable;

/**
 * Configurable log relay
 */
abstract class ConfigurableLogRelay extends LogRelay implements Configurable, TypeClassable
{
    use CommonConfigurable;
    use CommonTypeConfigurable;
    use EnvTypeConfigurable;

    /**
     * Log configuration (in config context)
     */
    protected const CONTEXT_CONFIG = 'config';


    /**
     * Create instance by parsing from environment variables
     * @param LogConfig $config
     * @param string|null ...$prefixes
     * @return static|null
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function fromSpecificEnv(LogConfig $config, ?string ...$prefixes) : ?static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['LOG'], $prefixes));

        return static::fromSpecificConfig($provider, $config, $selection);
    }


    /**
     * Create instance by parsing from specific configuration
     * @param ConfigProvider $provider
     * @param LogConfig $config
     * @param ConfigSelectable|null $selection
     * @return static|null
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    private static function fromSpecificConfig(ConfigProvider $provider, LogConfig $config, ?ConfigSelectable $selection) : ?static
    {
        $provider->withContext(static::CONTEXT_CONFIG, $config);

        // Extra check for 'TYPE'
        try {
            $parser = $provider->createParser(static::getConfigurationKeys(), $selection);
            if (!$parser->has('type')) return null;
        } catch (Throwable) {
            return null;
        }

        return static::fromConfig($provider, $selection);
    }
}