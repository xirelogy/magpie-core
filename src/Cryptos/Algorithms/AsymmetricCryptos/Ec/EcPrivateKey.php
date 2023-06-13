<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec;

use Exception;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplEcAsymmKey;
use Magpie\Cryptos\Impls\ImplEcAsymmKeyGenerator;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Packs\PackContext;

/**
 * Elliptic Curve private key
 */
#[FactoryTypeClass(EcPrivateKey::TYPECLASS, PrivateKey::class)]
class EcPrivateKey extends CommonPrivateKey
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'ec';
    /**
     * @var ImplEcAsymmKey Underlying object
     */
    protected ImplEcAsymmKey $impl;


    /**
     * Constructor
     * @param ImplEcAsymmKey $impl
     */
    protected function __construct(ImplEcAsymmKey $impl)
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
     * Corresponding curve
     * @return EcCurve
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function getCurve() : EcCurve
    {
        return EcCurve::_fromRaw($this->getImpl()->getCurve());
    }


    /**
     * Public X-coordinate (x)
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getX() : Numerals
    {
        return $this->getImpl()->getX() ?? throw new NullException();
    }


    /**
     * Public Y-coordinate (y)
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getY() : Numerals
    {
        return $this->getImpl()->getY() ?? throw new NullException();
    }


    /**
     * Private component (d)
     * @return Numerals
     * @throws SafetyCommonException
     */
    public function getD() : Numerals
    {
        return $this->getImpl()->getD() ?? throw new NullException();
    }


    /**
     * @inheritDoc
     */
    public function isPairedWith(PublicKey $publicKey) : bool
    {
        if (!$publicKey instanceof EcPublicKey) return false;

        try {
            if ($this->getCurve()->getName() != $publicKey->getCurve()->getName()) return false;
            if ($this->getX()->asHex() != $publicKey->getX()->asHex()) return false;
            if ($this->getY()->asHex() != $publicKey->getY()->asHex()) return false;
        } catch (Exception) {
            return false;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function getPublicKey() : EcPublicKey
    {
        $ret = parent::getPublicKey();

        if (!$ret instanceof EcPublicKey) throw new NotOfTypeException($ret, EcPublicKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->curve = $this->getCurve();
        $ret->x = $this->getX();
        $ret->y = $this->getY();
        $ret->d = $this->getD();
    }


    /**
     * @inheritDoc
     */
    protected function getImpl() : ImplEcAsymmKey
    {
        return $this->impl;
    }


    /**
     * @inheritDoc
     */
    public static function generate(?Context $context = null) : EcPrivateKeyGenerator
    {
        $context = $context ?? Context::getDefault();

        $implContext = ImplContext::initialize($context->getTypeClass());
        $implGenerator = $implContext->createAsymmetricKeyGenerator(static::TYPECLASS);

        return new class($implGenerator) extends EcPrivateKeyGenerator {
            /**
             * Constructor
             * @param ImplEcAsymmKeyGenerator $impl
             */
            public function __construct(ImplEcAsymmKeyGenerator $impl)
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
        if (!$implKey instanceof ImplEcAsymmKey) throw new NotOfTypeException($implKey, ImplEcAsymmKey::class);
        return new static($implKey);
    }
}