<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKeyGenerator;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;

/**
 * Specific OpenSSL asymmetric key generator instance
 * @internal
 */
abstract class SpecImplAsymmKeyGenerator implements ImplAsymmKeyGenerator
{
    /**
     * @var array OpenSSL key generator options
     */
    protected array $options = [];


    /**
     * Constructor
     * @param int $openSslType
     */
    protected function __construct(int $openSslType)
    {
        $this->options['private_key_type'] = $openSslType;
    }


    /**
     * @inheritDoc
     */
    public function go() : SpecImplAsymmKey
    {
        $key = ErrorHandling::execute(fn () => openssl_pkey_new($this->options));

        return SpecImplAsymmKey::initializeFromKey($key);
    }


    /**
     * Initialize from specific algorithm type class
     * @param string $algoTypeClass
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initializeFrom(string $algoTypeClass) : static
    {
        $className = ClassFactory::resolve($algoTypeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitializeFrom();
    }


    /**
     * Specific initialize from specific algorithm type class
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specificInitializeFrom() : static;
}