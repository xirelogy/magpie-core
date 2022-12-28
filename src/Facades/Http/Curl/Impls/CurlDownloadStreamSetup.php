<?php

namespace Magpie\Facades\Http\Curl\Impls;

use CurlHandle;
use Exception;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\Concepts\DownloadStreamWriteable;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\Facades\Http\Options\DownloadSetupClientRequestOption;

/**
 * Download stream setup for CURL
 * @internal
 */
class CurlDownloadStreamSetup
{
    /**
     * @var DownloadSetupClientRequestOption Associated download option
     */
    protected readonly DownloadSetupClientRequestOption $option;
    /**
     * @var string|null Request URL
     */
    protected ?string $url = null;
    /**
     * @var CurlReturnHeaders|null Return headers (as prepared)
     */
    protected ?CurlReturnHeaders $returnHeaders = null;
    /**
     * @var DownloadStreamWriteable|null Target stream
     */
    protected ?DownloadStreamWriteable $target = null;


    /**
     * Constructor
     * @param CurlHttpClientRequestOptionContext $context
     * @param DownloadSetupClientRequestOption $option
     * @throws ClientException
     */
    public function __construct(CurlHttpClientRequestOptionContext $context, DownloadSetupClientRequestOption $option)
    {
        $this->option = $option;

        $context->setOpt(CURLOPT_WRITEFUNCTION, $this->onCurlWrite(...));
    }


    /**
     * Prepare the return headers
     * @param string $url
     * @param CurlReturnHeaders $returnHeaders
     * @return void
     */
    public function prepare(string $url, CurlReturnHeaders $returnHeaders) : void
    {
        $this->url = $url;
        $this->returnHeaders = $returnHeaders;
    }


    /**
     * Handle CURL's write function
     * @param CurlHandle $ch
     * @param string $data
     * @return int
     * @throws Exception
     */
    protected function onCurlWrite(CurlHandle $ch, string $data) : int
    {
        $this->target = $this->ensureTarget($ch);

        $expected = strlen($data);
        $written = 0;

        do {
            $written += $this->target->write(substr($data, $written));
        } while ($written < $expected);

        return $written;
    }


    /**
     * Complete the download and return the body
     * @return HttpClientResponseBody
     * @throws SafetyCommonException
     */
    public function finalizeAsBody() : HttpClientResponseBody
    {
        if ($this->target === null) throw new InvalidStateException();

        return $this->target->finalizeAsBody();
    }


    /**
     * Ensure that target exist
     * @param CurlHandle $ch
     * @return DownloadStreamWriteable
     * @throws Exception
     */
    protected function ensureTarget(CurlHandle $ch) : DownloadStreamWriteable
    {
        _used($ch);

        if ($this->target === null) {
            if ($this->url === null || $this->returnHeaders === null) throw new InvalidStateException();
            $this->target = $this->option->call($this->url, $this->returnHeaders);
        }

        return $this->target;
    }
}