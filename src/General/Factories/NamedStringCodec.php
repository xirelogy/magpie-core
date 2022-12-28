<?php

namespace Magpie\General\Factories;

use BackedEnum;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\NamedStringEncodable;
use Magpie\General\Factories\Annotations\NamedString;
use Magpie\General\Factories\Encoders\Sha1NamedStringEncoder;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\HardCore\SourceCache;
use Magpie\System\Kernel\Kernel;
use Magpie\System\Traits\DirectoryDiscoverable;
use Magpie\System\Traits\LazyBootable;

/**
 * Named string encoding/decoding support
 */
class NamedStringCodec implements SourceCacheable
{
    use StaticClass;
    use DirectoryDiscoverable;
    use LazyBootable;

    /**
     * @var bool If already booted up
     */
    protected static bool $isBoot = false;
    /**
     * @var array<string, int> Forward mappings
     */
    protected static array $fwdMappings = [];
    /**
     * @var array<int, string> Reversed mappings
     */
    protected static array $revMappings = [];


    /**
     * Encode a named string
     * @param string $value
     * @return int
     * @throws UnsupportedValueException
     */
    public static final function encode(string $value) : int
    {
        static::ensureBoot();

        if (!array_key_exists($value, static::$fwdMappings)) {
            throw new UnsupportedValueException($value, _l('encoded named string'));
        }

        return static::$fwdMappings[$value];
    }


    /**
     * Decode a named string
     * @param int $value
     * @return string
     */
    public static final function decode(int $value) : string
    {
        static::ensureBoot();

        return static::$revMappings[$value] ?? "$value";
    }


    /**
     * @inheritDoc
     */
    protected static function onBoot() : void
    {
        $cached = SourceCache::instance()->getCache(static::class);
        if ($cached !== null) {
            static::$fwdMappings = $cached['fwdMappings'];
            static::$revMappings = $cached['revMappings'];
            return;
        }

        $autoload = AutoloadReflection::instance();

        $encoder = static::getEncoder();

        foreach ($autoload->expandDiscoverySourcesReflection(static::$discoverDirectories) as $class) {
            foreach ($class->getReflectionConstants() as $constant) {
                foreach ($constant->getAttributes(NamedString::class) as $attribute) {
                    _used($attribute);

                    // Get value as string (support string, backed-enums, stringables)
                    $value = $constant->getValue();
                    if ($value instanceof BackedEnum) $value = $value->value;
                    $value = "$value";

                    if (array_key_exists($value, static::$fwdMappings)) continue;   // Prevent duplicates

                    $encodedValue = $encoder->encode($value);
                    if (array_key_exists($encodedValue, static::$revMappings)) throw new DuplicatedKeyException($encodedValue);

                    static::$fwdMappings[$value] = $encodedValue;
                    static::$revMappings[$encodedValue] = $value;
                }
            }
        }
    }


    /**
     * Get the specific encoder to be used
     * @return NamedStringEncodable
     */
    protected static function getEncoder() : NamedStringEncodable
    {
        $provider = Kernel::current()->getProvider(NamedStringEncodable::class);
        if ($provider instanceof NamedStringEncodable) return $provider;

        return new Sha1NamedStringEncoder();
    }


    /**
     * @inheritDoc
     */
    public static function saveSourceCache() : void
    {
        static::ensureBoot();
        SourceCache::instance()->setCache(static::class, [
            'fwdMappings' => static::$fwdMappings,
            'revMappings' => static::$revMappings,
        ]);
    }


    /**
     * @inheritDoc
     */
    public static function deleteSourceCache() : void
    {
        SourceCache::instance()->deleteCache(static::class);

        // Un-boot
        static::$isBoot = false;
        static::$fwdMappings = [];
        static::$revMappings = [];
    }
}