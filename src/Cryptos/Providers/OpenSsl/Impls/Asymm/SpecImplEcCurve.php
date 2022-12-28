<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls\Asymm;

use Magpie\Cryptos\Impls\ImplEcCurve;

/**
 * Specific OpenSSL Elliptic Curve's curve parameter instance
 * @internal
 */
class SpecImplEcCurve implements ImplEcCurve
{
    /**
     * @var string Common name
     */
    protected string $name;


    /**
     * Constructor
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->name;
    }
}