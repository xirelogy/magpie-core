<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec\EcPrivateKey;
use Magpie\Cryptos\Impls\ImplEcAsymmKey;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NullException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use OpenSSLAsymmetricKey;

/**
 * Specific Elliptic Curve OpenSSL asymmetric key instance
 * @internal
 */
#[FactoryTypeClass(OPENSSL_KEYTYPE_EC, SpecImplAsymmKey::class)]
class SpecImplEcAsymmKey extends SpecImplAsymmKey implements ImplEcAsymmKey
{
    /**
     * @inheritDoc
     */
    public function getAlgoTypeClass() : string
    {
        return EcPrivateKey::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function getCurve() : SpecImplEcCurve
    {
        $curveName = $this->inDetails['ec']['curve_name'] ?? throw new NullException();
        $curveOid = $this->inDetails['ec']['curve_oid'] ?? throw new NullException();

        return new SpecImplEcCurve($curveName, $curveOid);
    }


    /**
     * @inheritDoc
     */
    public function getX() : ?Numerals
    {
        return $this->getEcNumeral('x');
    }


    /**
     * @inheritDoc
     */
    public function getY() : ?Numerals
    {
        return $this->getEcNumeral('y');
    }


    /**
     * @inheritDoc
     */
    public function getD() : ?Numerals
    {
        return $this->getEcNumeral('d');
    }


    /**
     * Extract numerals from EC
     * @param string $index
     * @return Numerals|null
     */
    protected function getEcNumeral(string $index) : ?Numerals
    {
        if (!array_key_exists($index, $this->inDetails['ec'])) return null;

        return Numerals::fromBinary($this->inDetails['ec'][$index]);
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitializeFromKey(OpenSSLAsymmetricKey $inKey, array $inDetails) : static
    {
        return new static($inKey, $inDetails);
    }
}