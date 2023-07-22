<?php

namespace Magpie\System\RunContexts;

use Exception;
use Magpie\Configurations\AppConfig;
use Magpie\Exceptions\SystemUnderMaintenanceException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Request;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\RouteDomain;
use Magpie\Routes\RouteRegistry;
use Magpie\System\Kernel\Kernel;

/**
 * Context of execution to handle web request
 */
class WebRunContext extends RunContext
{
    protected readonly Request $request;


    /**
     * Constructor
     * @param Request $request
     */
    protected function __construct(Request $request)
    {
        parent::__construct();

        $this->request = $request;
    }


    /**
     * @inheritDoc
     */
    public function run() : void
    {
        $appConfig = Kernel::current()->getConfig();

        $hostname = $this->request->hostname ?? '';
        $routeDomain = RouteRegistry::_route($hostname, $domainArguments);

        if ($domainArguments !== null) {
            $this->request->domainArguments = Request::_createRouteArgumentsCollectionFrom($domainArguments);
        }

        try {
            $this->onRun($appConfig, $routeDomain, $this->request);
        } catch (HttpResponseException $ex) {
            $appConfig->getHttpResponseExceptionRenderer()->createExceptionRenderer($ex)->render($this->request);
            return;
        }
    }


    /**
     * Handle running a handler for web server request
     * @param AppConfig $appConfig
     * @param RouteDomain|null $routeDomain
     * @param Request $request
     * @return void
     * @throws HttpResponseException
     * @throws Exception
     */
    protected function onRun(AppConfig $appConfig, ?RouteDomain $routeDomain, Request $request) : void
    {
        $handler = $routeDomain !== null ?
            $routeDomain->_route($request) :
            $appConfig->getDefaultRouteHandler($request);

        $response = $this->onRoute($handler, $request);

        $this->getResponseRenderer($appConfig, $response)->render($request);
    }


    /**
     * Handle routing using given route handler for web server request
     * @param RouteHandleable $handler
     * @param Request $request
     * @return mixed
     * @throws HttpResponseException
     * @throws Exception
     */
    protected function onRoute(RouteHandleable $handler, Request $request) : mixed
    {
        if (Kernel::current()->isUnderMaintenance()) {
            throw new SystemUnderMaintenanceException();
        }

        return $handler->route($request);
    }


    /**
     * Get a renderer for given response
     * @param AppConfig $appConfig
     * @param mixed $response
     * @return Renderable
     * @throws Exception
     */
    protected function getResponseRenderer(AppConfig $appConfig, mixed $response) : Renderable
    {
        if ($response instanceof Renderable) return $response;

        $renderer = $appConfig->getResponseRenderer($response);
        if ($renderer !== null) return $renderer;

        throw new UnsupportedValueException($response, _l('response'));
    }


    /**
     * @inheritDoc
     */
    protected static function onCapture() : static
    {
        $request = Request::capture();

        return new static($request);
    }
}