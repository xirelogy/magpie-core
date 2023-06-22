<?php

namespace Magpie\Cryptos\Paddings;

use Exception;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\WrongPaddingCryptoException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Log;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Str;

/**
 * Padding schema
 */
abstract class Padding implements TypeClassable
{
    /**
     * @var bool Will throw exception when decode error
     */
    protected bool $isThrowOnDecodeError = true;


    /**
     * Encode a payload, applying the padding
     * @param string $payload
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function encode(string $payload) : string;


    /**
     * Decode a payload, removing the padding
     * @param string $payload
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public abstract function decode(string $payload) : string;


    /**
     * Handle error while decoding a payload
     * @param string $payload
     * @param string|null $message
     * @return string
     * @throws CryptoException
     */
    protected function handleDecodeError(string $payload, ?string $message = null) : string
    {
        $ex = !Str::isNullOrEmpty($message) ? new Exception($message) : null;
        if ($this->isThrowOnDecodeError) throw new WrongPaddingCryptoException(static::getTypeClass(), previous: $ex);

        Log::warning('Wrong padding', [$message]);
        return $payload;
    }


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
     */
    protected abstract static function specInitialize() : static;
}