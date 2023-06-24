<?php

namespace Magpie\Cryptos\Providers;

use Magpie\Cryptos\Concepts\TryContentHandleListable;
use Magpie\Cryptos\Providers\Traits\CommonCryptoFormatContentHandlerTries;

/**
 * PKCS#12 CryptoFormatContent handler
 */
class Pkcs12CryptoFormatContentHandler implements TryContentHandleListable
{
    use CommonCryptoFormatContentHandlerTries;
}