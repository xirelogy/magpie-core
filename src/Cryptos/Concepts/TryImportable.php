<?php

namespace Magpie\Cryptos\Concepts;

use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\CryptoObject;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * May try to import from other source content
 * @template T
 */
interface TryImportable
{
    /**
     * Try to import and parse from source
     * @param CryptoFormatContent $source
     * @param class-string<CryptoObject> $hintClass
     * @return iterable<T>|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public function import(CryptoFormatContent $source, string $hintClass) : ?iterable;


    /**
     * Try to import and parse from binary source
     * @param BinaryBlockContent $source
     * @param string|null $password
     * @param class-string<CryptoObject> $hintClass
     * @return CryptoObject|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    public function importBinary(BinaryBlockContent $source, ?string $password, string $hintClass) : ?CryptoObject;
}