<?php

namespace Magpie\Cryptos\Algorithms\SymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplSymmCipher;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Cryptos\Providers\SymmetricCipherAlgorithms;
use Magpie\Exceptions\InvalidArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Objects\BinaryData;

/**
 * Setup of symmetric crypto cipher
 */
class CipherSetup implements Packable, AlgoTypeClassable
{
    use CommonPackable;


    /**
     * @var string Algorithm type class
     */
    protected string $algoTypeClass;
    /**
     * @var ImplSymmCipher Underlying implementation
     */
    protected readonly ImplSymmCipher $impl;
    /**
     * @var string|null Cipher key
     */
    protected ?string $key = null;
    /**
     * @var string|null IV
     */
    protected ?string $iv = null;
    /**
     * @var string|null Cipher block mode
     */
    protected ?string $mode = null;
    /**
     * @var Padding|null Padding mode
     */
    protected ?Padding $padding = null;


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param ImplSymmCipher $impl
     */
    protected function __construct(string $algoTypeClass, ImplSymmCipher $impl)
    {
        $this->algoTypeClass = $algoTypeClass;
        $this->impl = $impl;
    }


    /**
     * @inheritDoc
     */
    public function getAlgoTypeClass() : string
    {
        return $this->algoTypeClass;
    }


    /**
     * Block size in bits
     * @return int
     */
    public function getBlockNumBits() : int
    {
        return $this->impl->getBlockNumBits();
    }


    /**
     * Specify cipher mode
     * @param string $mode
     * @return $this
     * @throws SafetyCommonException
     * @throws CryptoException
     * @deprecated Mode shall be set during initialization and no longer changed.
     */
    public function withMode(string $mode) : static
    {
        $this->mode = $this->impl->setMode($mode);
        return $this;
    }


    /**
     * Specify key
     * @param BinaryData|string $key
     * @return $this
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function withKey(BinaryData|string $key) : static
    {
        $key = static::acceptData($key);
        $this->impl->checkKey($key);

        $this->key = $key;
        return $this;
    }


    /**
     * Get number of bits expected for IV
     * @return int|null
     */
    public function getIvNumBits() : ?int
    {
        return $this->impl->getIvNumBits();
    }


    /**
     * Specify IV (Initialization Vector)
     * @param BinaryData|string $iv
     * @return $this
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function withIv(BinaryData|string $iv) : static
    {
        $iv = static::acceptData($iv);
        $this->impl->checkIv($iv);

        $this->iv = $iv;
        return $this;
    }


    /**
     * Specify padding scheme
     * @param Padding|string $padding
     * @return $this
     * @throws SafetyCommonException
     */
    public function withPadding(Padding|string $padding) : static
    {
        if (is_string($padding)) $padding = Padding::initialize($padding);
        $this->padding = $padding;

        return $this;
    }


    /**
     * Create cipher
     * @return Cipher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function create() : Cipher
    {
        return $this->impl->createCipher($this->key, $this->iv, $this->mode, $this->padding);
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->algoTypeClass = $this->getAlgoTypeClass();
        $ret->blockNumBits = $this->getBlockNumBits();
        $ret->mode = $this->mode;
    }


    /**
     * Accept as binary data
     * @param BinaryData|string $data
     * @return string
     */
    protected static function acceptData(BinaryData|string $data) : string
    {
        return BinaryData::acceptBinary($data)->asBinary();
    }


    /**
     * Initialize setup
     * @param string $algoTypeClass Algorithm type class
     * @param int|null $blockNumBits Block size (in bits)
     * @param string|null $mode Algorithm mode
     * @param Context|null $context
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function initialize(string $algoTypeClass, ?int $blockNumBits = null, ?string $mode = null, ?Context $context = null) : static
    {
        return SymmetricCipherAlgorithms::_initializeCipherSetup($algoTypeClass, $blockNumBits, $mode, $context);
    }


    /**
     * Generate random bytes (preferable using cryptographically strong methods) of specific bit size
     * @param int $numBits
     * @param Context|null $context
     * @return BinaryData
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function generateRandom(int $numBits, ?Context $context = null) : BinaryData
    {
        $inContext = static::getImplContext($context);

        if (($numBits % 8) !== 0) throw new InvalidArgumentException('numBits');

        return $inContext->generateRandom($numBits);
    }


    /**
     * Calculate the bit size for given data
     * @param BinaryData|string $data
     * @return int
     */
    public static function calculateBitSize(BinaryData|string $data) : int
    {
        $data = static::acceptData($data);
        return strlen($data) * 8;
    }



    /**
     * Get implementation context
     * @param Context|null $context
     * @return ImplContext
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static function getImplContext(?Context $context) : ImplContext
    {
        $context = $context ?? Context::getDefault();
        return ImplContext::initialize($context->getTypeClass());
    }
}