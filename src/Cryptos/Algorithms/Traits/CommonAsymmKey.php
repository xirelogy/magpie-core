<?php

namespace Magpie\Cryptos\Algorithms\Traits;

use Magpie\Cryptos\Contents\ExportOption;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplAsymmKey;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Packs\PackContext;

/**
 * Asymmetric key (common implementation)
 */
trait CommonAsymmKey
{
    /**
     * @inheritDoc
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
        parent::onPack($ret, $context);

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