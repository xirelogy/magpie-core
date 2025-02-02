<?php

namespace Magpie\Models\Configs;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Configurations\Providers\ConfigParser;

/**
 * DBMS related database connection specific configuration
 */
abstract class DbmsConnectionConfig extends ConnectionConfig
{
    protected const CONFIG_HOST = 'host';
    protected const CONFIG_PORT = 'port';
    protected const CONFIG_USERNAME = 'username';
    protected const CONFIG_PASSWORD = 'password';
    protected const CONFIG_DATABASE = 'database';
    protected const CONFIG_CHARSET = 'charset';

    /**
     * @var string Hostname
     */
    public string $hostname = '';
    /**
     * @var int|null Port number
     */
    public ?int $port = null;
    /**
     * @var string|null Specific username to be used
     */
    public ?string $username = null;
    /**
     * @var string|null Specific password to be used
     */
    public ?string $password = null;
    /**
     * @var string|null Specific database to be connected to
     */
    public ?string $database = null;
    /**
     * @var string|null Specific character set
     */
    public ?string $charset = null;


    /**
     * Constructor
     * @param string $hostname
     * @param int|null $port
     * @param string|null $username
     * @param string|null $password
     * @param string|null $database
     * @param string|null $charset
     */
    public function __construct(
        string $hostname,
        ?int $port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $database = null,
        ?string $charset = null,
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
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
        $charset = $parser->get(static::CONFIG_CHARSET);

        return new static($host, $port, $username, $password, $database, $charset);
    }


    /**
     * @inheritDoc
     */
    public static function getConfigurationKeys() : iterable
    {
        yield static::CONFIG_HOST =>
            ConfigKey::create('host', true, StringParser::create(), desc: _l('Hostname'));
        yield static::CONFIG_PORT =>
            ConfigKey::create('port', false, IntegerParser::create()->withMin(1)->withMax(65535), static::configDefaultPort(), desc: _l('Port'));
        yield static::CONFIG_USERNAME =>
            ConfigKey::create('username', false, StringParser::create(), desc: _l('Username'));
        yield static::CONFIG_PASSWORD =>
            ConfigKey::create('password', false, StringParser::create(), desc: _l('Password'));
        yield static::CONFIG_DATABASE =>
            ConfigKey::create('database', false, StringParser::create(), desc: _l('Specific database'));
        yield static::CONFIG_CHARSET =>
            ConfigKey::create('charset', false, StringParser::create(), static::configDefaultCharset(), desc: _l('Character set'));
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
        $charset = $parserHost->optional($envKey->key('CHARSET'), StringParser::create(), static::configDefaultCharset());

        return new static($host, $port, $username, $password, $database, $charset);
    }


    /**
     * Default port if not specified
     * @return int|null
     */
    protected static function configDefaultPort() : ?int
    {
        return null;
    }


    /**
     * Default character set if not specified
     * @return string|null
     */
    protected static function configDefaultCharset() : ?string
    {
        return null;
    }
}