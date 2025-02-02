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
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\Factories\ClassFactory;

/**
 * Trait to support feature matrix configuration
 * @requires CommonConfigurable
 */
trait CommonFeatureMatrixConfigurable
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
        return static::specificParseFeatureMatrixConfig($parser);
    }


    /**
     * Create instance by parsing specifically from configuration
     * @param ConfigParser $parser
     * @return static
     * @throws ArgumentException
     */
    protected static abstract function specificParseFeatureMatrixConfig(ConfigParser $parser) : static;
}