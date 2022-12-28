<?php

namespace Magpie\Facades\Http\Curl;

use Magpie\Cryptos\X509\Certificate;
use Magpie\Facades\Http\Curl\Impls\CurlReturnHeaders;
use Magpie\Facades\Http\HttpClientRequestTimeStatistics;
use Magpie\Facades\Http\HttpClientResponse;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\Facades\Http\HttpClientResponseHeaders;
use Magpie\Facades\Http\HttpProtocolVersion;
use Magpie\General\DateTimes\Duration;

/**
 * HTTP client response utilizing CURL
 */
abstract class CurlHttpClientResponse extends HttpClientResponse
{
    // String keys to info
    protected const INFO_CERTINFO = 'certinfo';
    protected const INFO_PRIMARY_IP = 'primary_ip';
    protected const INFO_LOCAL_IP = 'local_ip';
    protected const INFO_SCHEME = 'scheme';
    protected const INFO_HTTP_VERSION = 'http_version';
    protected const INFO_TIME_TOTAL = 'total_time';
    protected const INFO_TIME_TOTAL_US = 'total_time_us';
    protected const INFO_TIME_NAMELOOKUP = 'namelookup_time';
    protected const INFO_TIME_NAMELOOKUP_US = 'namelookup_time_us';
    protected const INFO_TIME_CONNECT = 'connect_time';
    protected const INFO_TIME_CONNECT_US = 'connect_time_us';
    protected const INFO_TIME_APPCONNECT = 'appconnect_time';
    protected const INFO_TIME_APPCONNECT_US = 'appconnect_time_us';

    /**
     * @var int HTTP status code
     */
    protected int $httpStatusCode;
    /**
     * @var array<int, mixed> Return metadata
     */
    protected array $returnInfo;
    /**
     * @var CurlReturnHeaders Return headers
     */
    protected CurlReturnHeaders $returnHeaders;
    /**
     * @var HttpClientResponseBody Content body
     */
    protected HttpClientResponseBody $body;


    /**
     * Constructor
     * @param int $httpStatusCode
     * @param array<int, mixed> $returnInfo
     * @param CurlReturnHeaders $returnHeaders
     * @param HttpClientResponseBody $body
     */
    protected function __construct(int $httpStatusCode, array $returnInfo, CurlReturnHeaders $returnHeaders, HttpClientResponseBody $body)
    {
        $this->httpStatusCode = $httpStatusCode;
        $this->returnInfo = $returnInfo;
        $this->returnHeaders = $returnHeaders;
        $this->body = $body;
    }


    /**
     * @inheritDoc
     */
    public function getScheme() : string
    {
        return strtolower($this->returnInfo[static::INFO_SCHEME]);
    }


    /**
     * @inheritDoc
     */
    public function getHttpVersion() : ?string
    {
        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($this->returnInfo[static::INFO_HTTP_VERSION] ?? null) {
            case CURL_HTTP_VERSION_1_0:
                return HttpProtocolVersion::VER_1_0;
            case CURL_HTTP_VERSION_1_1:
                return HttpProtocolVersion::VER_1_1;
            case CURL_HTTP_VERSION_2_0:
            case CURL_HTTP_VERSION_2TLS:
            case CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE:
                return HttpProtocolVersion::VER_2_0;
            default:
                return null;
        }
    }


    /**
     * @inheritDoc
     */
    public function getHttpStatusCode() : int
    {
        return $this->httpStatusCode;
    }


    /**
     * @inheritDoc
     */
    public function getHeaders() : HttpClientResponseHeaders
    {
        return $this->returnHeaders;
    }


    /**
     * @inheritDoc
     */
    public function getBody() : HttpClientResponseBody
    {
        return $this->body;
    }


    /**
     * @inheritDoc
     */
    public function getCertificates() : iterable
    {
        if (!array_key_exists(static::INFO_CERTINFO, $this->returnInfo)) return;

        foreach ($this->returnInfo[static::INFO_CERTINFO] as $certInfo) {
            if (!is_array($certInfo)) continue;
            if (!array_key_exists('Cert', $certInfo)) continue;

            yield Certificate::import($certInfo['Cert']);
        }
    }


    /**
     * @inheritDoc
     */
    public function getLocalAddress() : ?string
    {
        return $this->returnInfo[static::INFO_LOCAL_IP] ?? null;
    }


    /**
     * @inheritDoc
     */
    public function getRemoteAddress() : ?string
    {
        return $this->returnInfo[static::INFO_PRIMARY_IP] ?? null;
    }


    /**
     * @inheritDoc
     */
    public function getTimeStatistics() : HttpClientRequestTimeStatistics
    {
        $ret = new HttpClientRequestTimeStatistics();

        $ret->total = $this->getReturnInfoTime(static::INFO_TIME_TOTAL, static::INFO_TIME_TOTAL_US);
        $ret->lookup = $this->getReturnInfoTime(static::INFO_TIME_NAMELOOKUP, static::INFO_TIME_NAMELOOKUP_US);
        $ret->connect = $this->getReturnInfoTime(static::INFO_TIME_CONNECT, static::INFO_TIME_CONNECT_US);
        $ret->handshake = $this->getReturnInfoTime(static::INFO_TIME_APPCONNECT, static::INFO_TIME_APPCONNECT_US);

        return $ret;
    }


    /**
     * Sum the time information from return info
     * @param Duration|null ...$durations
     * @return Duration|null
     */
    protected function getReturnInfoSumTime(?Duration ...$durations) : ?Duration
    {
        $ret = null;

        foreach ($durations as $duration) {
            if ($duration === null) continue;

            if ($ret === null) {
                $ret = $duration;
            } else {
                $ret = $ret->add($duration);
            }
        }

        return $ret;
    }


    /**
     * Extract time information from return info
     * @param string $secKey
     * @param string $microKey
     * @return Duration|null
     */
    protected function getReturnInfoTime(string $secKey, string $microKey) : ?Duration
    {
        if (array_key_exists($microKey, $this->returnInfo)) {
            return Duration::inMicroseconds($this->returnInfo[$microKey]);
        }

        if (array_key_exists($secKey, $this->returnInfo)) {
            return Duration::inMicroseconds($this->returnInfo[$secKey]);
        }

        return null;
    }
}