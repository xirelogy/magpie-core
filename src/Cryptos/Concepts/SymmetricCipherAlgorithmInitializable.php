<?php

namespace Magpie\Cryptos\Concepts;

use Exception;

/**
 * May initialize and register providers for a symmetric cipher algorithm
 */
interface SymmetricCipherAlgorithmInitializable
{
    /**
     * Initialize the supported symmetric cipher algorithms
     * @return void
     * @throws Exception
     */
    public function initialize() : void;
}