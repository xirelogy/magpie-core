<?php

namespace Magpie\Logs\Relays;

use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Logs\LogConfig;

/**
 * Simple file-based relay log
 */
#[FactoryTypeClass(SimpleFileLogRelay::TYPECLASS, ConfigurableLogRelay::class)]
class SimpleFileLogRelay extends FileBasedLogRelay
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'simple-file';


    /**
     * @inheritDoc
     */
    protected function getFilename() : string
    {
        $appName = LogConfig::systemDefaultSource();

        return "$appName.log";
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificParseTypeConfig(ConfigParser $parser) : static
    {
        /** @var LogConfig $config */
        $config = $parser->getContext(static::CONTEXT_CONFIG);

        return new static($config);
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        /** @var LogConfig $config */
        $config = $payload[static::CONTEXT_CONFIG];

        return new static($config);
    }
}