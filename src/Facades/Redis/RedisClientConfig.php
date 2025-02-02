<?php

namespace Magpie\Facades\Redis;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\Concepts\ConfigRedirectable;
use Magpie\Configurations\Concepts\ConfigSelectable;
use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\ConfigRedirect;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Objects\BasicUsernamePassword;

/**
 * Configuration for redis client
 * @implements ConfigRedirectable<static>
 */
class RedisClientConfig implements Configurable, ConfigRedirectable, EnvConfigurable
{
    use CommonConfigurable;

    /**
     * The default port for redis
     */
    public const DEFAULT_PORT = 6379;

    protected const CONFIG_HOST = 'host';
    protected const CONFIG_PORT = 'port';
    protected const CONFIG_DB = 'db';
    protected const CONFIG_USERNAME = 'username';
    protected const CONFIG_PASSWORD = 'password';


    /**
     * @var string Host name
     */
    public string $host;
    /**
     * @var int|null Specific port number to connect to
     */
    public ?int $port;
    /**
     * @var BasicUsernamePassword|string|null Authentication to be used
     */
    public BasicUsernamePassword|string|null $auth;
    /**
     * @var int|null Database to be selected
     */
    public ?int $database;


    /**
     * Constructor
     * @param string $host Host name
     * @param int|null $port Specific port number to connect to
     * @param BasicUsernamePassword|string|null $auth Authentication to be used
     * @param int|null $database Database to be selected
     */
    public function __construct(
        string                            $host,
        ?int                              $port = null,
        BasicUsernamePassword|string|null $auth = null,
        ?int                              $database = null,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
        $this->database = $database;
    }


    /**
     * @inheritDoc
     */
    public static function fromEnv(?string ...$prefixes) : static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['REDIS'], $prefixes));

        return static::fromConfig($provider, $selection);
    }


    /**
     * @inheritDoc
     */
    public static function createConfigRedirectSetup(ConfigProvider $provider) : ConfigRedirect
    {
        $setupFunction = function () use ($provider) {
            if ($provider::getTypeClass() == EnvConfigProvider::TYPECLASS) {
                return EnvConfigProvider::createRedirectSetup(['REDIS']);
            }

            return ConfigRedirect::invalid();
        };

        $setup = $setupFunction();

        return $setup->chain(function (ConfigSelectable $selection) use ($provider) {
            return static::fromConfig($provider, $selection);
        });
    }


    /**
     * @inheritDoc
     */
    protected static function parseConfig(ConfigParser $parser) : static
    {
        $host = $parser->get(static::CONFIG_HOST);
        $port = $parser->get(static::CONFIG_PORT);
        $database = $parser->get(static::CONFIG_DB);

        $authUsername = $parser->get(static::CONFIG_USERNAME);
        $authPassword = $parser->get(static::CONFIG_PASSWORD);
        $auth = static::translateAuth($authUsername, $authPassword);

        return new static($host, $port, $auth, $database);
    }


    /**
     * @inheritDoc
     */
    public static function getConfigurationKeys() : iterable
    {
        yield static::CONFIG_HOST
            => ConfigKey::create('host', true, StringParser::create(), desc: _l('Hostname'));
        yield static::CONFIG_PORT
            => ConfigKey::create('port', false, IntegerParser::create()->withMin(1)->withMax(65535), static::DEFAULT_PORT, desc: _l('Port'));
        yield static::CONFIG_DB
            => ConfigKey::create('db', false, IntegerParser::create()->withMin(0), desc: _l('Database number'));
        yield static::CONFIG_USERNAME
            => ConfigKey::create('username', false, StringParser::create(), desc: _l('Username'));
        yield static::CONFIG_PASSWORD
            => ConfigKey::create('password', false, StringParser::create(), desc: _l('Password'));
    }


    /**
     * Translate authentication username/password to corresponding authentication specification
     * @param string|null $authUsername
     * @param string|null $authPassword
     * @return BasicUsernamePassword|string|null
     */
    protected static function translateAuth(?string $authUsername, ?string $authPassword) : BasicUsernamePassword|string|null
    {
        if ($authPassword === null) return null;
        if ($authUsername === null) return $authPassword;

        return new BasicUsernamePassword($authUsername, $authPassword);
    }


    /**
     * Create a parser to parse redis client configuration from environment
     * @return Parser<static>
     * @deprecated
     */
    public static function createEnvParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $prefix = ($value !== '-') ? StringParser::create()->parse($value, $hintName) : null;
            return static::fromEnv($prefix);
        });
    }
}