<?php

namespace Magpie\Models\Providers\Sqlite;

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Models\Configs\ConnectionConfig;

/**
 * SQLite specific connection configuration
 */
#[FactoryTypeClass(SqliteConnection::TYPECLASS, ConnectionConfig::class)]
class SqliteConnectionConfig extends ConnectionConfig
{
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
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $path = $parserHost->requires($envKey->key('PATH'), StringParser::create());

        return new static($path);
    }
}