<?php

namespace Magpie\Routes\Concepts;

use Exception;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Request;

/**
 * Anything that could handle a route
 */
interface RouteHandleable
{
    /**
     * Handle the route request
     * @param Request $request
     * @return mixed
     * @throws HttpResponseException
     * @throws Exception
     */
    public function route(Request $request) : mixed;
}