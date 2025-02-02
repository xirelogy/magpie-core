<?php

namespace Magpie\Configurations\Traits;

use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Exceptions\ArgumentException;

/**
 * Trait to support configuration
 * @requires \Magpie\Configurations\Concepts\Configurable
 */
trait CommonConfigurable
{
    /**
     * @inheritDoc
     */
    public static function fromConfig(ConfigProvider $provider, ?ConfigSelectable $selection = null) : static
    {
        $parser = $provider->createParser(static::getConfigurationKeys(), $selection);
        return static::parseConfig($parser);
    }


    /**
     * Create instance by parsing from configuration
     * @param ConfigParser $parser
     * @return static
     * @throws ArgumentException
     */
    protected static abstract function parseConfig(ConfigParser $parser) : static;
}