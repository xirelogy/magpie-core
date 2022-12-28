<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Interface for implementation that can perform some operations on target binary data
 */
interface BinaryProcessable
{
    /**
     * Process the target binary data
     * @param string $input
     * @return string
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function process(string $input) : string;
}