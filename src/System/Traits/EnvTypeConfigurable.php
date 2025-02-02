<?php

namespace Magpie\System\Traits;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\General\Factories\ClassFactory;

/**
 * Trait to support typed configuration
 * @deprecated
 */
trait EnvTypeConfigurable
{
    /**
     * Create specific typed configuration from environment variables
     * @param EnvParserHost $parserHost
     * @param EnvKeySchema $envKey
     * @param array $payload
     * @return static
     * @throws ArgumentException
     */
    protected static final function fromEnvType(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload = []) : static
    {
        $typeParser = ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $className = ClassFactory::resolve($value, self::class);
            if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

            return $className;
        });

        /** @var static $typeClassName */
        $typeClassName = $parserHost->requires($envKey->key('TYPE'), $typeParser);

        return $typeClassName::specificFromEnv($parserHost, $envKey, $payload);
    }


    /**
     * Create specific configuration from environment variables
     * @param EnvParserHost $parserHost
     * @param EnvKeySchema $envKey
     * @param array $payload
     * @return static
     * @throws ArgumentException
     */
    protected static abstract function specificFromEnv(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload) : static;
}