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
        return [];
    }


    /**
     * @throws ArgumentException
     */
    protected static final function parseConfig(ConfigParser $parser) : static
    {
        // Create configuration key
        $typeParser = ClosureParser::create(function (mixed $value, ?string $hintName) : string {
            $value = StringParser::create()->parse($value, $hintName);
            $className = ClassFactory::resolve($value, self::class);
            if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

            return $className;
        });
        $configKey = ConfigKey::create(static::getTypeConfigName(), true, $typeParser, desc: ConfigProvider::describeType());

        /** @var static $typeClassName */
        $typeClassName = $parser->get($configKey);

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