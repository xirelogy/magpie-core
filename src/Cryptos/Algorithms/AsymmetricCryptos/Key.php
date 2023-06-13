<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\CryptoObject;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Providers\AsymmetricKeyImporter;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
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
    protected static function onImport(CryptoFormatContent $source, ?Context $context) : static
    {
        if ($context === null) {
            foreach (AsymmetricKeyImporter::getTryImporterLists() as $tryImporter) {
                $ret = Excepts::noThrow(fn () => $tryImporter->import($source, static::class));
                if ($ret instanceof static) return $ret;
            }
        }

        $context = $context ?? AsymmetricKeyImporter::getDefaultContext();

        return static::onImportKey($source, $context);
    }


    /**
     * Parse and import key from given source
     * @param CryptoFormatContent $source
     * @param Context $context
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws CryptoException
     */
    protected static function onImportKey(CryptoFormatContent $source, Context $context) : static
    {
        $implContext = ImplContext::initialize($context->getTypeClass());
        return $implContext->parseAsymmetricKey($source, static::isImportAsPrivate());
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