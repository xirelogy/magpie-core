<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\CryptoObject;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Providers\AsymmetricKeyImporter;
use Magpie\General\Packs\PackContext;
use Magpie\General\Sugars\Excepts;

/**
 * Asymmetric cryptography key
 */
abstract class Key extends CryptoObject implements AlgoTypeClassable
{
    /**
     * Number of bits in the current key
     * @return int
     */
    public abstract function getNumBits() : int;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->algoTypeClass = $this->getAlgoTypeClass();
    }


    /**
     * @inheritDoc
     */
    protected static function onImport(CryptoFormatContent $source, ?Context $context) : iterable
    {
        if ($context === null) {
            foreach (AsymmetricKeyImporter::getTryImporterLists() as $tryImporter) {
                $ret = Excepts::noThrow(fn () => $tryImporter->import($source, static::class));
                if ($ret !== null) {
                    foreach ($ret as $subRet) {
                        if ($subRet instanceof static) yield $subRet;
                    }
                    return;
                }
            }
        }

        yield from parent::onImport($source, $context);
    }


    /**
     * @inheritDoc
     */
    protected static function onImportFromBinary(BinaryBlockContent $source, ?string $password, ?Context $context) : ?self
    {
        if ($context === null) {
            foreach (AsymmetricKeyImporter::getTryImporterLists() as $tryImporter) {
                $ret = Excepts::noThrow(fn () => $tryImporter->importBinary($source, $password, static::class));
                if ($ret instanceof static) return $ret;
            }
        }

        $context = $context ?? AsymmetricKeyImporter::getDefaultContext();

        $implContext = ImplContext::initialize($context->getTypeClass());
        return $implContext->parseAsymmetricKeyFromBinary($source, $password, static::isImportAsPrivate());
    }


    /**
     * If import should be treating as private key
     * @return bool|null
     */
    protected static function isImportAsPrivate() : ?bool
    {
        return null;
    }
}