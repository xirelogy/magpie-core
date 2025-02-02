<?php

namespace Magpie\Configurations\Concepts;

use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May be configured from configuration
 */
interface Configurable
{
    /**
     * Create instance by parsing from configuration
     * @param ConfigProvider $provider
     * @param ConfigSelectable|null $selection
     * @return static
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function fromConfig(ConfigProvider $provider, ?ConfigSelectable $selection = null) : static;


    /**
     * Get all configuration keys
     * @return iterable<string|null, ConfigKey>
     */
    public static function getConfigurationKeys() : iterable;
}