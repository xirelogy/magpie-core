<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Common interface to setup symmetric cipher
 */
interface CommonSymmetricCipherSetupServiceable
{
    /**
     * Block size in bits
     * @return int
     */
    public function getBlockNumBits() : int;


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
}