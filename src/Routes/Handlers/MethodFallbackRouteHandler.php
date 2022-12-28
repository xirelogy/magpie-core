<?php

namespace Magpie\Routes\Handlers;

use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\HttpServer\Exceptions\HttpMethodNotAllowedException;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\Response;
use Magpie\Routes\Concepts\RouteHandleable;

/**
 * Handle routing for request made using methods not explicitly allowed (fallback)
 */
class MethodFallbackRouteHandler implements RouteHandleable
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }


    /**
     * @inheritDoc
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function route(Request $request) : mixed
    {
        $allowedMethods = $request->routeContext->getAllowedMethods() ?? [ CommonHttpMethod::GET ];

        // 'OPTIONS' may be handled accordingly
        // Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
        if ($request->getMethod() === CommonHttpMethod::OPTIONS) {
            return (new Response('', CommonHttpStatusCode::NO_CONTENT))
                ->withHeader(CommonHttpHeader::ALLOW, implode(', ', $allowedMethods))
                ;
        }

        throw new HttpMethodNotAllowedException(null, $allowedMethods);
    }
}