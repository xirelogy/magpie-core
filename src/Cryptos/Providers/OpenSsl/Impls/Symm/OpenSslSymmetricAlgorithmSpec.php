<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Symm;

/**
 * Specification of OpenSSL's symmetric algorithm
 * @internal
 */
class OpenSslSymmetricAlgorithmSpec
{
    /**
     * @var string Corresponding algorithm's type class
     */
    public readonly string $algoTypeClass;
    /**
     * @var int|null Corresponding block size
     */
    public readonly ?int $blockSize;
    /**
     * @var string|null Corresponding mode
     */
    public readonly ?string $mode;


    /**
     * Constructor
     * @param string $algoTypeClass
     * @param int|null $blockSize
     * @param string|null $mode
     */
    public function __construct(string $algoTypeClass, ?int $blockSize, ?string $mode)
    {
        $this->algoTypeClass = $algoTypeClass;
        $this->blockSize = $blockSize;
        $this->mode = $mode;
    }
}