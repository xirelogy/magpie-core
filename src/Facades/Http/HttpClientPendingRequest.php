<?php

namespace Magpie\Facades\Http;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedFeatureTypeClassException;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\Facades\Http\Traits\CommonPendingRequestOptions;
use Magpie\General\Concepts\TypeClassable;
use Magpie\Objects\ReleasableCollection;

/**
 * Pending HTTP client request
 */
abstract class HttpClientPendingRequest implements TypeClassable
{
    use CommonPendingRequestOptions;


    /**
     * @var array<string, mixed> Headers
     */
    protected array $headers;
    /**
     * @var HttpClientRequestBody|null Body to be sent along request
     */
    protected ?HttpClientRequestBody $body = null;
    /**
     * @var array<HttpClientRequestOptionProcessor> Specific option processors
     */
    protected array $optionProcessors;
    /**
     * @var bool If the request is currently pending
     */
    protected bool $isPending = true;
    /**
     * @var ReleasableCollection Resources to be released after request
     */
    protected ReleasableCollection $releasedAfterRequest;


    /**
     * Constructor
     * @param array<string, mixed> $parentHeaders
     * @param array<HttpClientRequestOptionProcessor> $parentOptions
     * @throws SafetyCommonException
     */
    protected function __construct(array $parentHeaders, array $parentOptions)
    {
        $this->releasedAfterRequest = new ReleasableCollection();
        $this->headers = $parentHeaders;

        $this->optionProcessors = [];
        foreach ($parentOptions as $parentOption) {
            $this->optionProcessors[] = $this->acceptOption($parentOption);
        }
    }


    /**
     * Add a header to be sent over with the request
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function withHeader(string $name, mixed $value) : static
    {
        $key = static::normalizeHeader($name);
        if (array_key_exists($key, $this->headers)) {
            if (is_array($value)) {
                $this->headers[$key] = array_merge($this->headers[$key], $value);
            } else {
                $this->headers[$key][] = $value;
            }
        } else {
            $this->headers[$key] = $value;
        }

        return $this;
    }


    /**
     * Add a header to be sent over with the request, only if it had not been added
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function withHeaderIfMissing(string $name, mixed $value) : static
    {
        $key = static::normalizeHeader($name);
        if (!array_key_exists($key, $this->headers)) return $this->withHeader($name, $value);

        return $this;
    }


    /**
     * Remove a header from the headers to be sent over with the request
     * @param string $name
     * @return $this
     */
    public function withoutHeader(string $name) : static
    {
        $key = static::normalizeHeader($name);
        unset($this->headers[$key]);

        return $this;
    }


    /**
     * Specify the request body to be sent along
     * @param HttpClientRequestBody $body
     * @return $this
     */
    public function withBody(HttpClientRequestBody $body) : static
    {
        $this->body = $body;
        return $this;
    }


    /**
     * Specify request options
     * @param HttpClientRequestOption ...$options
     * @return $this
     * @throws SafetyCommonException
     */
    public function withOption(HttpClientRequestOption ...$options) : static
    {
        foreach ($options as $option) {
            $this->optionProcessors[] = $this->acceptOption($option);
        }

        return $this;
    }


    /**
     * Make the request and get the corresponding response
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ClientException
     */
    public function request() : HttpClientResponse
    {
        if (!$this->isPending) throw new InvalidStateException();
        $this->isPending = false;

        try {
            return $this->onRequest();
        } finally {
            $this->releasedAfterRequest->release();
        }
    }


    /**
     * Handle making the request and get the corresponding response
     * @return HttpClientResponse
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ClientException
     */
    protected abstract function onRequest() : HttpClientResponse;


    /**
     * Accept option into option processor
     * @param HttpClientRequestOption $option
     * @return HttpClientRequestOptionProcessor
     * @throws ClassNotOfTypeException
     * @throws UnsupportedFeatureTypeClassException
     */
    protected function acceptOption(HttpClientRequestOption $option) : HttpClientRequestOptionProcessor
    {
        $httpTypeClass = $this->getTypeClass();
        return HttpClientRequestOptionProcessor::create($option, $httpTypeClass);
    }


    /**
     * Normalize method string
     * @param string $method
     * @return string
     */
    protected static function normalizeMethod(string $method) : string
    {
        return trim(strtoupper($method));
    }


    /**
     * Normalize header
     * @param string $headerName
     * @return string
     */
    protected static function normalizeHeader(string $headerName) : string
    {
        return trim(strtolower($headerName));
    }


    /**
     * Format header into a canonical name
     * @param string $headerName
     * @return string
     */
    protected static function canonicalHeader(string $headerName) : string
    {
        $headerParts = explode('-', $headerName);

        $outParts = [];
        foreach ($headerParts as $headerPart) {
            $outParts[] = strtoupper(substr($headerPart, 0, 1)) . strtolower(substr($headerPart, 1));
        }

        return implode('-', $outParts);
    }
}