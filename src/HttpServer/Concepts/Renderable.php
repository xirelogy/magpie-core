<?php

namespace Magpie\HttpServer\Concepts;

use Magpie\HttpServer\Request;

/**
 * Anything that can handle a response and render it as HTTP server's output
 */
interface Renderable
{
    /**
     * Render the response
     * @param Request|null $request Caller request, if any
     * @return void
     */
    public function render(?Request $request) : void;
}