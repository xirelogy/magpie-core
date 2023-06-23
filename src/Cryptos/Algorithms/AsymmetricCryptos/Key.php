<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Contents\BinaryBlockContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\CryptoObject;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Providers\AsymmetricKeyImporter;
use Magpie\General\Packs\PackContext;

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
    protected static function onImportFromBinary(BinaryBlockContent $source, ?string $password, ?Context $context) : ?self
    {
        $tryImported = static::onTryImportBinaryUsing(static::getImporterClass(), $source, $password, $context);
        if ($tryImported instanceof static) return $tryImported;

        $context = $context ?? AsymmetricKeyImporter::getDefaultContext();

        $implContext = ImplContext::initialize($context->getTypeClass());
        return $implContext->parseAsymmetricKeyFromBinary($source, $password, static::isImportAsPrivate());
    }


    /**
     * @inheritDoc
     */
    protected static final function getImporterClass() : ?string
    {
        return AsymmetricKeyImporter::class;
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