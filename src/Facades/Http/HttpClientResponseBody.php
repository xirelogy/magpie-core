<?php

namespace Magpie\Facades\Http;

use Exception;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Stringable;

/**
 * HTTP client response body
 */
abstract class HttpClientResponseBody implements Packable, BinaryDataProvidable, StreamReadConvertible, Stringable
{
    use CommonPackable;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {

    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        try {
            return $this->getData();
        } catch (Exception $ex) {
            return 'ERROR: ' . $ex->getMessage();
        }
    }
}