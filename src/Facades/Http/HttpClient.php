<?php

namespace Magpie\Facades\Http;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Http\Traits\CommonPendingRequestOptions;
use Magpie\General\Concepts\LogContainable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\System\Concepts\SystemBootable;

/**
 * HTTP client
 */
abstract class HttpClient implements TypeClassable, LogContainable, SystemBootable
{
    use CommonPendingRequestOptions;


    /**
     * @var array<string, mixed> Headers to be inherited by all requests
     */
    protected array $headers = [];
    /**
     * @var array<HttpClientRequestOption> Specific options to be inherited by all requests
     */
    protected array $options = [];


    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Prepare a pending request
     * @param string $method
     * @param string $url
     * @return HttpClientPendingRequest
     * @throws SafetyCommonException
     */
    public abstract function prepare(string $method, string $url) : HttpClientPendingRequest;


    /**
     * Perform request
     * @param string $method
     * @param string $url
     * @param HttpClientRequestBody|array|null $body
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    protected function request(string $method, string $url, HttpClientRequestBody|array|null $body, array $headers = [], array $options = []) : HttpClientResponse
    {
        $requester = $this->prepare($method, $url);
        if ($body !== null) {
            $body = static::normalizeBody($body);
            $requester->withBody($body);
        }

        foreach ($headers as $headerName => $headerValue) {
            $requester->withHeader($headerName, $headerValue);
        }
        foreach ($options as $option) {
            $requester->withOption($option);
        }

        return $requester->request();
    }


    /**
     * Perform GET request
     * @param string $url
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function get(string $url, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::GET, $url, null, $headers, $options);
    }


    /**
     * Perform POST request
     * @param string $url
     * @param HttpClientRequestBody|array|null $body
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function post(string $url, HttpClientRequestBody|array|null $body, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::POST, $url, $body, $headers, $options);
    }


    /**
     * Perform PUT request
     * @param string $url
     * @param HttpClientRequestBody|array|null $body
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function put(string $url, HttpClientRequestBody|array|null $body, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::PUT, $url, $body, $headers, $options);
    }


    /**
     * Perform DELETE request
     * @param string $url
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function delete(string $url, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::DELETE, $url, null, $headers, $options);
    }


    /**
     * Perform OPTIONS request
     * @param string $url
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function options(string $url, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::OPTIONS, $url, null, $headers, $options);
    }


    /**
     * Perform HEAD request
     * @param string $url
     * @param array<string, mixed> $headers
     * @param array<HttpClientRequestOption> $options
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws Exceptions\ClientException
     */
    public function head(string $url, array $headers = [], array $options = []) : HttpClientResponse
    {
        return $this->request(CommonHttpMethod::HEAD, $url, null, $headers, $options);
    }


    /**
     * Specify request options
     * @param HttpClientRequestOption ...$options
     * @return $this
     */
    public function withOption(HttpClientRequestOption ...$options) : static
    {
        foreach ($options as $option) {
            $this->options[] = $option;
        }

        return $this;
    }


    /**
     * Initialize a client
     * @param string|null $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(?string $typeClass = null) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize();
    }


    /**
     * Initialize a client specifically for this type of adaptation
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function specificInitialize() : static;


    /**
     * Normalize a body
     * @param HttpClientRequestBody|array|mixed $body
     * @return HttpClientRequestBody
     * @throws SafetyCommonException
     */
    protected static function normalizeBody(mixed $body) : HttpClientRequestBody
    {
        if ($body instanceof HttpClientRequestBody) return $body;
        if (is_array($body)) return HttpClientRequestBody::form($body);

        throw new UnsupportedValueException($body, _l('request body'));
    }
}