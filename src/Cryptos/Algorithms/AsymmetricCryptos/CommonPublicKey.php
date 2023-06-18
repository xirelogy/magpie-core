<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\NoChunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Algorithms\Traits\CommonAsymmKey;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;

/**
 * Public key in the asymmetric key pair (common implementation)
 */
abstract class CommonPublicKey extends PublicKey
{
    use CommonAsymmKey;

    /**
     * @inheritDoc
     */
    public function encrypt(BinaryData|string $plaintext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : BinaryData
    {
        $plaintext = BinaryData::acceptBinary($plaintext)->asBinary();
        $padding = Padding::accept($padding);
        $chunking = Chunking::accept($chunking) ?? new NoChunking();

        $crypto = $this->getImpl()->preparePublicKeyEncryption($padding, $chunking, $maxSize);
        return BinaryData::fromBinary($chunking->encrypt($crypto, $plaintext, $maxSize));
    }


    /**
     * @inheritDoc
     */
    public function verify(BinaryData|string $plaintext, BinaryData $signature, Hasher|string|null $hashAlgorithm = null) : bool
    {
        $plaintext = BinaryData::acceptBinary($plaintext)->asBinary();
        $hashAlgorithm = $hashAlgorithm ?? CommonHashTypeClass::SHA1;
        $hashTypeClass = $hashAlgorithm instanceof Hasher ? $hashAlgorithm->getTypeClass() : $hashAlgorithm;

        return $this->getImpl()->publicVerify($plaintext, $signature, $hashTypeClass);
    }


    /**
     * @inheritDoc
     */
    protected function getExportName() : string
    {
        return 'PUBLIC KEY';
    }


    /**
     * @inheritDoc
     * @internal
     */
    public static function _fromRaw(string $algoTypeClass, ImplAsymmKey $implKey) : static
    {
        $className = ClassFactory::resolve($algoTypeClass, PublicKey::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::onSpecificFromRaw($implKey);
    }


    /**
     * Create specific key instance
     * @param ImplAsymmKey $implKey
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function onSpecificFromRaw(ImplAsymmKey $implKey) : static;
}