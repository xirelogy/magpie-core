<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Rsa\RsaPrivateKey;
use Magpie\Cryptos\Impls\ImplRsaAsymmKeyGenerator;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;

/**
 * Specific RSA OpenSSL asymmetric key generator instance
 * @internal
 */
#[FactoryTypeClass(RsaPrivateKey::TYPECLASS, SpecImplAsymmKeyGenerator::class)]
class SpecImplRsaAsymmKeyGenerator extends SpecImplAsymmKeyGenerator implements ImplRsaAsymmKeyGenerator
{
    /**
     * @inheritDoc
     */
    public function setNumBits(int $numBits) : void
    {
        $this->options['private_key_bits'] = $numBits;
    }


    /**
     * @inheritDoc
     */
    public function go() : SpecImplRsaAsymmKey
    {
        $ret = parent::go();

        if (!$ret instanceof SpecImplRsaAsymmKey) throw new NotOfTypeException($ret, SpecImplRsaAsymmKey::class);

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitializeFrom() : static
    {
        return new static(OPENSSL_KEYTYPE_RSA);
    }
}