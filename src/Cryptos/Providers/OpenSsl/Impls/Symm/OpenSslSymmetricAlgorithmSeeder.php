<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\CommonCipherMode;
use Magpie\Cryptos\Concepts\SymmetricCipherAlgorithmProvidable;
use Magpie\Cryptos\Concepts\SymmetricCipherSetupServiceable;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Exceptions\InvalidBitSizeException;
use Magpie\Cryptos\Providers\OpenSsl\SpecContext;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * Seeder for OpenSSL's symmetric algorithm
 * @internal
 */
class OpenSslSymmetricAlgorithmSeeder implements SymmetricCipherAlgorithmProvidable
{
    /**
     * Current type class
     */
    public const TYPECLASS = SpecContext::TYPECLASS;
    /**
     * Replacement of 'null' block size in map
     */
    protected const NULL_BLOCK_SIZE = 0;
    /**
     * Replacement of 'null' mode in map
     */
    protected const NULL_MODE = '';

    /**
     * @var string Corresponding algorithm's type class
     */
    public readonly string $algoTypeClass;
    /**
     * @var OpenSslSymmetricAlgorithmConfig Specific configuration
     */
    protected readonly OpenSslSymmetricAlgorithmConfig $config;
    /**
     * @var array<int, array<string>> Map of discovered block sizes to their corresponding modes
     */
    protected array $blockSizeModeMap = [];


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param OpenSslSymmetricAlgorithmConfig $config
     */
    public function __construct(string $algoTypeClass, OpenSslSymmetricAlgorithmConfig $config)
    {
        $this->algoTypeClass = $algoTypeClass;
        $this->config = $config;
    }


    /**
     * @inheritDoc
     */
    public function getAlgoTypeClass() : string
    {
        return $this->algoTypeClass;
    }


    /**
     * The default block size for the algorithm
     * @return int
     */
    public function getDefaultBlockSize() : int
    {
        return $this->config->getDefaultBlockSize();
    }


    /**
     * Build the corresponding OpenSSL's method name
     * @param int|null $blockSize
     * @param string|null $mode
     * @return string
     */
    public function buildOpenSslMethodName(?int $blockSize, ?string $mode) : string
    {
        $ret = $this->config->openSslMethodPrefix;
        if ($blockSize !== null) $ret .= '-' . $blockSize;
        if ($mode !== null) $ret .= '-' . $mode;

        return $ret;
    }


    /**
     * Check if block size supported
     * @param int|null $blockSize
     * @return bool
     */
    public function isBlockSizeSupported(?int $blockSize) : bool
    {
        // When nothing, only null is supported
        if (count($this->blockSizeModeMap) <= 0) {
            return $blockSize === null;
        }

        // Otherwise, must be in map
        $checkBlockSize = $blockSize ?? static::NULL_BLOCK_SIZE;
        return array_key_exists($checkBlockSize, $this->blockSizeModeMap);
    }


    /**
     * All supported block sizes
     * @return iterable<int|null>
     */
    public function getBlockSizes() : iterable
    {
        $hasValue = false;

        foreach ($this->blockSizeModeMap as $blockSize => $modes) {
            _used($modes);
            if ($blockSize === static::NULL_BLOCK_SIZE) $blockSize = null;
            $hasValue = true;
            yield $blockSize;
        }

        // 'null' is always guaranteed if nothing provided
        if (!$hasValue) yield null;
    }


    /**
     * Check that the given combination of block size and mode is valid
     * @param int|null $blockSize
     * @param string|null $mode
     * @return void
     * @throws SafetyCommonException
     */
    public function checkBlockSizeAndMode(?int $blockSize, ?string $mode) : void
    {
        foreach ($this->getBlockSizeModes($blockSize) as $blockSizeMode) {
            if ($blockSizeMode === $mode) return;
        }

        throw new UnsupportedValueException($mode, _l('block cipher mode'));
    }


    /**
     * Check key size
     * @param int $keySize
     * @param int|null $blockSize
     * @param string|null $mode
     * @return void
     * @throws CryptoException
     */
    public function checkKeySize(int $keySize, ?int $blockSize, ?string $mode) : void
    {
        $result = ($this->config->checkKeySizeFn)($keySize, $blockSize, $mode);
        if ($result === true) return;

        $expectedSize = is_int($result) ? $result : null;
        throw new InvalidBitSizeException($keySize, _l('key'), $expectedSize);
    }


    /**
     * All modes supported by given block size
     * @param int|null $blockSize
     * @return iterable
     * @throws SafetyCommonException
     */
    public function getBlockSizeModes(?int $blockSize) : iterable
    {
        if (count($this->blockSizeModeMap) <= 0) {
            // 'null' mode of 'null' size is always guaranteed
            yield null;
            return;
        }

        $blockSizeKey = $blockSize ?? static::NULL_BLOCK_SIZE;
        if (!array_key_exists($blockSizeKey, $this->blockSizeModeMap)) throw new UnsupportedValueException($blockSize, _l('block size'));

        $hasValue = false;
        $modes = $this->blockSizeModeMap[$blockSizeKey];

        foreach ($modes as $mode) {
            if ($mode === static::NULL_MODE) $mode = null;
            $hasValue = true;
            yield $mode;
        }

        // 'null' is always guaranteed if nothing provided
        if (!$hasValue) yield null;
    }


    /**
     * Default mode when not specified
     * @param int|null $blockSize
     * @return string|null
     * @throws SafetyCommonException
     */
    public function getDefaultMode(?int $blockSize) : ?string
    {
        $modes = iter_flatten($this->getBlockSizeModes($blockSize), false);
        if (count($modes) <= 0) return null;

        // Prefer CBC when exist
        if (in_array(CommonCipherMode::CBC, $modes)) return CommonCipherMode::CBC;

        // Otherwise, exit
        return null;
    }


    /**
     * Register a specification
     * @param OpenSslSymmetricAlgorithmSpec $algorithmSpec
     * @return void
     */
    public function register(OpenSslSymmetricAlgorithmSpec $algorithmSpec) : void
    {
        $blockSize = $algorithmSpec->blockSize ?? static::NULL_BLOCK_SIZE;
        $mode = $algorithmSpec->mode ?? static::NULL_MODE;

        $modes = $this->blockSizeModeMap[$blockSize] ?? [];
        $modes[] = $mode;
        $this->blockSizeModeMap[$blockSize] = $modes;
    }


    /**
     * Close and stop registration
     * @return void
     */
    public function stopRegister() : void
    {
        ksort($this->blockSizeModeMap);
    }


    /**
     * @inheritDoc
     */
    public function createCipherSetupService(?int $blockNumBits, ?string $mode) : ?SymmetricCipherSetupServiceable
    {
        $mode = $mode ?? $this->getDefaultMode($blockNumBits);

        return new OpenSslSymmetricAlgorithmService($this, $blockNumBits, $mode);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}