<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\BinaryDataProvidable;

/**
 * May import from other source content
 */
interface Importable
{
    /**
     * Import and parse from source
     * @param CryptoContent|BinaryDataProvidable|string $source
     * @param Context|null $context
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public static function import(CryptoContent|BinaryDataProvidable|string $source, ?Context $context = null) : static;

}