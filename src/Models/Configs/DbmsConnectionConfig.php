<?php

namespace Magpie\Models\Configs;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;

/**
 * DBMS related database connection specific configuration
 */
abstract class DbmsConnectionConfig extends ConnectionConfig
{
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
    protected static function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey) : static
    {
        $host = $parserHost->requires($envKey->key('HOST'), StringParser::create());
        $port = $parserHost->optional($envKey->key('PORT'), IntegerParser::create()->withMin(1)->withMax(65535), static::envDefaultPort());
        $username = $parserHost->optional($envKey->key('USERNAME'), StringParser::create());
        $password = $parserHost->optional($envKey->key('PASSWORD'), StringParser::create());
        $database = $parserHost->optional($envKey->key('DATABASE'), StringParser::create());
        $charset = $parserHost->optional($envKey->key('CHARSET'), StringParser::create(), static::envDefaultCharset());

        return new static($host, $port, $username, $password, $database, $charset);
    }


    /**
     * Default port if not specified
     * @return int|null
     */
    protected static function envDefaultPort() : ?int
    {
        return null;
    }


    /**
     * Default character set if not specified
     * @return string|null
     */
    protected static function envDefaultCharset() : ?string
    {
        return null;
    }
}