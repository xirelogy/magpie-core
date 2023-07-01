<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Closure;

/**
 * Configuration for OpenSSL's symmetric algorithm
 * @internal
 */
class OpenSslSymmetricAlgorithmConfig
{
    /**
     * @var string Current algorithm type class
     */
    public readonly string $algoTypeClass;
    /**
     * @var string Prefix in OpenSSL's cipher method
     */
    public readonly string $openSslMethodPrefix;
    /**
     * @var bool If OpenSSL's cipher method has multiple entries (a.k.a. with block size and mode suffix)
     */
    protected readonly bool $hasOpenSslMulti;
    /**
     * @var true|int If block size expected in OpenSSL's cipher method (true) or the default block size (int)
     */
    protected readonly bool|int|OpenSslSymmetricAlgorithmFallbackBlockSize $expectBlockSizeSpec;
    /**
     * @var bool If mode expected in OpenSSL's cipher method
     */
    protected readonly bool $isExpectMode;
    /**
     * @var Closure Function to check key size
     */
    public readonly Closure $checkKeySizeFn;


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param string $openSslMethodPrefix
     * @param bool $hasOpenSslMulti
     * @param bool|int|OpenSslSymmetricAlgorithmFallbackBlockSize $expectBlockSizeSpec
     * @param bool $isExpectMode
     * @param callable(int,int|null,string|null):bool|int $checkKeySizeFn
     */
    public function __construct(string $algoTypeClass, string $openSslMethodPrefix, bool $hasOpenSslMulti, bool|int|OpenSslSymmetricAlgorithmFallbackBlockSize $expectBlockSizeSpec, bool $isExpectMode, callable $checkKeySizeFn)
    {
        $this->algoTypeClass = $algoTypeClass;
        $this->openSslMethodPrefix = $openSslMethodPrefix;
        $this->hasOpenSslMulti = $hasOpenSslMulti;
        $this->expectBlockSizeSpec = $expectBlockSizeSpec;
        $this->isExpectMode = $isExpectMode;
        $this->checkKeySizeFn = $checkKeySizeFn;
    }


    /**
     * If block size may be expected in OpenSSL's cipher method but can fallback
     * @return bool
     */
    public function isFallbackBlockSize() : bool
    {
        return $this->expectBlockSizeSpec instanceof OpenSslSymmetricAlgorithmFallbackBlockSize;
    }


    /**
     * If block size expected in OpenSSL's cipher method
     * @return bool
     */
    public function isExpectBlockSize() : bool
    {
        return $this->expectBlockSizeSpec === true;
    }


    /**
     * The default block size
     * @return int
     */
    public function getDefaultBlockSize() : int
    {
        if (is_int($this->expectBlockSizeSpec)) return $this->expectBlockSizeSpec;

        // Safe default
        return 0;
    }


    /**
     * If mode expected in OpenSSL's cipher method
     * @return bool
     */
    public function isExpectMode() : bool
    {
        return $this->isExpectMode;
    }


    /**
     * If the given OpenSSL's cipher method is matched
     * @param string $method
     * @return bool
     */
    public function ifOpenSSLMatched(string &$method) : bool
    {
        if ($this->hasOpenSslMulti) {
            // Expecting prefix match
            $prefix = $this->openSslMethodPrefix . '-';
            if (!str_starts_with($method, $prefix)) return false;
            $method = substr($method, strlen($prefix));
        } else {
            // Expecting full match
            if ($method !== $this->openSslMethodPrefix) return false;
            $method = '';
        }

        return true;
    }
}