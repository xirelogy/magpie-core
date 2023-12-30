<?php

namespace Magpie\Logs\Relays;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Logs\LogConfig;

/**
 * File-based relay log with specific file name
 */
#[FactoryTypeClass(SpecificFileLogRelay::TYPECLASS, ConfigurableLogRelay::class)]
class SpecificFileLogRelay extends FileBasedLogRelay
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'specific-file';

    /**
     * @var string Specific filename to be used
     */
    protected readonly string $specificFilename;


    /**
     * Constructor
     * @param string $specificFilename
     * @param LogConfig $config
     * @param string|null $source
     */
    public function __construct(string $specificFilename, LogConfig $config, ?string $source = null)
    {
        parent::__construct($config, $source);

        $this->specificFilename = $specificFilename;
    }


    /**
     * @inheritDoc
     */
    protected function getFilename() : string
    {
        return $this->specificFilename;
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
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        /** @var LogConfig $config */
        $config = $payload[static::ENV_PAYLOAD_CONFIG];

        $specificFilename = $parserHost->requires($envKey->key('FILENAME'), StringParser::create());

        return new static($specificFilename, $config);
    }
}