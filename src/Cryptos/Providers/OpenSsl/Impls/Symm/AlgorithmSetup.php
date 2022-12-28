<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

/**
 * Algorithm setup
 * @internal
 */
class AlgorithmSetup
{
    /**
     * @var string Algorithm name
     */
    public readonly string $name;
    /**
     * @var bool If this algorithm has multiple block size
     */
    public readonly bool $hasMultiBlockSize;
    /**
     * @var array<int, AlgorithmBlockSetup> Block setups
     */
    public array $blocks = [];


    /**
     * Constructor
     * @param string $name
     * @param bool $hasMultiBlockSize
     */
    public function __construct(string $name, bool $hasMultiBlockSize)
    {
        $this->name = $name;
        $this->hasMultiBlockSize = $hasMultiBlockSize;
    }
}