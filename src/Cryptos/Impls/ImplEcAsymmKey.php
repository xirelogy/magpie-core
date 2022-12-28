<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for Elliptic Curve asymmetric key implementation
 * @internal
 */
interface ImplEcAsymmKey extends ImplAsymmKey
{
    /**
     * Corresponding curve
     * @return ImplEcCurve
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function getCurve() : ImplEcCurve;


    /**
     * Public X-coordinate (x)
     * @return Numerals|null
     */
    public function getX() : ?Numerals;


    /**
     * Public Y-coordinate (y)
     * @return Numerals|null
     */
    public function getY() : ?Numerals;


    /**
     * Private component (d)
     * @return Numerals|null
     */
    public function getD() : ?Numerals;
}