<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec\EcCurve;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec\EcPrivateKey;
use Magpie\Cryptos\Impls\ImplEcAsymmKeyGenerator;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * Specific Elliptic Curve OpenSSL asymmetric key generator instance
 * @internal
 */
#[FactoryTypeClass(EcPrivateKey::TYPECLASS, SpecImplAsymmKeyGenerator::class)]
class SpecImplEcAsymmKeyGenerator extends SpecImplAsymmKeyGenerator implements ImplEcAsymmKeyGenerator
{
    /**
     * @inheritDoc
     */
    public function setCurve(EcCurve $curve) : void
    {
        $this->options['curve_name'] = $curve->getName();
    }


    /**
     * @inheritDoc
     */
    public function go() : SpecImplEcAsymmKey
    {
        $ret = parent::go();

        if (!$ret instanceof SpecImplEcAsymmKey) throw new NotOfTypeException($ret, SpecImplEcAsymmKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitializeFrom() : static
    {
        return new static(OPENSSL_KEYTYPE_EC);
    }
}