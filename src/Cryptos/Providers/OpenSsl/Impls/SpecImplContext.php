<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPublicKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Key;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplEcCurve;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplAsymmKey;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplAsymmKeyGenerator;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplEcCurve;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplEcCurveMap;
use Magpie\Cryptos\Providers\OpenSsl\SpecContext;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Objects\BinaryData;

/**
 * Specific context to support implementation details for OpenSSL
 * @internal
 */
#[FactoryTypeClass(SpecImplContext::TYPECLASS, ImplContext::class)]
class SpecImplContext extends ImplContext
{
    /**
     * Current type class
     */
    public const TYPECLASS = SpecContext::TYPECLASS;


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function generateRandom(int $numBits) : BinaryData
    {
        $numBytes = intval(floor($numBits / 8));
        $ret = openssl_random_pseudo_bytes($numBytes);

        return BinaryData::fromBinary($ret);
    }


    /**
     * @inheritDoc
     */
    public function parseAsymmetricKeyFromBinary(BinaryBlockContent $source, ?string $password, ?bool $isPrivate) : ?Key
    {
        $fallbackType = '';

        if ($source->type === null) {
            if ($isPrivate === null) return null;

            $fallbackType = $isPrivate ? 'PRIVATE KEY' : 'PUBLIC KEY';
        }

        $effType = $source->type ?? $fallbackType;
        $pemData = ImportExport::formatAsOpenSslPem($source->data, $effType);

        return match ($effType) {
            'PUBLIC KEY',
                => $this->parseAsymmetricPublicKey($pemData, $password),
            'PRIVATE KEY',
            'ENCRYPTED PRIVATE KEY',
                => $this->parseAsymmetricPrivateKey($pemData, $password),
            default,
                => null,
        };
    }


    /**
     * Parse and handle asymmetric public key
     * @param string $pemData
     * @param string|null $password
     * @return PublicKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function parseAsymmetricPublicKey(string $pemData, ?string $password) : PublicKey
    {
        _used($password);

        $key = ErrorHandling::execute(fn () => openssl_pkey_get_public($pemData));
        $implKey = SpecImplAsymmKey::initializeFromKey($key);

        return CommonPublicKey::_fromRaw($implKey->getAlgoTypeClass(), $implKey);
    }


    /**
     * Parse and handle asymmetric private key
     * @param string $pemData
     * @param string|null $password
     * @return PrivateKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function parseAsymmetricPrivateKey(string $pemData, ?string $password) : PrivateKey
    {
        $key = ErrorHandling::execute(fn () => openssl_pkey_get_private($pemData, $password));
        $implKey = SpecImplAsymmKey::initializeFromKey($key);

        return CommonPrivateKey::_fromRaw($implKey->getAlgoTypeClass(), $implKey);
    }


    /**
     * @inheritDoc
     */
    public function createAsymmetricKeyGenerator(string $algoTypeClass) : SpecImplAsymmKeyGenerator
    {
        return SpecImplAsymmKeyGenerator::initializeFrom($algoTypeClass);
    }


    /**
     * @inheritDoc
     */
    public function findEcCurveByName(string $name) : ?ImplEcCurve
    {
        $names = openssl_get_curve_names();
        if ($names === false) return null;

        if (!in_array($name, $names)) return null;

        $oid = SpecImplEcCurveMap::resolveOid($name) ?? throw new UnsupportedValueException($name, _l('OpenSSL EC curve OID'));

        return new SpecImplEcCurve($name, $oid);
    }


    /**
     * @inheritDoc
     */
    public function findEcCurveByOid(string $oid) : ?ImplEcCurve
    {
        $name = SpecImplEcCurveMap::resolveName($oid);
        if ($name === null) return null;

        return new SpecImplEcCurve($name, $oid);
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }
}