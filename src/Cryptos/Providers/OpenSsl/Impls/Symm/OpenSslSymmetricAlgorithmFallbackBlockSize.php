<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

/**
 * A block size specification that expects a block size and may fall back when not available
 */
class OpenSslSymmetricAlgorithmFallbackBlockSize
{
    /**
     * @var int Fallback block size
     */
    public readonly int $blockSize;


    /**
     * Constructor
     * @param int $blockSize
     */
    protected function __construct(int $blockSize)
    {
        $this->blockSize = $blockSize;
    }


    /**
     * Create an instance
     * @param int $blockSize
     * @return static
     */
    public static function create(int $blockSize) : static
    {
        return new static($blockSize);
    }
}