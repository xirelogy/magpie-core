<?php

namespace Magpie\Models\Providers\Pgsql;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Configs\DbmsConnectionConfig;

/**
 * PostgreSQL specific connection configuration
 */
#[FactoryTypeClass(PgsqlConnection::TYPECLASS, ConnectionConfig::class)]
class PgsqlConnectionConfig extends DbmsConnectionConfig
{
    /**
     * Default schema when not specified
     */
    public const DEFAULT_SCHEMA = 'public';

    protected const CONFIG_SCHEMA = 'schema';


    /**
     * @var string Specific database schema
     */
    public string $schema;


    /**
     * Constructor
     * @param string $hostname
     * @param int|null $port
     * @param string|null $username
     * @param string|null $password
     * @param string|null $database
     * @param string|null $schema
     * @param string|null $charset
     */
    public function __construct(
        string $hostname,
        ?int $port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $database = null,
        ?string $schema = null,
        ?string $charset = null,
    )
    {
        parent::__construct($hostname, $port, $username, $password, $database, $charset);

        $this->schema = $schema ?? static::DEFAULT_SCHEMA;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return PgsqlConnection::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function configDefaultPort() : ?int
    {
        return 5432;
    }


    /**
     * @inheritDoc
     */
    protected static function configDefaultCharset() : ?string
    {
        return 'UTF8';
    }


    /**
     * @inheritDoc
     */
    protected static function specificParseTypeConfig(ConfigParser $parser) : static
    {
        $host = $parser->get(static::CONFIG_HOST);
        $port = $parser->get(static::CONFIG_PORT);
        $username = $parser->get(static::CONFIG_USERNAME);
        $password = $parser->get(static::CONFIG_PASSWORD);
        $database = $parser->get(static::CONFIG_DATABASE);
        $schema = $parser->get(static::CONFIG_SCHEMA);
        $charset = $parser->get(static::CONFIG_CHARSET);

        return new static($host, $port, $username, $password, $database, $schema, $charset);
    }


    /**
     * @inheritDoc
     */
    public static function getConfigurationKeys() : iterable
    {
        yield from parent::getConfigurationKeys();

        yield static::CONFIG_SCHEMA =>
            ConfigKey::create('schema', false, StringParser::create(), desc: _l('Specific schema'));
    }


    /**
     * @inheritDoc
     */
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static
    {
        $host = $parserHost->requires($envKey->key('HOST'), StringParser::create());
        $port = $parserHost->optional($envKey->key('PORT'), IntegerParser::create()->withMin(1)->withMax(65535), static::configDefaultPort());
        $username = $parserHost->optional($envKey->key('USERNAME'), StringParser::create());
        $password = $parserHost->optional($envKey->key('PASSWORD'), StringParser::create());
        $database = $parserHost->optional($envKey->key('DATABASE'), StringParser::create());
        $schema = $parserHost->optional($envKey->key('SCHEMA'), StringParser::create());
        $charset = $parserHost->optional($envKey->key('CHARSET'), StringParser::create(), static::configDefaultCharset());

        return new static($host, $port, $username, $password, $database, $schema, $charset);
    }
}