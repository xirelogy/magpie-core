<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Packs\PackContext;

/**
 * HTTP client response body in a file
 */
abstract class HttpFileClientResponseBody extends HttpClientResponseBody implements BinaryContentable
{
    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->filename = $this->getFilename();
        $ret->mimeType = $this->getMimeType();
    }
}