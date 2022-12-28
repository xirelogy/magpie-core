<?php

namespace Magpie\HttpServer\Renderers;

use Magpie\HttpServer\Concepts\HttpResponseExceptionRenderable;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Response;

/**
 * Default implementation of HttpResponseException renderer
 */
class DefaultHttpResponseExceptionRenderer implements HttpResponseExceptionRenderable
{
    /**
     * @inheritDoc
     */
    public function createExceptionRenderer(HttpResponseException $ex) : Renderable
    {
        $code = $ex->getCode();
        $message = $ex->getMessage();
        $text = htmlspecialchars("$code $message");

        /** @noinspection HtmlRequiredLangAttribute */
        $response = new Response("<html><head><title>$text</title></head><body><h1>$text</h1></body></html>", $code);

        foreach ($ex->getHeaders() as $headerKey => $headerValue) {
            $response->withHeader($headerKey, $headerValue);
        }

        return $response;
    }
}