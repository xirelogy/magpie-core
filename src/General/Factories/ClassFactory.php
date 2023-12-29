<?php

namespace Magpie\General\Factories;

use Closure;
use Magpie\Exceptions\ConflictingTypeClassDefinitionException;
use Magpie\Exceptions\NoDefaultTypeClassException;
use Magpie\Exceptions\UnsupportedFeatureTypeClassException;
use Magpie\Exceptions\UnsupportedTypeClassException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\HardCore\SourceCache;
use Magpie\System\Traits\DirectoryDiscoverable;
use Magpie\System\Traits\LazyBootable;

/**
 * Class factory support
 */
class ClassFactory implements SourceCacheable
{
    use StaticClass;
    use DirectoryDiscoverable;
    use LazyBootable;

    /**
     * @var bool If already booted up
     */
    protected static bool $isBoot = false;
    /**
     * @var array<string, class-string> Map 'base class'-'type class' pair to their actual class
     */
    protected static array $classMap = [];
    /**
     * @var array<string, class-string> Map 'base association class'-'subject type class'-'feature type class' triplet to their actual class
     */
    protected static array $featureMap = [];
    /**
     * @var array<string, string> Default type classes
     */
    protected static array $defaultTypeClasses = [];
    /**
     * @var array<string, array<int, Closure>> Default type class check conditions
     */
    protected static array $defaultTypeClassChecks = [];


    /**
     * Get the corresponding class name of given base class for given type class
     * @param string|null $typeClass
     * @param class-string $baseClassName
     * @return class-string
     * @throws NoDefaultTypeClassException
     * @throws UnsupportedTypeClassException
     */
    public static function resolve(?string $typeClass, string $baseClassName) : string
    {
        static::ensureBoot();

        $typeClass = $typeClass ?? static::getDefaultTypeClass($baseClassName);

        $mapKey = static::makeClassMapKey($baseClassName, $typeClass);

        if (!array_key_exists($mapKey, static::$classMap)) {
            throw new UnsupportedTypeClassException($typeClass, $baseClassName);
        }

        return static::$classMap[$mapKey];
    }


    /**
     * Get the corresponding class name of given base class for given type class, returning
     * null when failed
     * @param string|null $typeClass
     * @param class-string $baseClassName
     * @return class-string|null
     */
    public static function safeResolve(?string $typeClass, string $baseClassName) : ?string
    {
        return Excepts::noThrow(fn () => static::resolve($typeClass, $baseClassName));
    }


    /**
     * Get the corresponding class name of given association base class for given subject-feature type class
     * @param string $featureTypeClass
     * @param string $subjectTypeClass
     * @param class-string $assocClassName
     * @return class-string
     * @throws UnsupportedFeatureTypeClassException
     */
    public static function resolveFeature(string $featureTypeClass, string $subjectTypeClass, string $assocClassName) : string
    {
        static::ensureBoot();

        $mapKey = static::makeFeatureMapKey($assocClassName, $subjectTypeClass, $featureTypeClass);

        if (!array_key_exists($mapKey, static::$featureMap)) {
            throw new UnsupportedFeatureTypeClassException($featureTypeClass, $subjectTypeClass, $assocClassName);
        }

        return static::$featureMap[$mapKey];
    }


    /**
     * Get the corresponding class name of given association base class for given subject-feature
     * type class, returning null when failed
     * @param string $featureTypeClass
     * @param string $subjectTypeClass
     * @param class-string $assocClassName
     * @return class-string|null
     */
    public static function safeResolveFeature(string $featureTypeClass, string $subjectTypeClass, string $assocClassName) : ?string
    {
        return Excepts::noThrow(fn () => static::resolveFeature($featureTypeClass, $subjectTypeClass, $assocClassName));
    }


    /**
     * @inheritDoc
     */
    protected static function onBoot() : void
    {
        $cached = SourceCache::instance()->getCache(static::class);
        if ($cached !== null) {
            static::$classMap = $cached['classMap'];
            static::$featureMap = $cached['featureMap'];
            return;
        }

        $autoload = AutoloadReflection::instance();

        foreach ($autoload->expandDiscoverySourcesReflection(static::$discoverDirectories, false) as $class) {
            foreach ($class->getAttributes(FactoryTypeClass::class) as $attribute) {
                /** @var FactoryTypeClass $attributeInst */
                $attributeInst = $attribute->newInstance();

                $key = static::makeClassMapKey($attributeInst->baseClassName, $attributeInst->typeClass);
                if (array_key_exists($key, static::$classMap)) {
                    if (static::$classMap[$key] !== $class->name) throw new ConflictingTypeClassDefinitionException($attributeInst->typeClass, $attributeInst->baseClassName);
                    continue;
                }

                static::$classMap[$key] = $class->name;
            }

            foreach ($class->getAttributes(FeatureMatrixTypeClass::class) as $attribute) {
                /** @var FeatureMatrixTypeClass $attributeInst */
                $attributeInst = $attribute->newInstance();

                $key = static::makeFeatureMapKey($attributeInst->assocClassName, $attributeInst->subjectTypeClass, $attributeInst->featureTypeClass);
                if (array_key_exists($key, static::$featureMap)) {
                    if (static::$featureMap[$key] !== $class->name) throw new ConflictingTypeClassDefinitionException($attributeInst->assocClassName, $attributeInst->subjectTypeClass . '/' . $attributeInst->featureTypeClass);
                    continue;
                }

                static::$featureMap[$key] = $class->name;
            }
        }
    }


    /**
     * Create the combination key of given class and a type class
     * @param class-string $className
     * @param string $typeClass
     * @return string
     */
    protected static function makeClassMapKey(string $className, string $typeClass) : string
    {
        return "$className::[$typeClass]";
    }


    /**
     * Create the combination key of given class and subject type class / feature type class
     * @param string $className
     * @param string $subjectTypeClass
     * @param string $featureTypeClass
     * @return string
     */
    protected static function makeFeatureMapKey(string $className, string $subjectTypeClass, string $featureTypeClass) : string
    {
        return "$className::[$subjectTypeClass][$featureTypeClass]";
    }


    /**
     * Get the default type class for given base class
     * @param class-string $baseClassName
     * @return string
     * @throws NoDefaultTypeClassException
     */
    protected static function getDefaultTypeClass(string $baseClassName) : string
    {
        if (!array_key_exists($baseClassName, static::$defaultTypeClasses)) {
            $checkedDefaultTypeClass = static::checkDefaultTypeClass($baseClassName);
            if ($checkedDefaultTypeClass === null) throw new NoDefaultTypeClassException($baseClassName);

            static::$defaultTypeClasses[$baseClassName] = $checkedDefaultTypeClass;
        }

        return static::$defaultTypeClasses[$baseClassName];
    }


    /**
     * Get the default type class for given base class by checking current condition
     * @param class-string $baseClassName
     * @return string|null
     */
    protected static function checkDefaultTypeClass(string $baseClassName) : ?string
    {
        if (!array_key_exists($baseClassName, static::$defaultTypeClassChecks)) return null;

        $checkFunctions = static::$defaultTypeClassChecks[$baseClassName];
        ksort($checkFunctions);

        foreach ($checkFunctions as $checkFunction) {
            $checkResult = Excepts::noThrow(fn () => $checkFunction()); // Exceptions understood as failure
            if ($checkResult !== null) return $checkResult;
        }

        return null;
    }


    /**
     * Set the default type class for given base class
     * @param class-string $baseClassName
     * @param string $typeClass
     * @return void
     */
    public static function setDefaultTypeClass(string $baseClassName, string $typeClass) : void
    {
        static::$defaultTypeClasses[$baseClassName] = $typeClass;
    }


    /**
     * Set the default type class check condition for given base class
     * @param class-string $baseClassName
     * @param callable():(string|null) $checkerFn
     * @param int|null $weight Specific weight
     * @return void
     */
    public static function setDefaultTypeClassCheck(string $baseClassName, callable $checkerFn, ?int $weight = null) : void
    {
        $checks = static::$defaultTypeClassChecks[$baseClassName] ?? [];

        if ($weight === null) {
            $weight = count($checks) + 1000;
        }

        $checks[$weight] = $checkerFn;
        static::$defaultTypeClassChecks[$baseClassName] = $checks;
    }


    /**
     * @inheritDoc
     */
    public static function saveSourceCache() : void
    {
        static::ensureBoot();

        ksort(static::$classMap);
        ksort(static::$featureMap);

        SourceCache::instance()->setCache(static::class, [
            'classMap' => static::$classMap,
            'featureMap' => static::$featureMap,
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
        static::$classMap = [];
        static::$featureMap = [];
    }
}