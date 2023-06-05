<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Rsa;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKeyGenerator;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplRsaAsymmKeyGenerator;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * RSA private key generator
 */
abstract class RsaPrivateKeyGenerator extends CommonPrivateKeyGenerator
{
    /**
     * Default number of bits
     */
    public const DEFAULT_NUM_BITS = 1024;
    /**
     * @var ImplRsaAsymmKeyGenerator Underlying implementation
     */
    protected ImplRsaAsymmKeyGenerator $impl;


    /**
     * Constructor
     * @param ImplRsaAsymmKeyGenerator $impl
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function __construct(ImplRsaAsymmKeyGenerator $impl)
    {
        $this->impl = $impl;
        $this->impl->setNumBits(self::DEFAULT_NUM_BITS);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return RsaPrivateKey::TYPECLASS;
    }


    /**
     * Specify the number of bits
     * @param int $numBits
     * @return $this
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function withNumBits(int $numBits) : static
    {
        $this->getImpl()->setNumBits($numBits);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function go() : RsaPrivateKey
    {
        $ret = parent::go();

        if (!$ret instanceof RsaPrivateKey) throw new NotOfTypeException($ret, RsaPrivateKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function getImpl() : ImplRsaAsymmKeyGenerator
    {
        return $this->impl;
    }
}