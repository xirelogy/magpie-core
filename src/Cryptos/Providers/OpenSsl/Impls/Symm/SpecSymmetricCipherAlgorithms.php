<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CommonCipherAlgoTypeClass;
use Magpie\Cryptos\Algorithms\SymmetricCryptos\CommonCipherMode;
use Magpie\Cryptos\Providers\ClosureSymmetricCipherAlgorithmInitializer;
use Magpie\Cryptos\Providers\SymmetricCipherAlgorithms;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\StaticClass;

/**
 * Collection of all supported symmetric cipher algorithms
 * @internal
 */
class SpecSymmetricCipherAlgorithms
{
    use StaticClass;


    /**
     * Register current provider
     * @return void
     */
    public static function register() : void
    {
        $initializer = ClosureSymmetricCipherAlgorithmInitializer::create(function () {
            static::initializeAlgorithms();
        });

        SymmetricCipherAlgorithms::registerInitializer($initializer);
    }


    /**
     * Check and initialize algorithms
     * @return void
     * @throws SafetyCommonException
     */
    protected static function initializeAlgorithms() : void
    {
        // Build configurations in cache
        /** @var array<string, OpenSslSymmetricAlgorithmConfig> $configs */
        $configs = [];
        foreach (static::getAlgorithmConfigs() as $config) {
            $configs[$config->algoTypeClass] = $config;
        }

        /** @var array<string, OpenSslSymmetricAlgorithmSeeder> $seeders */
        $seeders = [];

        foreach (static::getAlgorithmsUsing($configs) as $algorithm) {
            $algoTypeClass = $algorithm->algoTypeClass;
            $algoConfig = $configs[$algoTypeClass] ?? null;
            if ($algoConfig === null) continue;

            if (!array_key_exists($algoTypeClass, $seeders)) {
                $seeders[$algoTypeClass] = new OpenSslSymmetricAlgorithmSeeder($algoTypeClass, $algoConfig);
            }

            $seeders[$algoTypeClass]->register($algorithm);
        }

        // Finalize seeders and offer them
        foreach ($seeders as $seeder) {
            $seeder->stopRegister();
            SymmetricCipherAlgorithms::registerProvider($seeder);
        }
    }


    /**
     * Read and decode from list of OpenSSL supported algorithms
     * @param array<string, OpenSslSymmetricAlgorithmConfig> $configs
     * @return iterable<OpenSslSymmetricAlgorithmSpec>
     * @throws SafetyCommonException
     */
    protected static function getAlgorithmsUsing(array $configs) : iterable
    {
        foreach (openssl_get_cipher_methods() as $method) {
            // Prior to OpenSSL 1.1.1 string may be returned in mixed case, force all to be lowercase
            $method = strtolower($method);

            // Try to match for a configuration
            $algoConfig = static::matchConfig($configs, $method);
            if ($algoConfig === null) continue;

            // Handle block size, if any
            $blockSize = null;
            if ($algoConfig->isFallbackBlockSize()) {
                $blockSize = static::readFallbackBlock($method);
            } else if ($algoConfig->isExpectBlockSize()) {
                $data = static::readNextBlock($method);
                if ($data === null) continue;

                $blockSize = IntegerParser::create()->parse($data);
            }

            // Handle mode, if any
            $mode = null;
            if ($algoConfig->isExpectMode()) {
                $data = static::readNextBlock($method);
                if ($data === null) continue;

                $mode = StringParser::create()->parse($data);
                if (!static::isModeSupported($mode)) continue;
            }

            // Return result
            yield new OpenSslSymmetricAlgorithmSpec($algoConfig->algoTypeClass, $blockSize, $mode);
        }
    }


    /**
     * Try to match a configuration
     * @param array<string, OpenSslSymmetricAlgorithmConfig> $configs
     * @param string $method
     * @return OpenSslSymmetricAlgorithmConfig|null
     */
    protected static function matchConfig(array $configs, string &$method) : ?OpenSslSymmetricAlgorithmConfig
    {
        foreach ($configs as $config) {
            if ($config->ifOpenSSLMatched($method)) return $config;
        }

        return null;
    }


    /**
     * Read next block (optionally) as a block size
     * @param string $method
     * @return int|null
     */
    protected static function readFallbackBlock(string &$method) : ?int
    {
        $tryMethod = $method;
        $nextData = static::readNextBlock($tryMethod);
        if ($nextData === null) return null;

        $ret = Excepts::noThrow(fn () => IntegerParser::create()->parse($nextData));
        if ($ret === null) return null;

        $method = $tryMethod;
        return $ret;
    }


    /**
     * Read next block, separated by dash
     * @param string $method
     * @return string|null
     */
    protected static function readNextBlock(string &$method) : ?string
    {
        if ($method === '') return null;

        $dashPos = strpos($method, '-');

        if ($dashPos === false) {
            // All remaining data taken
            $data = $method;
            $method = '';
        } else {
            // Only up to dash taken
            $data = substr($method, 0, $dashPos);
            $method = substr($method, $dashPos + 1);
        }

        return $data;
    }


    /**
     * All supported algorithm configurations
     * @return iterable<OpenSslSymmetricAlgorithmConfig>
     */
    protected static function getAlgorithmConfigs() : iterable
    {
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::AES, 'aes', true, true, true, static::checkKeySizeEquivalent(...));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::ARIA, 'aria', true, true, true, static::checkKeySizeEquivalent(...));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::BLOWFISH, 'bf', true, 64, true, static::checkKeySizeWithinRange(32, 448));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::CAMELLIA, 'camellia', true, true, true, static::checkKeySizeEquivalent(...));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::CAST5, 'cast5', true, 64, true, static::checkKeySizeWithinRange(40, 128));

        // DES must be matched longer first
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::DES_EDE3, 'des-ede3', true, 64, true, static::checkKeySizeForDes(3));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::DES_EDE, 'des-ede', true, 64, true, static::checkKeySizeForDes(2));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::DES, 'des', true, 64, true, static::checkKeySizeForDes(1));

        // RC2/RC4 may have optional block size specification
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::RC2, 'rc2', true, OpenSslSymmetricAlgorithmFallbackBlockSize::create(128), true, static::checkKeySizeWithinRange(40, 128));
        yield new OpenSslSymmetricAlgorithmConfig(CommonCipherAlgoTypeClass::RC4, 'rc4', true, OpenSslSymmetricAlgorithmFallbackBlockSize::create(128), false, static::checkKeySizeWithinRange(8, 2048));
    }


    /**
     * Check key size for those ciphers that has: key size = block size
     * @param int $keySize
     * @param int|null $blockSize
     * @param string|null $mode
     * @return bool|int
     */
    protected static function checkKeySizeEquivalent(int $keySize, ?int $blockSize, ?string $mode) : bool|int
    {
        _used($mode);

        if ($blockSize === null) return false;
        if ($keySize === $blockSize) return true;

        return $blockSize;
    }


    /**
     * Check key size for those ciphers that accept key size within a given range
     * @param int $minSize
     * @param int $maxSize
     * @return callable(int,int|null,string|null):(bool|int)
     */
    protected static function checkKeySizeWithinRange(int $minSize, int $maxSize) : callable
    {
        return function (int $keySize, ?int $blockSize, ?string $mode) use ($minSize, $maxSize) : bool|int {
            _used($blockSize, $mode);
            return ($minSize <= $keySize) && ($keySize <= $maxSize);
        };
    }


    /**
     * Check key size for DES cipher where its 7-bit version and 8-bit version (parity included) is both supported
     * @param int $numBlocks
     * @return callable(int,int|null,string|null):(bool|int)
     */
    protected static function checkKeySizeForDes(int $numBlocks) : callable
    {
        return function (int $keySize, ?int $blockSize, ?string $mode) use ($numBlocks) : bool {
            _used($blockSize, $mode);
            return $keySize == (56 * $numBlocks) || $keySize == (64 * $numBlocks);
        };
    }


    /**
     * Check if mode supported
     * @param string $mode
     * @return bool
     */
    protected static function isModeSupported(string $mode) : bool
    {
        return match ($mode) {
            CommonCipherMode::CBC,
            CommonCipherMode::ECB,
            CommonCipherMode::CCM,
            CommonCipherMode::CFB,
            CommonCipherMode::CFB1,
            CommonCipherMode::CFB8,
            CommonCipherMode::CTR,
            CommonCipherMode::GCM,
                => true,
            default,
                => false,
        };
    }
}