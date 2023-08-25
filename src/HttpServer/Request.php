<?php

namespace Magpie\HttpServer;

use Exception;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Concepts\PrimitiveBinaryContentable;
use Magpie\General\Contents\PrimitiveFileBinaryContent;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\HttpServer\Concepts\ClientAddressesResolvable;
use Magpie\HttpServer\Concepts\UserCollectable;
use Magpie\HttpServer\Contents\PostBodyContent;
use Magpie\Objects\Uri;
use Magpie\Routes\Impls\ActualRouteContext;
use Magpie\Routes\Impls\ForwardingUserCollection;
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
    public readonly UserCollectable $domainArguments;
    /**
     * @var UserCollectable Route arguments
     */
    public readonly UserCollectable $routeArguments;
    /**
     * @var string|null The resolved hostname
     */
    public readonly ?string $hostname;
    /**
     * @var Uri Request URI
     */
    public readonly Uri $requestUri;
    /**
     * @var Uri Full request URI
     */
    public readonly Uri $fullUri;
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
     * @var bool If POST body should be suppressed
     */
    protected readonly bool $isSuppressBody;


    /**
     * Constructor
     * @param UserCollectable $queries
     * @param UserCollectable $posts
     * @param UserCollectable $cookies
     * @param ServerCollection $serverVars
     * @param bool $isSuppressBody
     */
    protected function __construct(UserCollectable $queries, UserCollectable $posts, UserCollectable $cookies, ServerCollection $serverVars, bool $isSuppressBody = false)
    {
        $this->domainArguments = new ForwardingUserCollection();
        $this->routeArguments = new ForwardingUserCollection();
        $this->routeContext = ActualRouteContext::_create($this->domainArguments, $this->routeArguments);
        $this->queries = $queries;
        $this->posts = $posts;
        $this->cookies = $cookies;
        $this->serverVars = $serverVars;
        $this->isSuppressBody = $isSuppressBody;
        $this->headers = $this->serverVars->getHeaders();

        $this->requestUri = Uri::safeParse($this->serverVars->safeOptional('REQUEST_URI', default: '/'));

        $scheme = static::resolveSchemeIsHttps($this->headers, $this->serverVars) ? 'https' : 'http';
        $this->hostname = static::resolveHostname($this->headers, $this->serverVars, $port);
        $this->fullUri = static::createFullUri($scheme, $this->hostname, $port, $this->requestUri);

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
        if ($this->isSuppressBody) return '';

        return static::readPostBody();
    }


    /**
     * Hostname
     * @return string|null
     * @deprecated
     */
    public function getHostname() : ?string
    {
        return $this->hostname;
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
        $serverVars = ServerCollection::capture();
        $queries = static::createUserCollectionFrom($_GET);
        $posts = static::createUserCollectionFrom(static::getPosts($serverVars, $isSuppressBody));
        $cookies = static::createUserCollectionFrom($_COOKIE);

        return new static($queries, $posts, $cookies, $serverVars, $isSuppressBody);
    }


    /**
     * All post variables and files
     * @param ServerCollection $serverVars
     * @param bool|null $isSuppressBody
     * @return iterable<string, array<string|PrimitiveBinaryContentable>|string|PrimitiveBinaryContentable>
     */
    private static function getPosts(ServerCollection $serverVars, ?bool &$isSuppressBody) : iterable
    {
        $requestMethod = $serverVars->safeOptional('REQUEST_METHOD', StringParser::createTrimEmptyAsNull());
        if ($requestMethod !== 'POST') {
            $body = static::readPostBody();
            yield from static::getPostsFromBody($serverVars, $body, $isSuppressBody);
            return;
        }

        $isSuppressBody = false;

        yield from $_POST;

        foreach ($_FILES as $fileKey => $fileDesc) {
            $fileName = $fileDesc['full_path'];
            $fileType = $fileDesc['type'];
            $filePath = $fileDesc['tmp_name'];

            if (is_array($fileName)) {
                $count = count($fileName);
                $outFiles = [];
                for ($i = 0; $i < $count; ++$i) {
                    $outFiles[] = static::wrapPostFile($fileName[$i], $fileType[$i], $filePath[$i]);
                }
                yield $fileKey => $outFiles;
            } else {
                yield $fileKey => static::wrapPostFile($fileName, $fileType, $filePath);
            }
        }
    }


    /**
     * All post variables and files from body
     * @param ServerCollection $serverVars
     * @param string $body
     * @param bool|null $isSuppressBody
     * @return iterable<string, array<string|PrimitiveBinaryContentable>|string|PrimitiveBinaryContentable>
     */
    private static function getPostsFromBody(ServerCollection $serverVars, string $body, ?bool &$isSuppressBody) : iterable
    {
        $isSuppressBody = false;

        $content = PostBodyContent::create($serverVars, $body);
        if ($content === null) return;

        $isSuppressBody = true;

        yield from $content->getVariables();
    }


    /**
     * Read the post body
     * @return string
     */
    private static function readPostBody() : string
    {
        $ret = @file_get_contents('php://input');
        if ($ret === false) $ret = '';
        return $ret;
    }


    /**
     * Wrap a post file
     * @param string $filename
     * @param string $type
     * @param string $path
     * @return PrimitiveBinaryContentable
     */
    private static function wrapPostFile(string $filename, string $type, string $path) : PrimitiveBinaryContentable
    {
        return new PrimitiveFileBinaryContent($path, $type, $filename);
    }


    /**
     * Resolve if the request scheme is HTTPS
     * @param HeaderCollection $headers
     * @param ServerCollection $serverVars
     * @return bool
     */
    protected static function resolveSchemeIsHttps(HeaderCollection $headers, ServerCollection $serverVars) : bool
    {
        $lowerStringParser = StringParser::createTrimEmptyAsNull()
            ->withPreprocessor(function (?string $value) : ?string {
                if ($value === null) return null;
                return strtolower($value);
            });

        // Determined according to forwarding headers
        if ($headers->safeOptional('X-Forwarded-Proto', $lowerStringParser) === 'https') return true;
        if ($headers->safeOptional('X-Forwarded-Port', IntegerParser::create()) === 443) return true;

        // Determined according to local server variables
        if ($serverVars->safeOptional('HTTPS', $lowerStringParser) === 'on') return true;
        if ($serverVars->safeOptional('REQUEST_SCHEME', $lowerStringParser) === 'https') return true;
        if ($serverVars->safeOptional('SERVER_PORT', IntegerParser::create()) === 443) return true;

        return false;
    }


    /**
     * Resolve for request hostname in order
     * @param HeaderCollection $headers
     * @param ServerCollection $serverVars
     * @param int|null $port
     * @return string|null
     */
    protected static function resolveHostname(HeaderCollection $headers, ServerCollection $serverVars, ?int &$port = null) : ?string
    {
        $port = null;

        $parser = StringParser::create()
            ->withEmptyAsNull()
            ->withPreprocessor(function (string $value) use (&$port) : string {
                $colonPos = strpos($value, ':');
                if ($colonPos !== false) {
                    $port = IntegerParser::create()->parse(substr($value, $colonPos + 1));
                    $value = substr($value, 0, $colonPos);
                }
                return $value;
            })
            ;

        return $headers->safeOptional(CommonHttpHeader::HOST, $parser)
            ?? $serverVars->safeOptional('SERVER_NAME', $parser)
            ?? $serverVars->safeOptional('SERVER_ADDR', $parser)
            ?? null;
    }


    /**
     * Create a full URI
     * @param string $scheme
     * @param string|null $hostname
     * @param int|null $port
     * @param Uri $requestUri
     * @return Uri
     */
    protected static function createFullUri(string $scheme, ?string $hostname, ?int $port, Uri $requestUri) : Uri
    {
        $ret = new Uri($requestUri->path);
        $ret->host = $hostname;
        $ret->port = $port;
        if ($hostname !== null) $ret->scheme = $scheme;
        $ret->query = $requestUri->query;

        return $ret;
    }


    /**
     * Create user inputs collection from given super global
     * @param iterable<string, mixed> $vars
     * @return UserCollectable
     */
    protected static function createUserCollectionFrom(iterable $vars) : UserCollectable
    {
        return new class($vars) extends Collection implements UserCollectable {
            /**
             * Constructor
             * @param iterable<string, mixed> $keyValues
             */
            public function __construct(iterable $keyValues)
            {
                parent::__construct(iter_flatten($keyValues));
            }
        };
    }
}