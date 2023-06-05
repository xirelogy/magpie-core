<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos;

use Magpie\Cryptos\Concepts\AlgoTypeClassable;
use Magpie\Cryptos\Concepts\Exportable;
use Magpie\Cryptos\Contents\CryptoContent;
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
    public abstract function getNumBits() : int;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->algoTypeClass = $this->getAlgoTypeClass();
    }


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

        return static::onConstructImplKey($implKey);
    }


    /**
     * @param ImplAsymmKey $implKey
     * @return static
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    protected static abstract function onConstructImplKey(ImplAsymmKey $implKey) : static;
}