<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Rsa\RsaPrivateKey;
use Magpie\Cryptos\Impls\ImplRsaAsymmKey;
use Magpie\Cryptos\Numerals;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use OpenSSLAsymmetricKey;

/**
 * Specific RSA OpenSSL asymmetric key instance
 * @internal
 */
#[FactoryTypeClass(OPENSSL_KEYTYPE_RSA, SpecImplAsymmKey::class)]
class SpecImplRsaAsymmKey extends SpecImplAsymmKey implements ImplRsaAsymmKey
{
    /**
     * @inheritDoc
     */
    public function getAlgoTypeClass() : string
    {
        return RsaPrivateKey::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function getN() : ?Numerals
    {
        return $this->getRsaNumeral('n');
    }


    /**
     * @inheritDoc
     */
    public function getE() : ?Numerals
    {
        return $this->getRsaNumeral('e');
    }


    /**
     * @inheritDoc
     */
    public function getD() : ?Numerals
    {
        return $this->getRsaNumeral('d');
    }


    /**
     * @inheritDoc
     */
    public function getP() : ?Numerals
    {
        return $this->getRsaNumeral('p');
    }


    /**
     * @inheritDoc
     */
    public function getQ() : ?Numerals
    {
        return $this->getRsaNumeral('q');
    }


    /**
     * @inheritDoc
     */
    public function getDmp1() : ?Numerals
    {
        return $this->getRsaNumeral('dmp1');
    }


    /**
     * @inheritDoc
     */
    public function getDmq1() : ?Numerals
    {
        return $this->getRsaNumeral('dmq1');
    }


    /**
     * @inheritDoc
     */
    public function getIqmp() : ?Numerals
    {
        return $this->getRsaNumeral('iqmp');
    }


    /**
     * @inheritDoc
     */
    public function getPublic() : SpecImplRsaAsymmKey
    {
        $ret = parent::getPublic();

        if (!$ret instanceof SpecImplRsaAsymmKey) throw new NotOfTypeException($ret, SpecImplRsaAsymmKey::class);

        return $ret;
    }


    /**
     * Extract numerals from RSA
     * @param string $index
     * @return Numerals|null
     */
    protected function getRsaNumeral(string $index) : ?Numerals
    {
        if (!array_key_exists($index, $this->inDetails['rsa'])) return null;

        return Numerals::fromBinary($this->inDetails['rsa'][$index]);
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitializeFromKey(OpenSSLAsymmetricKey $inKey, array $inDetails) : static
    {
        return new static($inKey, $inDetails);
    }
}