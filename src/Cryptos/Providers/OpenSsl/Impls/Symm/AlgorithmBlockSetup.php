<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

/**
 * Block setup related with given symmetric algorithm
 * @internal
 */
class AlgorithmBlockSetup
{
    /**
     * @var int Block's number of bits
     */
    public readonly int $blockNumBits;
    /**
     * @var array<string, string> List of supported modes
     */
    public array $modes = [];


    /**
     * Constructor
     * @param int $blockNumBits
     */
    public function __construct(int $blockNumBits)
    {
        $this->blockNumBits = $blockNumBits;
    }
}