<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Facades\Http\HttpClientRequestBody;

/**
 * A body to be sent along with the request, which is an encoded body
 */
abstract class HttpEncodedClientRequestBody extends HttpClientRequestBody
{
    /**
     * Content type
     * @return string
     */
    public abstract function getContentType() : string;


    /**
     * Content length
     * @return int|null
     */
    public abstract function getContentLength() : ?int;


    /**
     * @return string
     */
    public abstract function getEncodedBody() : string;
}