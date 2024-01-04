<?php

namespace Magpie\System\RunContexts;

use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\Request;
use Magpie\Routes\RouteRegistry;
use Magpie\Routes\RouteRun;
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
        $routeDomain = RouteRegistry::_route($hostname, $this->request);

        try {
            RouteRun::create($appConfig)
                ->run($routeDomain, $this->request)
                ->render($this->request)
                ;
        } catch (HttpResponseException $ex) {
            $appConfig->getHttpResponseExceptionRenderer()->createExceptionRenderer($ex)->render($this->request);
            return;
        }
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