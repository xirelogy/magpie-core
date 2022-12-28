<?php

namespace Magpie\Cryptos;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\BootContext;

/**
 * Context for cryptography function provider
 */
abstract class Context implements TypeClassable, SystemBootable
{
    /**
     * @var static|null The default context
     */
    private static ?self $defaultContext = null;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Initialize new context
     * @param string|null $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(?string $typeClass = null) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize();
    }


    /**
     * Specifically initialize new context
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function specificInitialize() : static;


    /**
     * Get default context
     * @return static
     * @throws SafetyCommonException
     */
    public static function getDefault() : static
    {
        if (static::$defaultContext === null) {
            static::$defaultContext = static::initialize();
        }

        return static::$defaultContext;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
        ClassFactory::includeDirectory(__DIR__ . '/Algorithms/AsymmetricCryptos');
        ClassFactory::includeDirectory(__DIR__ . '/Algorithms/AsymmetricCryptos/Chunkings');
        ClassFactory::includeDirectory(__DIR__ . '/Algorithms/AsymmetricCryptos/Paddings');
        ClassFactory::includeDirectory(__DIR__ . '/Algorithms/Hashes');
        ClassFactory::includeDirectory(__DIR__ . '/Algorithms/SymmetricCryptos');
        ClassFactory::includeDirectory(__DIR__ . '/Paddings');
    }
}