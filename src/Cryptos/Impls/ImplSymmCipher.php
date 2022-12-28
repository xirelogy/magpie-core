<?php

namespace Magpie\Cryptos\Impls;

use Magpie\Cryptos\Algorithms\SymmetricCryptos\Cipher;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Paddings\Padding;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for symmetric cipher implementation
 * @internal
 */
interface ImplSymmCipher
{
    /**
     * Block size in bits
     * @return int
     */
    public function getBlockNumBits() : int;


    /**
     * Default mode
     * @return string|null
     */
    public function getDefaultMode() : ?string;


    /**
     * Set cipher mode
     * @param string $mode
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function setMode(string $mode) : string;


    /**
     * Get number of bits expected for IV
     * @return int|null
     */
    public function getIvNumBits() : ?int;


    /**
     * Check and ensure that the provided key can be used
     * @param string $key
     * @return void
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function checkKey(string $key) : void;


    /**
     * Check and ensure that the provided IV can be used
     * @param string $iv
     * @return void
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function checkIv(string $iv) : void;


    /**
     * Create cipher
     * @param string $key
     * @param string|null $iv
     * @param string|null $mode
     * @param Padding|null $padding
     * @return Cipher
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function createCipher(string $key, ?string $iv, ?string $mode, ?Padding $padding) : Cipher;
}