<?php

namespace Magpie\Controllers\Concepts;

use Exception;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Request;

/**
 * A callable interface for the controller
 */
interface ControllerCallable
{
    /**
     * Make the call
     * @param Request $request
     * @param array $routeArguments
     * @return mixed
     * @throws HttpResponseException
     * @throws Exception
     */
    public function call(Request $request, array $routeArguments) : mixed;
}