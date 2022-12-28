<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Numerals;

/**
 * Interface for RSA asymmetric key implementation
 * @internal
 */
interface ImplRsaAsymmKey extends ImplAsymmKey
{
    /**
     * @inheritDoc
     */
    public function getPublic() : ImplRsaAsymmKey;


    /**
     * Modulus: `n`
     * @return Numerals|null
     */
    public function getN() : ?Numerals;


    /**
     * Public exponent: `e`
     * @return Numerals|null
     */
    public function getE() : ?Numerals;


    /**
     * Private exponent: `d`
     * @return Numerals|null
     */
    public function getD() : ?Numerals;


    /**
     * First prime: `p`
     * @return Numerals|null
     */
    public function getP() : ?Numerals;


    /**
     * Second prime: `q`
     * @return Numerals|null
     */
    public function getQ() : ?Numerals;


    /**
     * First exponent: `d mod (p - 1)`
     * @return Numerals|null
     */
    public function getDmp1() : ?Numerals;


    /**
     * Second exponent: `d mod (q - 1)`
     * @return Numerals|null
     */
    public function getDmq1() : ?Numerals;


    /**
     * Coefficient: `(inv q) mod p`
     * @return Numerals|null
     */
    public function getIqmp() : ?Numerals;
}