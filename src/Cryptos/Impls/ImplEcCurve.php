<?php

namespace Magpie\Cryptos\Impls;

/**
 * Interface for Elliptic Curve's curve parameter implementation
 * @internal
 */
interface ImplEcCurve
{
    /**
     * Common curve name
     * @return string
     */
    public function getName() : string;
}