<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\NoChunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Algorithms\Hashes\CommonHashTypeClass;
use Magpie\Cryptos\Algorithms\Hashes\Hasher;
use Magpie\Cryptos\Algorithms\Traits\CommonAsymmKey;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;

/**
 * Private key in the asymmetric key pair (common implementation)
 */
abstract class CommonPrivateKey extends PrivateKey
{
    use CommonAsymmKey;

    /**
     * @inheritDoc
     */
    public function decrypt(BinaryData|string $ciphertext, Padding|string|null $padding = null, Chunking|string|null $chunking = null) : string
    {
        $ciphertext = BinaryData::acceptBinary($ciphertext)->asBinary();
        $padding = Padding::accept($padding);
        $chunking = Chunking::accept($chunking) ?? new NoChunking();

        $crypto = $this->getImpl()->preparePrivateKeyDecryption($padding, $chunking, $maxSize);
        return $chunking->decrypt($crypto, $ciphertext, $maxSize);
    }


    /**
     * @inheritDoc
     */
    public function sign(BinaryData|string $plaintext, Hasher|string $hashAlgorithm = CommonHashTypeClass::SHA1) : BinaryData
    {
        $plaintext = BinaryData::acceptBinary($plaintext)->asBinary();
        $hashTypeClass = $hashAlgorithm instanceof Hasher ? $hashAlgorithm->getTypeClass() : $hashAlgorithm;

        return $this->getImpl()->privateSign($plaintext, $hashTypeClass);
    }


    /**
     * @inheritDoc
     */
    public function getPublicKey() : CommonPublicKey
    {
        $implKey = $this->getImpl()->getPublic();

        return CommonPublicKey::_fromRaw($this->getAlgoTypeClass(), $implKey);
    }


    /**
     * @inheritDoc
     */
    protected function getExportName() : string
    {
        return 'PRIVATE KEY';
    }


    /**
     * @inheritDoc
     * @internal
     */
    public static function _fromRaw(string $algoTypeClass, ImplAsymmKey $implKey) : static
    {
        $className = ClassFactory::resolve($algoTypeClass, PrivateKey::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::onSpecificFromRaw($implKey);
    }


    /**
     * Create specific key instance
     * @param ImplAsymmKey $implKey
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function onSpecificFromRaw(ImplAsymmKey $implKey) : static;
}