<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec\EcCurve;

/**
 * Interface for Elliptic Curve asymmetric key generator implementation
 * @internal
 */
interface ImplEcAsymmKeyGenerator extends ImplAsymmKeyGenerator
{
    /**
     * Set the elliptic curve
     * @param EcCurve $curve
     * @return void
     */
    public function setCurve(EcCurve $curve) : void;


    /**
     * @inheritDoc
     */
    public function go() : ImplEcAsymmKey;
}