<?php

namespace Magpie\Models\Configs;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Database connection specific configuration
 */
abstract class ConnectionConfig implements TypeClassable
{
    use EnvTypeConfigurable;


    /**
     * Create configuration from environment variables
     * @param string|null $prefix
     * @return static
     * @throws ArgumentException
     */
    public static function fromEnv(?string $prefix = null) : static
    {
        $parserHost = new EnvParserHost();
        $envKey = new EnvKeySchema('DB', $prefix);

        return static::fromEnvType($parserHost, $envKey);
    }


    /**
     * Create a parser to parse configuration from environment
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