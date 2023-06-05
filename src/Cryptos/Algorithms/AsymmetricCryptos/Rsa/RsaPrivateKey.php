<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Rsa;

use Exception;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplRsaAsymmKey;
use Magpie\Cryptos\Impls\ImplRsaAsymmKeyGenerator;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Packs\PackContext;

/**
 * RSA private key
 */
#[FactoryTypeClass(RsaPrivateKey::TYPECLASS, PrivateKey::class)]
class RsaPrivateKey extends CommonPrivateKey
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'rsa';
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
     * Private exponent: `d`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getD() : Numerals
    {
        return $this->getImpl()->getD() ?? throw new NullException();
    }


    /**
     * First prime: `p`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getP() : Numerals
    {
        return $this->getImpl()->getP() ?? throw new NullException();
    }


    /**
     * Second prime: `q`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getQ() : Numerals
    {
        return $this->getImpl()->getQ() ?? throw new NullException();
    }


    /**
     * First exponent: `d mod (p - 1)`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getDmp1() : Numerals
    {
        return $this->getImpl()->getDmp1() ?? throw new NullException();
    }


    /**
     * Second exponent: `d mod (q - 1)`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getDmq1() : Numerals
    {
        return $this->getImpl()->getDmq1() ?? throw new NullException();
    }


    /**
     * Coefficient: `(inv q) mod p`
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getIqmp() : Numerals
    {
        return $this->getImpl()->getIqmp() ?? throw new NullException();
    }


    /**
     * @inheritDoc
     */
    public function isPairedWith(PublicKey $publicKey) : bool
    {
        if (!$publicKey instanceof RsaPublicKey) return false;

        try {
            if ($this->getN()->asHex() != $publicKey->getN()->asHex()) return false;
            if ($this->getE()->asHex() != $publicKey->getE()->asHex()) return false;
        } catch (Exception) {
            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function getPublicKey() : RsaPublicKey
    {
        $ret = parent::getPublicKey();

        if (!$ret instanceof RsaPublicKey) throw new NotOfTypeException($ret, RsaPublicKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->n = $this->getN();
        $ret->e = $this->getE();
        $ret->d = $this->getD();
        $ret->p = $this->getP();
        $ret->q = $this->getQ();
        $ret->dmp1 = $this->getDmp1();
        $ret->dmq1 = $this->getDmq1();
        $ret->iqmp = $this->getIqmp();
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
    public static function generate(?Context $context = null) : RsaPrivateKeyGenerator
    {
        $context = $context ?? Context::getDefault();

        $implContext = ImplContext::initialize($context->getTypeClass());
        $implGenerator = $implContext->createAsymmetricKeyGenerator(static::TYPECLASS);

        return new class($implGenerator) extends RsaPrivateKeyGenerator {
            /**
             * Constructor
             * @param ImplRsaAsymmKeyGenerator $impl
             */
            public function __construct(ImplRsaAsymmKeyGenerator $impl)
            {
                parent::__construct($impl);
            }
        };
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