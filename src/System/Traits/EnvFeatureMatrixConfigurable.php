<?php

namespace Magpie\System\Traits;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\EnvKeySchema;
use Magpie\Configurations\EnvParserHost;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\Factories\ClassFactory;

/**
 * Trait to support feature-matrix typed configuration
 */
trait EnvFeatureMatrixConfigurable
{
    /**
     * Create specific feature-matrix typed configuration from environment variables
     * @param EnvParserHost $parserHost
     * @param EnvKeySchema $envKey
     * @param array $payload
     * @return static
     * @throws ArgumentException
     */
    protected static final function fromEnvFeatureMatrix(EnvParserHost $parserHost, EnvKeySchema $envKey, array $payload = []) : static
    {
        $typeParser = ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $values = explode(';', $value);
            if (count($values) !== 2) throw new InvalidDataException();
            $subjectTypeClass = trim($values[0]);
            $featureTypeClass = trim($values[1]);
            $className = ClassFactory::resolveFeature($featureTypeClass, $subjectTypeClass, self::class);
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