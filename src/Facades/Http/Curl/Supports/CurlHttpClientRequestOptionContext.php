<?php

namespace Magpie\Facades\Http\Curl\Supports;

use CurlHandle;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\Curl\Impls\CurlDownloadStreamSetup;
use Magpie\Facades\Http\Curl\Impls\CurlSafeUtils;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;

/**
 * Context to process HTTP client request options for CURL
 */
abstract class CurlHttpClientRequestOptionContext extends HttpClientRequestOptionContext
{
    /**
     * @var CurlHandle Associated CURL handle
     */
    protected readonly CurlHandle $ch;


    /**
     * Constructor
     * @param CurlHandle $ch
     */
    protected function __construct(CurlHandle $ch)
    {
        $this->ch = $ch;
    }


    /**
     * Set the options on the given handle like `curl_setopt`
     * @param int $option
     * @param mixed $value
     * @return void
     * @throws ClientException
     */
    public function setOpt(int $option, mixed $value) : void
    {
        CurlSafeUtils::setOpt($this->ch, $option, $value);
    }


    /**
     * Translate crypto related content into CURL's option
     * @param CryptoFormatContent $content
     * @param int $pathOption
     * @param int|null $typeOption
     * @param int|null $passwordOption
     * @return iterable<int, mixed>
     * @throws SafetyCommonException
     */
    public abstract function translateCryptoContentOptions(CryptoFormatContent $content, int $pathOption, ?int $typeOption, ?int $passwordOption) : iterable;


    /**
     * Set the download setup
     * @param CurlDownloadStreamSetup $downloadSetup
     * @return void
     */
    public abstract function setDownloadSetup(CurlDownloadStreamSetup $downloadSetup) : void;
}