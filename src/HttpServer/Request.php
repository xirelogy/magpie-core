<?php

namespace Magpie\HttpServer;

use Exception;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\Concepts\UserCollectable;
use Magpie\Objects\Uri;
use Magpie\Routes\Impls\ActualRouteContext;
use Magpie\Routes\RouteContext;
use Magpie\Routes\RouteDomain;
use Magpie\System\Concepts\Capturable;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;

/**
 * Representation of an HTTP request made to this server
 */
class Request implements Capturable
{
    /**
     * @var RouteDomain|null Associated route domain
     */
    public ?RouteDomain $routeDomain;
    /**
     * @var RouteContext Associated route context
     */
    public readonly RouteContext $routeContext;
    /**
     * @var UserCollectable Domain arguments
     */
    public UserCollectable $domainArguments;
    /**
     * @var UserCollectable Route arguments
     */
    public UserCollectable $routeArguments;
    /**
     * @var Uri Request URI
     */
    public readonly Uri $requestUri;
    /**
     * @var UserCollectable Queries from request URL
     */
    public readonly UserCollectable $queries;
    /**
     * @var UserCollectable Form post variables
     */
    public readonly UserCollectable $posts;
    /**
     * @var UserCollectable Cookies supplied with the request
     */
    public readonly UserCollectable $cookies;
    /**
     * @var HeaderCollection Request headers
     */
    public readonly HeaderCollection $headers;
    /**
     * @var ServerCollection Server variables
     */
    public readonly ServerCollection $serverVars;
    /**
     * @var RequestState Associated request state
     */
    public readonly RequestState $state;


    /**
     * Constructor
     * @param UserCollectable $queries
     * @param UserCollectable $posts
     * @param UserCollectable $cookies
     * @param ServerCollection $serverVars
     */
    protected function __construct(UserCollectable $queries, UserCollectable $posts, UserCollectable $cookies, ServerCollection $serverVars)
    {
        $this->routeContext = new ActualRouteContext();
        $this->domainArguments = static::_createRouteArgumentsCollectionFrom([]);
        $this->routeArguments = static::_createRouteArgumentsCollectionFrom([]);
        $this->queries = $queries;
        $this->posts = $posts;
        $this->cookies = $cookies;
        $this->serverVars = $serverVars;
        $this->headers = $this->serverVars->getHeaders();

        $this->requestUri = Uri::safeParse($this->serverVars->safeOptional('REQUEST_URI', default: '/'));

        $this->state = new RequestState();
    }


    /**
     * Request method
     * @return string
     */
    public function getMethod() : string
    {
        return strtoupper($this->serverVars->safeOptional('REQUEST_METHOD', default: CommonHttpMethod::GET));
    }


    /**
     * Request body
     * @return string
     */
    public function getBody() : string
    {
        return file_get_contents('php://input');
    }


    /**
     * Hostname
     * @return string|null
     */
    public function getHostname() : ?string
    {
        $parser = StringParser::create()->withEmptyAsNull();

        return $this->headers->safeOptional(CommonHttpHeader::HOST, $parser)
            ?? $this->serverVars->safeOptional('SERVER_NAME', $parser)
            ?? $this->serverVars->safeOptional('SERVER_ADDR', $parser)
            ?? null;
    }


    /**
     * Client address (most relevant)
     * @return string|null
     */
    public function getClientAddress() : ?string
    {
        return iter_first($this->getClientAddresses());
    }


    /**
     * All client addresses
     * @return iterable<string>
     */
    public function getClientAddresses() : iterable
    {
        $directAddress = $this->serverVars->safeOptional('REMOTE_ADDR');

        yield from $this->getClientAddressesResolver()->resolveFrom($directAddress, $this->serverVars);
    }


    /**
     * Get client addresses resolver
     * @return ClientAddressesResolvable
     */
    protected function getClientAddressesResolver() : ClientAddressesResolvable
    {
        return $this->routeDomain?->_getClientAddressesResolver()
            ?? Kernel::current()->getConfig()->getDefaultClientAddressesResolver();
    }


    /**
     * @inheritDoc
     */
    public final static function capture() : static
    {
        try {
            return static::onCapture();
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }


    /**
     * Capture the current incoming HTTP server request
     * @return static
     * @throws Exception
     */
    protected static function onCapture() : static
    {
        $queries = static::createUserCollectionFrom($_GET);
        $posts = static::createUserCollectionFrom($_POST);
        $cookies = static::createUserCollectionFrom($_COOKIE);
        $serverVars = ServerCollection::capture();

        return new static($queries, $posts, $cookies, $serverVars);
    }


    /**
     * Create user inputs collection from given super global
     * @param array<string, mixed> $vars
     * @return UserCollectable
     */
    protected static function createUserCollectionFrom(array $vars) : UserCollectable
    {
        return new class($vars) extends Collection implements UserCollectable {
            /**
             * Constructor
             * @param array<string, mixed> $keyValues
             */
            public function __construct(array $keyValues)
            {
                parent::__construct($keyValues);
            }
        };
    }

    
    /**
     * Create route arguments collection
     * @param array<string, mixed> $vars
     * @return UserCollectable
     * @internal
     */
    public static final function _createRouteArgumentsCollectionFrom(array $vars) : UserCollectable
    {
        return new class($vars) extends Collection implements UserCollectable {
            /**
             * Constructor
             * @param array<string, mixed> $keyValues
             */
            public function __construct(array $keyValues)
            {
                parent::__construct($keyValues);
            }


            /**
             * @inheritDoc
             */
            public function fullKey(int|string $key) : string
            {
                $prefix = !is_empty_string($this->prefix) ? ($this->prefix . '.') : '';
                return $prefix . ":$key";
            }
        };
    }
}