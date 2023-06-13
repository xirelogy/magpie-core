<?php

namespace Magpie\Cryptos;

use Magpie\Cryptos\Concepts\Exportable;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * An object (handle) related to cryptographic operations
 */
abstract class CryptoObject implements Packable, Importable, Exportable
{
    use CommonPackable;


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {

    }


    /**
     * @inheritDoc
     */
    public static final function import(CryptoFormatContent|CryptoContent|BinaryDataProvidable|string $source, ?Context $context = null) : static
    {
        $source = CryptoFormatContent::accept($source);

        return static::onImport($source, $context);
    }


    /**
     * Import and parse for current type of object from source
     * @param CryptoFormatContent $source
     * @param Context|null $context
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    protected static abstract function onImport(CryptoFormatContent $source, ?Context $context) : static;
}