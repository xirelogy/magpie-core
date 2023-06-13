<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPublicKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Impls\ImplEcAsymmKey;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Packs\PackContext;

/**
 * Elliptic Curve public key
 */
#[FactoryTypeClass(EcPublicKey::TYPECLASS, PublicKey::class)]
class EcPublicKey extends CommonPublicKey
{
    /**
     * Current type class
     */
    public const TYPECLASS = EcPrivateKey::TYPECLASS;
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
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->curve = $this->getCurve();
        $ret->x = $this->getX();
        $ret->y = $this->getY();
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
    protected static function onSpecificFromRaw(ImplAsymmKey $implKey) : static
    {
        if (!$implKey instanceof ImplEcAsymmKey) throw new NotOfTypeException($implKey, ImplEcAsymmKey::class);
        return new static($implKey);
    }
}