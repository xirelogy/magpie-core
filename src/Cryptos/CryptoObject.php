<?php

namespace Magpie\Cryptos;

use Magpie\Cryptos\Algorithms\AsymmetricCryptos\Key;
use Magpie\Cryptos\Concepts\Exportable;
use Magpie\Cryptos\Concepts\Importable;
use Magpie\Cryptos\Concepts\TryImporterListable;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\TryErrorHandling;
use Magpie\Cryptos\Providers\Importer;
use Magpie\Cryptos\X509\Certificate;
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
        $triedImports = static::onTryImportUsing(static::getImporterClass(), $source, $context);
        if ($triedImports !== null) {
            yield from $triedImports;
            return;
        }

        foreach ($source->getBinaryBlocks() as $block) {
            $imported = static::onImportFromBinary($block, $source->password, $context);
            if ($imported instanceof static) yield $imported;
        }
    }


    /**
     * Try import multiple from container
     * @param class-string<TryImporterListable>|null $importerClassName
     * @param CryptoFormatContent $source
     * @param Context|null $context
     * @return iterable<static>|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static final function onTryImportUsing(?string $importerClassName, CryptoFormatContent $source, ?Context $context) : ?iterable
    {
        if ($importerClassName === null) return null;
        if ($context !== null) return null;

        if (!is_subclass_of($importerClassName, TryImporterListable::class)) return null;

        foreach ($importerClassName::getTryImporterLists() as $tryImporter) {
            $importedRets = TryErrorHandling::noThrow(fn () => $tryImporter->import($source, static::class));
            if ($importedRets === null) continue;

            $ret = [];
            foreach ($importedRets as $importedRet) {
                if ($importedRet instanceof static) $ret[] = $importedRet;
            }

            if (count($ret) > 0) return $ret;
        }

        return null;
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
    protected static function onImportFromBinary(BinaryBlockContent $source, ?string $password, ?Context $context) : ?self
    {
        return match ($source->type) {
            'CERTIFICATE' ,
                => Certificate::onImportFromBinary($source, $password, $context),
            'PUBLIC KEY',
            'PRIVATE KEY',
            'ENCRYPTED PRIVATE KEY',
                => Key::onImportFromBinary($source, $password, $context),
            default,
                => null,
        };
    }


    /**
     * Try import and parse for current type of object from binary block content source
     * @param string|null $importerClassName
     * @param BinaryBlockContent $source
     * @param string|null $password
     * @param Context|null $context
     * @return static|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static final function onTryImportBinaryUsing(?string $importerClassName, BinaryBlockContent $source, ?string $password, ?Context $context) : ?static
    {
        if ($importerClassName === null) return null;
        if ($context !== null) return null;

        if (!is_subclass_of($importerClassName, TryImporterListable::class)) return null;

        foreach ($importerClassName::getTryImporterLists() as $tryImporter) {
            $ret = TryErrorHandling::noThrow(fn () => $tryImporter->importBinary($source, $password, static::class));
            if ($ret instanceof static) return $ret;
        }

        return null;
    }


    /**
     * The importer class name (if any)
     * @return class-string<Importer>|null
     */
    protected static function getImporterClass() : ?string
    {
        return null;
    }
}