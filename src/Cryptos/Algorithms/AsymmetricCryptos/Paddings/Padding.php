<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;

/**
 * Padding strategy in asymmetric cryptography
 */
abstract class Padding implements TypeClassable
{
    /**
     * Accept a padding specification
     * @param self|string|null $spec
     * @return static|null
     * @throws SafetyCommonException
     */
    public static function accept(self|string|null $spec) : ?static
    {
        if ($spec === null) return null;
        if ($spec instanceof self) return $spec;

        return static::initialize($spec);
    }


    /**
     * Initialize a new padding schema
     * @param string $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(string $typeClass) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specInitialize();
    }


    /**
     * Specific initialization
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function specInitialize() : static;
}
