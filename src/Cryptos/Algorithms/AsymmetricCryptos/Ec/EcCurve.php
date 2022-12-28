<?php

namespace Magpie\Cryptos\Algorithms\AsymmetricCryptos\Ec;

use Magpie\Cryptos\Context;
use Magpie\Cryptos\Exceptions\CryptoException;
use Magpie\Cryptos\Impls\ImplContext;
use Magpie\Cryptos\Impls\ImplEcCurve;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Elliptic Curve's curve parameter
 */
class EcCurve implements Packable
{
    use CommonPackable;

    /**
     * @var ImplEcCurve Underlying object
     */
    protected ImplEcCurve $impl;


    /**
     * Constructor
     * @param ImplEcCurve $impl
     */
    protected function __construct(ImplEcCurve $impl)
    {
        $this->impl = $impl;
    }


    /**
     * Common curve name
     * @return string
     */
    public function getName() : string
    {
        return $this->impl->getName();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->name = $this->getName();
    }


    /**
     * Create curve instance by searching with given name
     * @param string $name
     * @param Context|null $context
     * @return static|null
     * @throws SafetyCommonException
     * @throws CryptoException
     */
    public static function fromName(string $name, ?Context $context = null) : ?static
    {
        $context = $context ?? Context::getDefault();

        $implContext = ImplContext::initialize($context->getTypeClass());
        $implCurve = $implContext->findEcCurveByName($name);

        if ($implCurve === null) return null;
        return new static($implCurve);
    }


    /**
     * Create curve instance using given implementation
     * @param ImplEcCurve $implCurve
     * @return static
     * @internal
     */
    public static function _fromRaw(ImplEcCurve $implCurve) : static
    {
        return new static($implCurve);
    }
}