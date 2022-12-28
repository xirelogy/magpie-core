<?php

namespace Magpie\Facades\Http;

use Magpie\Codecs\Formats\Formatter;
use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\Facades\Http\Bodies\HttpFormClientRequestBody;
use Magpie\Facades\Http\Bodies\HttpJsonClientRequestBody;
use Magpie\General\Concepts\TypeClassable;

/**
 * A body to be sent along with the request
 */
abstract class HttpClientRequestBody implements TypeClassable
{
    /**
     * Create a form request body
     * @param array $keyValues
     * @return static
     */
    public static function form(array $keyValues) : static
    {
        return new HttpFormClientRequestBody($keyValues);
    }


    /**
     * Create a JSON encoded request body
     * @param object|array $payload
     * @param Formatter|null $formatter
     * @return static
     * @throws InvalidJsonDataFormatException
     */
    public static function json(object|array $payload, ?Formatter $formatter = null) : static
    {
        return new HttpJsonClientRequestBody($payload, $formatter);
    }
}