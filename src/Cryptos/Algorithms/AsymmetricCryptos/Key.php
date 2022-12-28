<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Concepts\Exportable;
use Magpie\Cryptos\Contents\CryptoContent;
use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Asymmetric cryptography key
 */
abstract class Key implements Packable, AlgoTypeClassable, Exportable
{
    use CommonPackable;


    /**
     * Number of bits in the current key
     * @return int
     */
    public function getNumBits() : int
    {
        return $this->getImpl()->getNumBits();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->algoTypeClass = $this->getAlgoTypeClass();
        $ret->bits = $this->getNumBits();
    }


    /**
     * @inheritDoc
     */
    public final function export(ExportOption ...$options) : string
    {
        return $this->getImpl()->export($this->getExportName(), $options);
    }


    /**
     * Export name
     * @return string
     */
    protected abstract function getExportName() : string;



    /**
     * Get implementation
     * @return ImplAsymmKey
     */
    protected abstract function getImpl() : ImplAsymmKey;


    /**
     * Parse for a key from given source
     * @param CryptoContent|BinaryDataProvidable|string $source
     * @param bool $isPrivate
     * @param Context|null $context
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static function onImport(CryptoContent|BinaryDataProvidable|string $source, bool $isPrivate, ?Context $context) : static
    {
        $context = $context ?? Context::getDefault();

        $implContext = ImplContext::initialize($context->getTypeClass());
        $implKey = $implContext->parseAsymmetricKey($source, $isPrivate);

        $algoTypeClass = $implKey->getAlgoTypeClass();
        return static::_fromRaw($algoTypeClass, $implKey);
    }


    /**
     * Create key instance using given algorithm type class
     * @param string $algoTypeClass
     * @param ImplAsymmKey $implKey
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     * @internal
     */
    public abstract static function _fromRaw(string $algoTypeClass, ImplAsymmKey $implKey) : static;
}