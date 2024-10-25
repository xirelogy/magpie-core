<?php

namespace Magpie\Routes;

use Exception;
use Magpie\Configurations\AppConfig;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\SystemUnderMaintenanceException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Impls\RouteEventHost;
use Magpie\System\Kernel\Kernel;

/**
 * Host and run route handlers
 */
class RouteRun
{
    /**
     * @var AppConfig Associated application config
     */
    protected readonly AppConfig $appConfig;


    /**
     * Constructor
     * @param AppConfig $appConfig
     */
    protected function __construct(AppConfig $appConfig)
    {
        $this->appConfig = $appConfig;
    }


    /**
     * Run and handle incoming web server request
     * @param RouteDomain|null $routeDomain Resolved routing domain, if any
     * @param Request $request Incoming request
     * @return Renderable Corresponding renderer object
     * @throws HttpResponseException
     * @throws Exception
     */
    public final function run(?RouteDomain $routeDomain, Request $request) : Renderable
    {
        $handler = $routeDomain !== null ?
            $routeDomain->_route($request) :
            $this->appConfig->getDefaultRouteHandler($request);

        $response = $this->onRun($handler, $request);
        return $this->getResponseRenderer($response);
    }


    /**
     * Run and handle incoming web server request (internally)
     * @param RouteHandleable $handler The route handler
     * @param Request $request Incoming request
     * @return mixed
     * @throws HttpResponseException
     * @throws Exception
     */
    protected function onRun(RouteHandleable $handler, Request $request) : mixed
    {
        if (Kernel::current()->isUnderMaintenance()) {
            throw new SystemUnderMaintenanceException();
        }

        RouteEventHost::instance()->notifyBeforeRoute($handler, $request);
        $response = $handler->route($request);
        RouteEventHost::instance()->notifyAfterRoute($handler, $response);
        return $response;
    }


    /**
     * Get a renderer for given response
     * @param mixed $response
     * @return Renderable
     * @throws SafetyCommonException
     */
    protected function getResponseRenderer(mixed $response) : Renderable
    {
        if ($response instanceof Renderable) return $response;

        $renderer = $this->appConfig->getResponseRenderer($response);
        if ($renderer !== null) return $renderer;

        throw new UnsupportedValueException($response, _l('response'));
    }


    /**
     * Create an instance
     * @param AppConfig $appConfig
     * @return static
     */
    public static function create(AppConfig $appConfig) : static
    {
        return new static($appConfig);
    }
}