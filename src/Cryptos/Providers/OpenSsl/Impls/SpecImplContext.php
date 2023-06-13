<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\CommonPublicKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Key;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PrivateKey;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\PublicKey;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Encodings\Pem;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplEcCurve;
use Magpie\Cryptos\Impls\ImplSymmCipher;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplAsymmKey;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplAsymmKeyGenerator;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm\SpecImplEcCurve;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Symm\SpecImplSymmAlgorithms;
use Magpie\Cryptos\Providers\OpenSsl\Impls\Symm\SpecImplSymmCipher;
use Magpie\Cryptos\Providers\OpenSsl\SpecContext;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
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
    public function createSymmetricCipher(string $algoTypeClass, ?int $blockNumBits) : ImplSymmCipher
    {
        $openSslAlgoTypeClass = SpecImplSymmAlgorithms::translateAlgorithm($algoTypeClass);
        $setup = SpecImplSymmAlgorithms::getAlgorithm($openSslAlgoTypeClass);

        if ($setup->hasMultiBlockSize) {
            if ($blockNumBits === null) throw new MissingArgumentException('blockNumBits');
            if (!array_key_exists($blockNumBits, $setup->blocks)) throw new UnsupportedValueException($blockNumBits, _l('block size'));
            $setupBlock = $setup->blocks[$blockNumBits];
        } else {
            if ($blockNumBits !== null) throw new UnsupportedException(_l('Block size cannot be specified'));
            $setupBlock = iter_first($setup->blocks);
        }

        return new SpecImplSymmCipher($algoTypeClass, $openSslAlgoTypeClass, $setup->hasMultiBlockSize, $setupBlock);
    }


    /**
     * @inheritDoc
     */
    public function parseAsymmetricKey(CryptoFormatContent $source, ?bool $isPrivate) : Key
    {
        // Convert into a source recognizable by OpenSSL
        $expectedType = match ($isPrivate) {
            true => 'PRIVATE KEY',
            false => 'PUBLIC KEY',
            default => null,
        };
        $openSslSource = ImportExport::readAsOpenSslPem($source, $expectedType);

        if ($isPrivate === null) {
            // Public/private key not specified
            foreach (Pem::decode($openSslSource->pemData) as $pemBlock) {
                return match ($pemBlock->type) {
                    'PUBLIC KEY',
                        => $this->parseAsymmetricPublicKey($openSslSource),
                    'PRIVATE KEY',
                    'ENCRYPTED PRIVATE KEY',
                        => $this->parseAsymmetricPrivateKey($openSslSource),
                    default,
                        => throw new UnsupportedValueException($source),
                };
            }
        }

        return $isPrivate
            ? $this->parseAsymmetricPrivateKey($openSslSource)
            : $this->parseAsymmetricPublicKey($openSslSource)
            ;
    }


    /**
     * Parse and handle asymmetric public key
     * @param OpenSslCryptoContent $openSslSource
     * @return PublicKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function parseAsymmetricPublicKey(OpenSslCryptoContent $openSslSource) : PublicKey
    {
        $key = ErrorHandling::execute(fn () => openssl_pkey_get_public($openSslSource->pemData));
        $implKey = SpecImplAsymmKey::initializeFromKey($key);

        return CommonPublicKey::_fromRaw($implKey->getAlgoTypeClass(), $implKey);
    }


    /**
     * Parse and handle asymmetric private key
     * @param OpenSslCryptoContent $openSslSource
     * @return PrivateKey
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected function parseAsymmetricPrivateKey(OpenSslCryptoContent $openSslSource) : PrivateKey
    {
        $key = ErrorHandling::execute(fn () => openssl_pkey_get_private($openSslSource->pemData, $openSslSource->password));
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

        return new SpecImplEcCurve($name);
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }
}