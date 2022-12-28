<?php

namespace Magpie\HttpServer\Concepts;

/**
 * Anything that can handle a response and render it as HTTP server's output
 */
interface Renderable
{
    /**
     * Render the response
     * @return void
     */
    public function render() : void;
}