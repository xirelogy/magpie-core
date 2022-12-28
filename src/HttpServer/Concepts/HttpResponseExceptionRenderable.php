<?php

namespace Magpie\HttpServer\Concepts;

use Magpie\HttpServer\Exceptions\HttpResponseException;

interface HttpResponseExceptionRenderable
{
    /**
     * Create renderer for given exception
     * @param HttpResponseException $ex
     * @return Renderable
     */
    public function createExceptionRenderer(HttpResponseException $ex) : Renderable;
}