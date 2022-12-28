<?php

namespace Magpie\Cryptos\Concepts;

/**
 * Support for algorithm type class
 */
interface AlgoTypeClassable
{
    /**
     * Algorithm type class
     * @return string
     */
    public function getAlgoTypeClass() : string;
}