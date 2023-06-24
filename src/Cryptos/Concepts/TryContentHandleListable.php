<?php

namespace Magpie\Cryptos\Concepts;

/**
 * May list multiple tries to handle CryptoFormatContent
 */
interface TryContentHandleListable
{
    /**
     * Get try content handle
     * @return iterable<TryContentHandleable>
     */
    public static function getTryContentHandleLists() : iterable;
}