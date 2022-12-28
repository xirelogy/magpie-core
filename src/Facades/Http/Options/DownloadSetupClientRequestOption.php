<?php

namespace Magpie\Facades\Http\Options;

use Closure;
use Magpie\Facades\Http\Concepts\DownloadStreamWriteable;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\Facades\Http\HttpClientResponseHeaders;

/**
 * Option to setup download stream
 */
class DownloadSetupClientRequestOption extends HttpClientRequestOption
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'download-setup';

    /**
     * @var Closure Setup function
     */
    protected readonly Closure $setupFn;


    /**
     * Constructor
     * @param callable(string,HttpClientResponseHeaders):DownloadStreamWriteable $setupFn
     */
    protected function __construct(callable $setupFn)
    {
        parent::__construct();

        $this->setupFn = $setupFn;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Call the setup function
     * @param string $url
     * @param HttpClientResponseHeaders $responseHeaders
     * @return DownloadStreamWriteable
     */
    public function call(string $url, HttpClientResponseHeaders $responseHeaders) : DownloadStreamWriteable
    {
        return ($this->setupFn)($url, $responseHeaders);
    }


    /**
     * Create a setup option
     * @param callable(string,HttpClientResponseHeaders):DownloadStreamWriteable $setupFn
     * @return static
     */
    public static function create(callable $setupFn) : static
    {
        return new static($setupFn);
    }
}