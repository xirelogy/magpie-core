<?php

namespace Magpie\Facades\Redis;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\Objects\BasicUsernamePassword;

/**
 * Configuration for redis client
 */
class RedisClientConfig
{
    /**
     * The default port for redis
     */
    public const DEFAULT_PORT = 6379;


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
     * Create configuration from environment variables
     * @param string|null $prefix
     * @return static
     * @throws ArgumentException
     */
    public static function fromEnv(?string $prefix = null) : static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('REDIS', $prefix);

        $host = $parserHost->requires($envKey->key('HOST'), StringParser::create());
        $port = $parserHost->optional($envKey->key('PORT'), IntegerParser::create()->withMin(1)->withMax(65535), 6379);
        $database = $parserHost->optional($envKey->key('DB'), IntegerParser::create()->withMin(0));

        $authUsername = $parserHost->optional($envKey->key('USERNAME'), StringParser::create());
        $authPassword = $parserHost->optional($envKey->key('PASSWORD'), StringParser::create());
        $auth = static::translateAuth($authUsername, $authPassword);

        return new static($host, $port, $auth, $database);
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
     */
    public static function createEnvParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $prefix = ($value !== '-') ? StringParser::create()->parse($value, $hintName) : null;
            return static::fromEnv($prefix);
        });
    }
}