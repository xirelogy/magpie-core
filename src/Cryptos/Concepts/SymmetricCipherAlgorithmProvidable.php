<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;

/**
 * Provider for a symmetric cipher algorithm
 */
interface SymmetricCipherAlgorithmProvidable extends AlgoTypeClassable, TypeClassable
{
    /**
     * Try to create a cipher setup service interface. This function should throw an exception for
     * improper setup, or with specific exception, and return null for setup not supported (silently)
     * @param int|null $blockNumBits
     * @param string|null $mode
     * @return SymmetricCipherSetupServiceable|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public function createCipherSetupService(?int $blockNumBits, ?string $mode) : ?SymmetricCipherSetupServiceable;
}