<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Rsa;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPublicKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Impls\ImplRsaAsymmKey;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Packs\PackContext;

/**
 * RSA public key
 */
#[FactoryTypeClass(RsaPublicKey::TYPECLASS, PublicKey::class)]
class RsaPublicKey extends CommonPublicKey
{
    /**
     * Current type class
     */
    public const TYPECLASS = RsaPrivateKey::TYPECLASS;
    /**
     * @var ImplRsaAsymmKey Underlying object
     */
    protected ImplRsaAsymmKey $impl;


    /**
     * Constructor
     * @param ImplRsaAsymmKey $impl
     */
    protected function __construct(ImplRsaAsymmKey $impl)
    {
        parent::__construct();

        $this->impl = $impl;
    }


    /**
     * @inheritDoc
     */
    public function getAlgoTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Modulus: `n`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getN() : Numerals
    {
        return $this->getImpl()->getN() ?? throw new NullException();
    }


    /**
     * Public exponent: `e`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getE() : Numerals
    {
        return $this->getImpl()->getE() ?? throw new NullException();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->n = $this->getN();
        $ret->e = $this->getE();
    }


    /**
     * @inheritDoc
     */
    protected function getImpl() : ImplRsaAsymmKey
    {
        return $this->impl;
    }


    /**
     * @inheritDoc
     */
    protected static function onSpecificFromRaw(ImplAsymmKey $implKey) : static
    {
        if (!$implKey instanceof ImplRsaAsymmKey) throw new NotOfTypeException($implKey, ImplRsaAsymmKey::class);
        return new static($implKey);
    }
}