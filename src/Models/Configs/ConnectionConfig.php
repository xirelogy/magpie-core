<?php

namespace Magpie\Models\Configs;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\Concepts\Configurable;
use Magpie\Configurations\Concepts\EnvConfigurable;
use Magpie\Configurations\Providers\EnvConfigProvider;
use Magpie\Configurations\Providers\EnvConfigSelection;
use Magpie\Configurations\Traits\CommonConfigurable;
use Magpie\Configurations\Traits\CommonTypeConfigurable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\System\Traits\EnvTypeConfigurable;

/**
 * Database connection specific configuration
 */
abstract class ConnectionConfig implements Configurable, EnvConfigurable, TypeClassable
{
    use CommonConfigurable;
    use CommonTypeConfigurable;
    use EnvTypeConfigurable;


    /**
     * @inheritDoc
     */
    public static function fromEnv(?string ...$prefixes) : static
    {
        $provider = EnvConfigProvider::create();
        $selection = new EnvConfigSelection(array_merge(['DB'], $prefixes));

        return static::fromConfig($provider, $selection);
    }


    /**
     * Create a parser to parse configuration from environment
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