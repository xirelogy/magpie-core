<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Codecs\ParserHosts\ArrayParserHost;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Chunkings\Chunking;
use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Paddings\Padding;
use Magpie\Cryptos\Concepts\BinaryProcessable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ErrorHandling;
use Magpie\Cryptos\Providers\OpenSsl\Impls\ImportExport;
use Magpie\Cryptos\Providers\OpenSsl\Impls\PemEncoding;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Objects\BinaryData;
use OpenSSLAsymmetricKey;

/**
 * Specific OpenSSL asymmetric key instance
 * @internal
 */
abstract class SpecImplAsymmKey implements ImplAsymmKey
{
    /**
     * @var OpenSSLAsymmetricKey Underlying key object
     */
    protected OpenSSLAsymmetricKey $inKey;
    /**
     * @var array Certificate details as queried
     */
    protected array $inDetails;


    /**
     * Constructor
     * @param OpenSSLAsymmetricKey $inKey
     * @param array $inDetails
     * @throws ArgumentException
     */
    protected function __construct(OpenSSLAsymmetricKey $inKey, array $inDetails)
    {
        $this->inKey = $inKey;
        $this->inDetails = $inDetails;

        if (!array_key_exists('bits', $inDetails)) throw new MissingArgumentException('bits');
    }


    /**
     * @inheritDoc
     */
    public function getNumBits() : int
    {
        return $this->inDetails['bits'];
    }


    /**
     * @inheritDoc
     */
    public function preparePublicKeyEncryption(?Padding $padding, ?Chunking $chunking, ?int &$maxSize = null) : BinaryProcessable
    {
        $inPadding = SpecImplPaddings::translatePadding($padding);
        $maxSize = SpecImplPaddings::calculateChunkSize($this->getNumBits(), $inPadding, true);

        return new class($this->inKey, $inPadding) implements BinaryProcessable {
            /**
             * Constructor
             * @param OpenSSLAsymmetricKey $inKey
             * @param int $inPadding
             */
            public function __construct(
                protected OpenSSLAsymmetricKey $inKey,
                protected int $inPadding,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function process(string $input) : string
            {
                $output = '';
                $isSuccess = ErrorHandling::execute(function () use($input, &$output) {
                    return openssl_public_encrypt($input, $output, $this->inKey, $this->inPadding);
                });
                if (!$isSuccess) throw ErrorHandling::captureError();

                return $output;
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function preparePrivateKeyDecryption(?Padding $padding, ?Chunking $chunking, ?int &$maxSize = null) : BinaryProcessable
    {
        $inPadding = SpecImplPaddings::translatePadding($padding);
        $maxSize = SpecImplPaddings::calculateChunkSize($this->getNumBits(), $inPadding, false);

        return new class($this->inKey, $inPadding) implements BinaryProcessable {
            /**
             * Constructor
             * @param OpenSSLAsymmetricKey $inKey
             * @param int $inPadding
             */
            public function __construct(
                protected OpenSSLAsymmetricKey $inKey,
                protected int $inPadding,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function process(string $input) : string
            {
                $output = '';
                $isSuccess = ErrorHandling::execute(function () use($input, &$output) {
                    return openssl_private_decrypt($input, $output, $this->inKey, $this->inPadding);
                });
                if (!$isSuccess) throw ErrorHandling::captureError();

                return $output;
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function privateSign(string $plaintext, string $hashTypeClass) : BinaryData
    {
        $inHashType = SpecImplAsymmSignature::translateHash($hashTypeClass);

        $output = '';
        ErrorHandling::execute(function () use ($plaintext, &$output, $inHashType) {
            return openssl_sign($plaintext, $output, $this->inKey, $inHashType);
        });

        return BinaryData::fromBinary($output);
    }


    /**
     * @inheritDoc
     */
    public function publicVerify(string $plaintext, BinaryData $signature, string $hashTypeClass) : bool
    {
        $inHashType = SpecImplAsymmSignature::translateHash($hashTypeClass);

        $ret = ErrorHandling::execute(fn () => openssl_verify($plaintext, $signature->asBinary(), $this->inKey, $inHashType));

        return $ret === 1;
    }


    /**
     * @inheritDoc
     */
    public function getPublic() : SpecImplAsymmKey
    {
        $pubKey = $this->inDetails['key'] ?? throw new InvalidDataException();

        $retKey = ErrorHandling::execute(fn () => openssl_pkey_get_public($pubKey));

        return static::initializeFromKey($retKey);
    }


    /**
     * @inheritDoc
     */
    public function export(string $exportName, array $options) : string
    {
        $isPublicKey = str_contains($exportName, 'PUBLIC KEY');

        [$exportOption, $exportPassword] = ImportExport::checkExportOptions($options, !$isPublicKey);

        if ($isPublicKey) {
            // Export public key
            return PemEncoding::reformat($this->inDetails['key'], $exportName, $exportOption);
        } else {
            // Export private key
            $output = '';
            ErrorHandling::execute(function () use(&$output, $exportPassword) {
                return openssl_pkey_export($this->inKey, $output, $exportPassword);
            });

            return PemEncoding::reformat($output, $exportName, $exportOption);
        }
    }


    /**
     * Initialize from key
     * @param OpenSSLAsymmetricKey $inKey
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initializeFromKey(OpenSSLAsymmetricKey $inKey) : static
    {
        $inDetails = ErrorHandling::execute(fn () => openssl_pkey_get_details($inKey));

        $parserHost = new ArrayParserHost($inDetails);
        $openSslTypeClass = $parserHost->requires('type');

        $className = ClassFactory::resolve($openSslTypeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitializeFromKey($inKey, $inDetails);
    }


    /**
     * Initialize specifically from key
     * @param OpenSSLAsymmetricKey $inKey
     * @param array $inDetails
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected abstract static function specificInitializeFromKey(OpenSSLAsymmetricKey $inKey, array $inDetails) : static;
}