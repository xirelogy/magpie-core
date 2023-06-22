<?php

namespace Magpie\Cryptos;

use Magpie\Cryptos\Concepts\Exportable;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
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

        foreach (static::onImport($source, $context) as $imported) {
            return $imported;
        }

        throw new UnsupportedValueException($source, _l('import source'));
    }


    /**
     * Import multiple from container
     * @param CryptoFormatContent|BinaryDataProvidable|string $source
     * @param Context|null $context
     * @return iterable<static>
     * @throws SafetyCommonException
     * @throws CryptoException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static final function importContainer(CryptoFormatContent|BinaryDataProvidable|string $source, ?Context $context = null) : iterable
    {
        $source = CryptoFormatContent::accept($source);

        yield from static::onImport($source, $context);
    }


    /**
     * Import multiple from container
     * @param CryptoFormatContent $source
     * @param Context|null $context
     * @return iterable<static>
     * @throws SafetyCommonException
     * @throws CryptoException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected static function onImport(CryptoFormatContent $source, ?Context $context) : iterable
    {
        foreach ($source->getBinaryBlocks() as $block) {
            $imported = static::onImportFromBinary($block, $source->password, $context);
            if ($imported instanceof static) yield $imported;
        }
    }


    /**
     * Import and (try) parse for current type of object from binary block content source
     * @param BinaryBlockContent $source
     * @param string|null $password
     * @param Context|null $context
     * @return static|null
     * @throws SafetyCommonException
     * @throws CryptoException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected static abstract function onImportFromBinary(BinaryBlockContent $source, ?string $password, ?Context $context) : ?self;
}