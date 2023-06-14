<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CommonCipherAlgoTypeClass;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Traits\StaticClass;

/**
 * Symmetric cipher algorithm information for OpenSSL
 * @internal
 */
class SpecImplSymmAlgorithms
{
    use StaticClass;

    /**
     * @var array<string, AlgorithmSetup>|null Map of supported algorithms
     */
    protected static ?array $algorithms = null;


    /**
     * Translate and accept algorithm typeclass into OpenSSL's algorithm name
     * @param string $algorithmTypeClass
     * @return string
     * @throws SafetyCommonException
     */
    public static function translateAlgorithm(string $algorithmTypeClass) : string
    {
        return match ($algorithmTypeClass) {
            CommonCipherAlgoTypeClass::AES => 'aes',
            CommonCipherAlgoTypeClass::BLOWFISH => 'bf',
            CommonCipherAlgoTypeClass::CAST5 => 'cast5',
            CommonCipherAlgoTypeClass::DES => 'des',
            CommonCipherAlgoTypeClass::TRIPLE_DES_EDE => 'des-ede',
            CommonCipherAlgoTypeClass::TRIPLE_DES_EDE3 => 'des-ede3',
            default => throw new UnsupportedValueException($algorithmTypeClass),
        };
    }


    /**
     * Check given key size is valid for given algorithm/block size
     * @param string $algorithm
     * @param int $blockNumBits
     * @param int $keyNumBits
     * @return bool
     */
    public static function checkAlgorithmKeySize(string $algorithm, int $blockNumBits, int $keyNumBits) : bool
    {
        // Must be multiple of 8 bits (1 byte)
        if (($keyNumBits % 8) !== 0) return false;

        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($algorithm) {
            case 'aes':
            case 'aria':
            case 'camellia':
                // Algorithms where key size = block size
                return $keyNumBits === $blockNumBits;

            case 'bf':
                // Blowfish support 32 - 448 bits
                return (32 <= $keyNumBits) && ($keyNumBits <= 448);

            case 'cast5':
                // CAST5 support 40 - 128 bits
                return (40 <= $keyNumBits) && ($keyNumBits <= 128);

            case 'des':
            case 'desx':
                // DES uses fixed 56 bits (1 bit parity every 7 bits)
                return $keyNumBits === 56 || $keyNumBits === 64;

            case 'des-ede':
                // 2-key Triple-DES uses fixed 112 bits (1 bit parity every 7 bits)
                return $keyNumBits === 112 || $keyNumBits === 128;

            case 'des-ede3':
                // 3-key Triple-DES uses fixed 168 bits (1 bit parity every 7 bits)
                return $keyNumBits === 168 || $keyNumBits === 192;

            default:
                // If not recognized here, not supported!
                return false;
        }
    }


    /**
     * Get algorithm setup
     * @param string $algorithm
     * @return AlgorithmSetup
     * @throws SafetyCommonException
     */
    public static function getAlgorithm(string $algorithm) : AlgorithmSetup
    {
        $algorithms = static::getAlgorithms();
        if (!array_key_exists($algorithm, $algorithms)) throw new UnsupportedValueException($algorithm, _l('cipher algorithm'));

        return $algorithms[$algorithm];
    }


    /**
     * Get the current map of supported algorithms
     * @return array<string, AlgorithmSetup>
     * @throws SafetyCommonException
     */
    protected static function getAlgorithms() : array
    {
        if (static::$algorithms === null) {
            static::$algorithms = [];

            foreach (static::decodeAlgorithms() as $algoParams) {
                [$algorithmName, $hasMultiBlockSize, $blockSize, $mode] = $algoParams;
                $algoSetup = static::$algorithms[$algorithmName] ?? new AlgorithmSetup($algorithmName, $hasMultiBlockSize);

                $algoBlockSetup = $algoSetup->blocks[$blockSize] ?? new AlgorithmBlockSetup($blockSize);
                $algoBlockSetup->modes[$mode] = $mode;
                $algoSetup->blocks[$blockSize] = $algoBlockSetup;

                static::$algorithms[$algorithmName] = $algoSetup;
            }
        }

        return static::$algorithms;
    }


    /**
     * Read and decode OpenSSL's list of algorithms
     * @return iterable<array{0: string, 1: bool, 2: int, 3: string|null}>
     * @throws SafetyCommonException
     */
    protected static function decodeAlgorithms() : iterable
    {
        // Matching headers, and whether there is block size specification, mode specification

        // Decode matcher to match prefix with:
        // 1) Algorithm name as in OpenSSL
        // 2) Whether multiple block size specification available, or a fixed block size if it is a number
        // 3) Whether multiple mode specification available

        $headers = [
            'aes-' => ['aes', true, true],
            'aria-' => ['aria', true, true],
            'bf-' => ['bf', 64, true],
            'camellia-' => ['camellia', true, true],
            'cast5-' => ['cast5', 64, true],
            'des-ede3-' => ['des-ede3', 64, true],
            'des-ede-' => ['des-ede', 64, true],
            'desx-' => ['desx', 64, true],
            'des-' => ['des', 64, true],
        ];

        foreach (static::readAlgorithms() as $algorithm) {
            foreach ($headers as $headerKey => $headerSetup) {
                if (str_starts_with($algorithm, $headerKey)) {
                    $headerLength = strlen($headerKey);
                    $readBlockSize = null;

                    [$algorithmName, $hasBlockSize, $hasMode] = $headerSetup;
                    $algorithmSuffix = substr($algorithm, $headerLength);

                    // Try to handle block size
                    if ($hasBlockSize === true) {
                        $dashPos = strpos($algorithmSuffix, '-');
                        if ($dashPos !== false) {
                            $readBlockSize = IntegerParser::create()->withStrict()->parse(substr($algorithmSuffix, 0, $dashPos));
                            $algorithmSuffix = substr($algorithmSuffix, $dashPos + 1);
                        }
                    } else {
                        $readBlockSize = $hasBlockSize;
                    }

                    // Try to handle mode
                    $readMode = null;
                    if ($hasMode) {
                        $readMode = strtolower($algorithmSuffix);
                    }

                    yield [$algorithmName, $hasBlockSize === true, $readBlockSize, $readMode];
                    continue 2;
                }
            }
        }
    }


    /**
     * Read OpenSSL's list of algorithms
     * @return iterable<string>
     */
    protected static function readAlgorithms() : iterable
    {
        foreach (openssl_get_cipher_methods() as $method) {

            // Encryption + HMAC is not supported via symmetric cipher interface
            if (str_ends_with($method, '-hmac-sha1')) continue;
            if (str_ends_with($method, '-hmac-sha256')) continue;
            if (str_ends_with($method, '-hmac-md5')) continue;

            // id-aes / id-smime not yet supported
            if (str_starts_with($method, 'id-aes128-')) continue;
            if (str_starts_with($method, 'id-aes192-')) continue;
            if (str_starts_with($method, 'id-aes256-')) continue;
            if (str_starts_with($method, 'id-smime-alg-')) continue;

            // blacklist rc2/rc4
            if (str_starts_with($method, 'rc2-')) continue;
            if (str_starts_with($method, 'rc4-')) continue;
            if ($method === 'rc4') continue;

            // FIXME: chacha blocked
            if (str_starts_with($method, 'chacha20')) continue;

            yield $method;
        }
    }
}