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
     * @var string Curve OID
     */
    protected string $oid;


    /**
     * Constructor
     * @param string $name
     * @param string $oid
     */
    public function __construct(string $name, string $oid)
    {
        $this->name = $name;
        $this->oid = $oid;
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->name;
    }


    /**
     * @inheritDoc
     */
    public function getOid() : string
    {
        return $this->oid;
    }
}