<?php

namespace Magpie\Configurations\Traits;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\ConfigKey;
use Magpie\Configurations\ConfigName;
use Magpie\Configurations\Providers\ConfigParser;
use Magpie\Configurations\Providers\ConfigProvider;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\General\Factories\ClassFactory;

/**
 * Trait to support typed configuration
 * @requires CommonConfigurable
 */
trait CommonTypeConfigurable
{
    /**
     * @inheritDoc
     */
    public static function getConfigurationKeys() : iterable
    {
        $typeName = static::getTypeConfigName();

        $typeParser = ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $className = ClassFactory::resolve($value, self::class);
            if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

            return $className;
        });

        yield $typeName => ConfigKey::create($typeName, true, $typeParser, desc: ConfigProvider::describeType());
    }


    /**
     * @throws ArgumentException
     */
    protected static final function parseConfig(ConfigParser $parser) : static
    {
        /** @var static $typeClassName */
        $typeClassName = $parser->get(static::getTypeConfigName());

        return $typeClassName::specificParseConfig($parser);
    }


    /**
     * Configuration name for parsing type
     * @return ConfigName|string
     */
    protected static function getTypeConfigName() : ConfigName|string
    {
        return 'type';
    }


    /**
     * Create instance by parsing specifically from configuration
     * @param ConfigParser $parser
     * @return static
     * @throws ArgumentException
     */
    private static function specificParseConfig(ConfigParser $parser) : static
    {
        $parser->addKeys(static::getConfigurationKeys());
        return static::specificParseTypeConfig($parser);
    }


    /**
     * Create instance by parsing specifically from configuration
     * @param ConfigParser $parser
     * @return static
     * @throws ArgumentException
     */
    protected static abstract function specificParseTypeConfig(ConfigParser $parser) : static;
}