<?php

namespace Magpie\Models\Providers\Sqlite;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Models\Configs\ConnectionConfig;

/**
 * SQLite specific connection configuration
 */
#[FactoryTypeClass(SqliteConnection::TYPECLASS, ConnectionConfig::class)]
class SqliteConnectionConfig extends ConnectionConfig
{
    protected const CONFIG_PATH = 'path';

    /**
     * @var string SQLite path
     */
    public string $path = '';


    /**
     * Constructor
     * @param string $path
     */
    public function __construct(
        string $path,
    ) {
        $this->path = $path;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return SqliteConnection::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificParseTypeConfig(ConfigParser $parser) : static
    {
        $path = $parser->get(static::CONFIG_PATH);

        return new static($path);
    }


    /**
     * @inheritDoc
     */
    public static function getConfigurationKeys() : iterable
    {
        yield from parent::getConfigurationKeys();

        yield static::CONFIG_PATH =>
            ConfigKey::create('path', true, StringParser::create(), desc: _l('File path'));
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $path = $parserHost->requires($envKey->key('PATH'), StringParser::create());

        return new static($path);
    }
}