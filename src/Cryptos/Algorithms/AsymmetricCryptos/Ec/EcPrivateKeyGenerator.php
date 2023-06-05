<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKeyGenerator;
use Magpie\Cryptos\Impls\ImplEcAsymmKeyGenerator;
use Magpie\Exceptions\NotOfTypeException;

/**
 * Elliptic Curve private key generator
 */
class EcPrivateKeyGenerator extends CommonPrivateKeyGenerator
{
    /**
     * @var ImplEcAsymmKeyGenerator Underlying implementation
     */
    protected ImplEcAsymmKeyGenerator $impl;


    /**
     * Constructor
     * @param ImplEcAsymmKeyGenerator $impl
     */
    protected function __construct(ImplEcAsymmKeyGenerator $impl)
    {
        $this->impl = $impl;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return EcPrivateKey::TYPECLASS;
    }


    /**
     * Specify the elliptic curve
     * @param EcCurve $curve
     * @return $this
     */
    public function withCurve(EcCurve $curve) : static
    {
        $this->impl->setCurve($curve);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function go() : EcPrivateKey
    {
        $ret = parent::go();

        if (!$ret instanceof EcPrivateKey) throw new NotOfTypeException($ret, EcPrivateKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function getImpl() : ImplEcAsymmKeyGenerator
    {
        return $this->impl;
    }
}