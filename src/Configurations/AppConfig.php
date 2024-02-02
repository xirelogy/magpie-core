<?php

namespace Magpie\Configurations;

use Exception;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\ConsoleCustomization;
use Magpie\Events\EventDelivery;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Factories\NamedStringCodec;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\HttpServer\BinaryContentResponse;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\Concepts\HttpResponseExceptionRenderable;
use Magpie\HttpServer\Concepts\Renderable;
use Magpie\HttpServer\Exceptions\HttpServiceUnavailableException;
use Magpie\HttpServer\Renderers\DefaultHttpResponseExceptionRenderer;
use Magpie\HttpServer\Renderers\StringRenderer;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\Resolvers\DefaultClientAddressesResolver;
use Magpie\HttpServer\Response;
use Magpie\Logs\Concepts\LogRelayable;
use Magpie\Logs\LogConfig;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Logs\Relays\ConfigurableLogRelay;
use Magpie\Logs\Relays\SimpleFileLogRelay;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Schemas\Configs\SchemaPreference;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Handlers\ClosureRouteHandler;
use Magpie\Schedules\Impls\ScheduleRegistry;
use Magpie\System\Concepts\SourceCacheable;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Impls\Consoles\SymfonyConsole;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;
use Stringable;

/**
 * Application configuration
 */
abstract class AppConfig
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }


    /**
     * All boot registrable classes
     * @return iterable<class-string<SystemBootable>>
     */
    public function getBootRegistrableClasses() : iterable
    {
        return [];
    }


    /**
     * All source cacheable classes
     * @return iterable<class-string<SourceCacheable>>
     */
    public function getSourceCacheableClasses() : iterable
    {
        yield ClassFactory::class;
        yield NamedStringCodec::class;
        yield EventDelivery::class;
        yield ScheduleRegistry::class;
    }


    /**
     * Initialization upon kernel boot up
     * @param Kernel $kernel
     * @return void
     */
    public final function initialize(Kernel $kernel) : void
    {
        try {
            $this->onInitialize($kernel);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Initialization upon kernel boot up
     * @param Kernel $kernel
     * @return void
     * @throws Exception
     */
    protected function onInitialize(Kernel $kernel) : void
    {
        $config = $this->createDefaultLogConfig();
        $logRelay = ConfigurableLogRelay::fromEnv($config);

        if ($logRelay !== null) {
            Kernel::current()->setLogger(new DefaultLogger($logRelay));
        }
    }


    /**
     * Create the default log relay
     * @return LogRelayable
     */
    public function createDefaultLogRelay() : LogRelayable
    {
        $config = $this->createDefaultLogConfig();
        return new SimpleFileLogRelay($config);
    }


    /**
     * Create default log configuration
     * @return LogConfig
     */
    public function createDefaultLogConfig() : LogConfig
    {
        return new LogConfig(
            SystemTimezone::default(),
            LogConfig::DEFAULT_TIME_FORMAT,
            LogConfig::systemDefaultSource(),
        );
    }


    /**
     * The log path, in relative to the project path
     * @return string
     */
    public function getProjectRelativeLogPath() : string
    {
        return '/storage/logs';
    }


    /**
     * Get connection configuration for given model
     * @param string $name
     * @return ConnectionConfig|null
     */
    public function getModelConnectionConfig(string $name) : ?ConnectionConfig
    {
        _used($name);

        return null;
    }


    /**
     * Get connection schema preference for given model
     * @param string $name
     * @return SchemaPreference|null
     */
    public function getModelSchemaPreference(string $name) : ?SchemaPreference
    {
        _used($name);

        return null;
    }


    /**
     * All directories containing PHP source code for Model
     * @return iterable<string>
     */
    public function getModelSourceDirectories() : iterable
    {
        return [];
    }


    /**
     * All directories containing PHP source code for Model that needs to synchronize schema to database
     * @return iterable<string>
     */
    public function getModelSourceSyncDirectories() : iterable
    {
        return $this->getModelSourceDirectories();
    }


    /**
     * Get the default client address resolver
     * @return ClientAddressesResolvable
     */
    public function getDefaultClientAddressesResolver() : ClientAddressesResolvable
    {
        return DefaultClientAddressesResolver::create();
    }


    /**
     * Get a default route handler when there is no domain defined to handle the request
     * @param Request $request
     * @return RouteHandleable
     */
    public function getDefaultRouteHandler(Request $request) : RouteHandleable
    {
        _used($request);

        return ClosureRouteHandler::for(function (Request $request) : mixed {
            _used($request);
            throw new HttpServiceUnavailableException();
        });
    }


    /**
     * Get intermediate renderer to render HttpResponseException
     * @return HttpResponseExceptionRenderable
     */
    public function getHttpResponseExceptionRenderer() : HttpResponseExceptionRenderable
    {
        return new DefaultHttpResponseExceptionRenderer();
    }


    /**
     * Get a renderer for given response
     * @param mixed $response
     * @return Renderable|null
     */
    public function getResponseRenderer(mixed $response) : ?Renderable
    {
        // Null may be handled as no-content
        if ($response === null) return new Response('', CommonHttpStatusCode::NO_CONTENT);

        // Support for serving binary content
        if ($response instanceof BinaryDataProvidable) return new BinaryContentResponse($response);

        // Support for string as HTML content
        if (is_string($response)) return new StringRenderer($response);
        if ($response instanceof Stringable) return new StringRenderer($response->__toString());

        // Otherwise, not supported
        return null;
    }


    /**
     * Create default console provider
     * @param callable(ConsoleCustomization):void|null $customizingFn
     * @return Consolable
     */
    public final function createDefaultConsolable(?callable $customizingFn = null) : Consolable
    {
        $custom = static::getDefaultConsoleCustomization();
        if ($customizingFn !== null) $customizingFn($custom);
        return $this->onCreateDefaultConsolable($custom);
    }


    /**
     * Actually creating default console provider
     * @param ConsoleCustomization $custom
     * @return Consolable
     */
    protected function onCreateDefaultConsolable(ConsoleCustomization $custom) : Consolable
    {
        return new SymfonyConsole($custom);
    }


    /**
     * Create default console customization
     * @return ConsoleCustomization
     */
    protected function getDefaultConsoleCustomization() : ConsoleCustomization
    {
        return ConsoleCustomization::default();
    }
}
