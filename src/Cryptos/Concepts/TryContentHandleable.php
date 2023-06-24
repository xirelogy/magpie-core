<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * May try to handle CryptoFormatContent
 */
interface TryContentHandleable
{
    /**
     * Try to handle content into binary blocks
     * @param CryptoFormatContent $content
     * @return iterable<BinaryBlockContent>|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public function getBinaryBlocks(CryptoFormatContent $content) : ?iterable;
}