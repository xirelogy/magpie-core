<?php

namespace Magpie\Routes\Middlewares;

use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\General\Sugars\Excepts;
use Magpie\HttpServer\Concepts\WithHeaderSpecifiable;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\Response;
use Magpie\Objects\Uri;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Constants\RouteMiddlewarePurposePriority;
use Magpie\Routes\RouteMiddleware;

/**
 * Middleware to support CORS preflight
 * @note This middleware is expected to be 'used' at domain level, otherwise it may not respond to
 * preflight check correctly
 */
class CorsMiddleware extends RouteMiddleware
{
    /**
     * @inheritDoc
     */
    public function handle(Request $request, RouteHandleable $next) : mixed
    {
        // References: https://www.html5rocks.com/static/images/cors_server_flowchart.png

        // Check origin
        $origin = $this->getOrigin($request);
        if ($origin === null || !$this->isOriginAllowed($origin)) {
            return $next->route($request);
        }

        // Check path
        if (!$this->isPathIncluded($request->requestUri->path)) {
            return $next->route($request);
        }

        // Get allowed methods
        $allowedMethods = $request->routeContext->getAllowedMethods();
        if ($allowedMethods === null) {
            return $next->route($request);
        }

        // May handle preflight CORS accordingly
        if (static::isPreflightRequest($request)) {
            $response = new Response('', CommonHttpStatusCode::NO_CONTENT);
            $this->addResponseHeaders($response, $origin, $allowedMethods);
            return $response;
        }

        // Get the response from next level and try to cast
        $response = $next->route($request);
        $response = static::tryCastResponseAsHeaderSpecifiable($response);

        // And add CORS headers when able to
        if ($response instanceof WithHeaderSpecifiable) {
            $this->addResponseHeaders($response, $origin, $allowedMethods);
        }

        return $response;
    }


    /**
     * Try to get the CORS origin
     * @param Request $request
     * @return string|null
     * @throws SafetyCommonException
     */
    protected function getOrigin(Request $request) : ?string
    {
        // Prefer from the 'Origin' header
        $origin = $request->headers->optional(CommonHttpHeader::ORIGIN);
        if ($origin !== null) return $origin;

        // Fallback to 'Referer' header in GET request
        if ($request->getMethod() === CommonHttpMethod::GET) {
            $referer = $request->headers->optional(CommonHttpHeader::REFERER);
            if ($referer !== null) {
                /** @var Uri|null $refererUrl */
                $refererUrl = Excepts::noThrow(fn () => Uri::parse($referer));
                if ($refererUrl !== null) {
                    $next = $refererUrl->build();
                    $next->path = '';
                    return "$next";
                }
            }
        }

        // Fallback to default origin
        return $this->getDefaultOrigin($request);
    }


    /**
     * Default origin when none was discovered
     * @param Request $request
     * @return string|null
     * @throws SafetyCommonException
     */
    protected function getDefaultOrigin(Request $request) : ?string
    {
        _used($request);
        _throwable() ?? throw new NullException();

        return null;
    }


    /**
     * If this request is a preflight request
     * @param Request $request
     * @return bool
     */
    protected final function isPreflightRequest(Request $request) : bool
    {
        if ($request->getMethod() !== CommonHttpMethod::OPTIONS) return false;
        if (!$request->headers->has(CommonHttpHeader::ACCESS_CONTROL_REQUEST_METHOD)) return false;

        return true;
    }


    /**
     * Add headers to given response
     * @param Response $response
     * @param string $origin
     * @param array<string> $allowedMethods
     * @return void
     */
    private function addResponseHeaders(WithHeaderSpecifiable $response, string $origin, array $allowedMethods) : void
    {
        $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
        $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_ALLOW_METHODS, implode(', ', $allowedMethods));
        $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_ALLOW_HEADERS, implode(', ', iter_flatten($this->getHeadersAllowed(), false)));

        $exposedHeaders = $this->getAllHeadersExposed();
        if (count($exposedHeaders) > 0) {
            $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS, implode(', ', $exposedHeaders));
        }

        if ($this->isCredentialsAllowed()) {
            $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_ALLOW_CREDENTIALS, 'true');
        }

        $maxAge = $this->getMaxAge();
        if ($maxAge !== null) {
            $response->withHeader(CommonHttpHeader::ACCESS_CONTROL_MAX_AGE, $maxAge);
        }
    }


    /**
     * @inheritDoc
     */
    public static function getPriorityWeight() : int
    {
        return RouteMiddlewarePurposePriority::CORS;
    }


    /**
     * Check if the given origin is allowed
     * @param string $origin
     * @return bool
     */
    protected function isOriginAllowed(string $origin) : bool
    {
        _used($origin);

        // Default allow all
        return true;
    }


    /**
     * Check if the given path is included
     * @param string $path
     * @return bool
     */
    protected function isPathIncluded(string $path) : bool
    {
        _used($path);

        // Default allow all
        return true;
    }


    /**
     * Get allowed headers
     * @return iterable<string>
     */
    protected function getHeadersAllowed() : iterable
    {
        yield CommonHttpHeader::ACCEPT;
        yield CommonHttpHeader::ACCEPT_LANGUAGE;
        yield CommonHttpHeader::CONTENT_LANGUAGE;
        yield CommonHttpHeader::CONTENT_TYPE;
        yield CommonHttpHeader::RANGE;
    }


    /**
     * Get exposed headers
     * @return array<string>
     */
    private function getAllHeadersExposed() : array
    {
        return iter_flatten($this->getHeadersExposed(), false);
    }


    /**
     * Get exposed headers
     * @return iterable<string>
     */
    protected function getHeadersExposed() : iterable
    {
        return [];
    }


    /**
     * If credentials is allowed (Access-Control-Allow-Credentials)
     * @return bool
     */
    protected function isCredentialsAllowed() : bool
    {
        return false;
    }


    /**
     * Max age of CORS response, if specified
     * @return int|null
     */
    protected function getMaxAge() : ?int
    {
        return null;
    }
}