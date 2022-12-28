<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use Magpie\Cryptos\Contents\CryptoContent;
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
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\BinaryDataProvidable;
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
    public function parseAsymmetricKey(CryptoContent|BinaryDataProvidable|string $source, bool $isPrivate) : SpecImplAsymmKey
    {
        try {
            [$data, $password] = ImportExport::readAsPem($source, $isPrivate);
        } catch (PersistenceException|StreamException $ex) {
            throw new OperationFailedException(previous: $ex);
        }

        $data = PemEncoding::reformat($data, $isPrivate ? 'PRIVATE KEY' : 'PUBLIC KEY');

        $key = $isPrivate ?
            ErrorHandling::execute(fn () => openssl_pkey_get_private($data, $password)) :
            ErrorHandling::execute(fn () => openssl_pkey_get_public($data));

        return SpecImplAsymmKey::initializeFromKey($key);
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