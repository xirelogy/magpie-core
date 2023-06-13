<?php

namespace Magpie\Facades\Http\Curl;

use Closure;
use CURLFile;
use CurlHandle;
use Exception;
use Fiber;
use Magpie\Cryptos\Contents\CryptoFormatContent;
use Magpie\Cryptos\Contents\DerCryptoFormatContent;
use Magpie\Cryptos\Contents\PemCryptoFormatContent;
use Magpie\Cryptos\Contents\Pkcs12CryptoFormatContent;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Http\Bodies\HttpEncodedClientRequestBody;
use Magpie\Facades\Http\Bodies\HttpFormClientRequestBody;
use Magpie\Facades\Http\Bodies\HttpSimpleClientResponseBody;
use Magpie\Facades\Http\Curl\Impls\CurlAsyncPoll;
use Magpie\Facades\Http\Curl\Impls\CurlDownloadStreamSetup;
use Magpie\Facades\Http\Curl\Impls\CurlLogContainer;
use Magpie\Facades\Http\Curl\Impls\CurlReturnHeaders;
use Magpie\Facades\Http\Curl\Impls\CurlSafeUtils;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\HttpClientPendingRequest;
use Magpie\Facades\Http\HttpClientRequestBody;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\Facades\Http\HttpClientResponse;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\Facades\Http\Options\DownloadSetupClientRequestOption;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Contents\BinaryContent;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonMimeType;
use Magpie\Logs\Concepts\Loggable;
use function curl_exec;
use function curl_getinfo;
use function curl_init;

/**
 * Pending HTTP client request utilizing CURL
 */
abstract class CurlHttpClientPendingRequest extends HttpClientPendingRequest
{
    /**
     * @var string Request method
     */
    protected string $method;
    /**
     * @var string Request URL
     */
    protected string $url;
    /**
     * @var array<string, string> Headers explicitly to be blacklisted
     */
    protected array $blacklistHeaders = [];
    /**
     * @var CurlDownloadStreamSetup|null Download setup
     */
    protected ?CurlDownloadStreamSetup $downloadSetup = null;
    /**
     * @var Loggable|null Associated logger
     */
    protected readonly ?Loggable $logger;


    /**
     * Constructor
     * @param string $method
     * @param string $url
     * @param array<string, mixed> $parentHeaders
     * @param array<HttpClientRequestOption> $parentOptions
     * @param Loggable|null $logger
     * @throws SafetyCommonException
     */
    protected function __construct(string $method, string $url, array $parentHeaders, array $parentOptions, ?Loggable $logger)
    {
        parent::__construct($parentHeaders, $parentOptions);

        $this->method = static::normalizeMethod($method);
        $this->url = $url;
        $this->downloadSetup = null;
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return CurlHttpClient::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected function onRequest() : HttpClientResponse
    {
        $ch = static::getHandle();

        $logContainer = null;
        if ($this->logger !== null) {
            $logContainer = new CurlLogContainer($this->logger);
            CurlSafeUtils::setOpt($ch, CURLOPT_STDERR, $logContainer->getHandle());
            CurlSafeUtils::setOpt($ch, CURLOPT_VERBOSE, true);
        } else {
            CurlSafeUtils::setOpt($ch, CURLOPT_VERBOSE, false);
        }

        try {
            // Setup body
            $this->onRequestBody($ch, $this->body);

            // Setup request method
            CurlSafeUtils::setOpt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

            // Extra handling when body does not exist
            if ($this->body === null) {
                switch ($this->method) {
                    case 'POST':
                    case 'PUT':
                        $this->withHeaderIfMissing(CommonHttpHeader::CONTENT_LENGTH, 0);
                        break;
                    case 'HEAD':
                        CurlSafeUtils::setOpt($ch, CURLOPT_NOBODY, true);
                        break;
                }
            }

            // Setup URL
            CurlSafeUtils::setOpt($ch, CURLOPT_URL, $this->url);

            // Setup CURL handling
            CurlSafeUtils::setOpt($ch, CURLOPT_FAILONERROR, false);
            CurlSafeUtils::setOpt($ch, CURLOPT_CERTINFO, true);
            CurlSafeUtils::setOpt($ch, CURLOPT_RETURNTRANSFER, true);

            // Setup how return headers are handled
            $returnHeaders = CurlReturnHeaders::from($ch);

            // Apply options
            $optionContext = $this->createClientRequestOptionContext($ch);
            foreach ($this->optionProcessors as $optionProcessor) {
                $optionProcessor->apply($optionContext);
            }

            // Some other headers setup
            $this->withBlacklistHeader(CommonHttpHeader::ACCEPT);

            // Apply headers
            $this->onRequestHeaders($ch);

            // Prepare the download setup whenever required
            $this->downloadSetup?->prepare($this->url, $returnHeaders);

            // Execute the request
            $bodyContent = $this->onExecute($ch);

            $body = $this->downloadSetup !== null ?
                $this->downloadSetup->finalizeAsBody() :
                HttpSimpleClientResponseBody::fromContent($bodyContent);

            // Extract other information
            $httpStatusCode = intval(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
            $returnInfo = curl_getinfo($ch);

            return new class($httpStatusCode, $returnInfo, $returnHeaders, $body) extends CurlHttpClientResponse {
                /**
                 * Constructor
                 * @param int $httpStatusCode
                 * @param array<int, mixed> $returnInfo
                 * @param CurlReturnHeaders $returnHeaders
                 * @param HttpClientResponseBody $body
                 */
                public function __construct(int $httpStatusCode, array $returnInfo, CurlReturnHeaders $returnHeaders, HttpClientResponseBody $body)
                {
                    parent::__construct($httpStatusCode, $returnInfo, $returnHeaders, $body);
                }
            };
        } finally {
            $logContainer?->finalize();
        }
    }


    /**
     * Execute request for the given handle
     * @param CurlHandle $ch
     * @return string
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ClientException
     */
    protected function onExecute(CurlHandle $ch) : string
    {
        $currentFiber = Fiber::getCurrent();

        if ($currentFiber !== null) {
            // Fiber exist, run in asynchronous context
            try {
                return CurlAsyncPoll::instance()->asyncExec($ch, $currentFiber);
            } catch (ClientException|SafetyCommonException|PersistenceException|StreamException $ex) {
                throw $ex;
            } catch (Exception $ex) {
                throw new OperationFailedException(previous: $ex);
            }
        } else {
            // No fiber, run in synchronous context
            $ret = curl_exec($ch);
            CurlSafeUtils::checkResult($ch, $ret);
            return $ret;
        }
    }


    /**
     * Process the request headers
     * @param CurlHandle $ch
     * @return void
     * @throws ClientException
     */
    protected function onRequestHeaders(CurlHandle $ch) : void
    {
        $outHeaders = [];

        // Process the headers to be sent
        foreach ($this->headers as $headerKey => $headerValue) {
            $headerKey = static::canonicalHeader($headerKey);

            if (is_array($headerValue)) {
                foreach ($headerValue as $value) {
                    if ($value !== '') {
                        $outHeaders[] = "$headerKey: $value";
                    } else {
                        $outHeaders[] = "$headerKey;";
                    }
                }
            } else {
                $headerValue = "$headerValue";  // Forced to become string
                if ($headerValue !== '') {
                    $outHeaders[] = "$headerKey: $headerValue";
                } else {
                    $outHeaders[] = "$headerKey;";
                }
            }
        }

        // Blacklist headers
        foreach ($this->blacklistHeaders as $blacklistHeader) {
            $blacklistHeader = static::canonicalHeader($blacklistHeader);
            $outHeaders[] = "$blacklistHeader:";
        }

        // Apply to handle
        CurlSafeUtils::setOpt($ch, CURLOPT_HTTPHEADER, $outHeaders);
    }


    /**
     * Process the request body
     * @param CurlHandle $ch
     * @param HttpClientRequestBody|null $body
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ClientException
     */
    protected function onRequestBody(CurlHandle $ch, ?HttpClientRequestBody $body) : void
    {
        if ($body === null) return;

        if ($body instanceof HttpFormClientRequestBody) {
            // Processed as a form
            $keyValues = $this->acceptFormBody($body, $isStringOnly);

            if ($isStringOnly) {
                CurlSafeUtils::setOpt($ch, CURLOPT_POSTFIELDS, static::urlEncodeFormBody($keyValues));
                $this->withOverriddenHeader(CommonHttpHeader::CONTENT_TYPE, CommonMimeType::FORM_URLENCODED);
                $this->withoutHeader(CommonHttpHeader::CONTENT_LENGTH);
                $this->withoutHeader(CommonHttpHeader::TRANSFER_ENCODING);
            } else {
                CurlSafeUtils::setOpt($ch, CURLOPT_POSTFIELDS, $keyValues);
            }
        } else if ($body instanceof HttpEncodedClientRequestBody) {
            // Process as encoded body
            CurlSafeUtils::setOpt($ch, CURLOPT_POSTFIELDS, $body->getEncodedBody());
            $this->withOverriddenHeader(CommonHttpHeader::CONTENT_TYPE, $body->getContentType());
            $this->withOverriddenHeader(CommonHttpHeader::CONTENT_LENGTH, $body->getContentLength());
        } else {
            // Not supported
            throw new UnsupportedValueException($body, _l('request body'));
        }

        // Prevent some CURL defaults
        $this->withBlacklistHeader(CommonHttpHeader::EXPECT);
    }


    /**
     * Flatten the key values into URL encoded string
     * @param array $keyValues
     * @return string
     */
    protected static function urlEncodeFormBody(array $keyValues) : string
    {
        return http_build_query($keyValues);
    }


    /**
     * Process and accept key values
     * @param HttpFormClientRequestBody $formBody
     * @param bool|null $isStringOnly
     * @return array<string, string|CURLFile>
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected function acceptFormBody(HttpFormClientRequestBody $formBody, ?bool &$isStringOnly = null) : array
    {
        $outKeyValues = [];
        foreach ($formBody->checkKeyValues($isStringOnly) as $key => $value) {
            $outKeyValues[$key] = $this->acceptFormBodyValue($value);
        }

        return $outKeyValues;
    }


    /**
     * Process and accept values
     * @param string|BinaryDataProvidable $value
     * @return string|CURLFile
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected function acceptFormBodyValue(string|BinaryDataProvidable $value) : string|CURLFile
    {
        if (is_string($value)) return $value;

        $value = BinaryContent::getFileSystemAccessible($value, $isReleasable);
        if ($isReleasable) $this->releasedAfterRequest->addIfReleasable($value);

        return new CURLFile(
            $value->getFileSystemPath(),
            $value->getMimeType() ?? '',
            $value->getFilename() ?? '',
        );
    }


    /**
     * With header overridden, removing any existing customization
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected function withOverriddenHeader(string $name, mixed $value) : static
    {
        return $this
            ->withoutHeader($name)
            ->withHeader($name, $value)
            ;
    }


    /**
     * Set header to blacklist
     * @param string $name
     * @param bool $isForceRemove
     * @return $this
     */
    protected function withBlacklistHeader(string $name, bool $isForceRemove = false) : static
    {
        $key = static::normalizeHeader($name);

        if ($isForceRemove) {
            // Force removing the header if previously defined, so will remove and blacklist
            $this->withoutHeader($name);
        } else {
            // Otherwise, will not proceed to blacklist if defined
            if (array_key_exists($key, $this->headers)) return $this;
        }

        $this->blacklistHeaders[$key] = $name;
        return $this;
    }


    /**
     * Get or create handle
     * @return CurlHandle
     * @throws SafetyCommonException
     */
    protected static function getHandle() : CurlHandle
    {
        $ch = curl_init();
        if ($ch === false) throw new OperationFailedException(_l('Cannot initialize CURL'));

        // Reset the handle
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, null);
        curl_reset($ch);

        return $ch;
    }



    /**
     * Create context to process HttpClientRequestOption
     * @param CurlHandle $ch
     * @return CurlHttpClientRequestOptionContext
     */
    protected function createClientRequestOptionContext(CurlHandle $ch) : CurlHttpClientRequestOptionContext
    {
        $translateCryptoContentOptionsFn = $this->translateCryptoContentOptions(...);
        $setDownloadSetupFn = function(CurlDownloadStreamSetup $downloadSetup) {
            if ($this->downloadSetup !== null) throw new DuplicatedKeyException(DownloadSetupClientRequestOption::TYPECLASS);
            $this->downloadSetup = $downloadSetup;
        };

        return new class($ch, $translateCryptoContentOptionsFn, $setDownloadSetupFn) extends CurlHttpClientRequestOptionContext {
            /**
             * @var Closure Closure to invoke translateCryptoContentOptions()
             */
            protected Closure $translateCryptoContentOptionsFn;
            /**
             * @var Closure Closure to invoke setDownloadSetup()
             */
            protected Closure $setDownloadSetupFn;


            /**
             * Constructor
             * @param CurlHandle $ch
             * @param callable(CryptoFormatContent,int,int|null,int|null):iterable $translateCryptoContentOptionsFn
             * @param callable(CurlDownloadStreamSetup):void $setDownloadSetupFn
             */
            public function __construct(CurlHandle $ch, callable $translateCryptoContentOptionsFn, callable $setDownloadSetupFn)
            {
                parent::__construct($ch);

                $this->translateCryptoContentOptionsFn = $translateCryptoContentOptionsFn;
                $this->setDownloadSetupFn = $setDownloadSetupFn;
            }


            /**
             * @inheritDoc
             * @noinspection PhpRedundantCatchClauseInspection
             */
            public function translateCryptoContentOptions(CryptoFormatContent $content, int $pathOption, ?int $typeOption, ?int $passwordOption) : iterable
            {
                try {
                    yield from ($this->translateCryptoContentOptionsFn)($content, $pathOption, $typeOption, $passwordOption);
                } catch (PersistenceException|StreamException $ex) {
                    throw new OperationFailedException(previous: $ex);
                }
            }


            /**
             * @inheritDoc
             */
            public function setDownloadSetup(CurlDownloadStreamSetup $downloadSetup) : void
            {
                ($this->setDownloadSetupFn)($downloadSetup);
            }
        };
    }


    /**
     * Translate crypto related content into CURL's option
     * @param CryptoFormatContent $content
     * @param int $pathOption
     * @param int|null $typeOption
     * @param int|null $passwordOption
     * @return iterable<int, mixed>
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    protected function translateCryptoContentOptions(CryptoFormatContent $content, int $pathOption, ?int $typeOption, ?int $passwordOption) : iterable
    {
        $file = BinaryContent::getFileSystemAccessible($content->data, $isReleasable);
        if ($isReleasable) $this->releasedAfterRequest->addIfReleasable($file);

        yield $pathOption => $file->getFileSystemPath();

        if ($typeOption !== null) {
            $curlType = match ($content::getTypeClass()) {
                PemCryptoFormatContent::TYPECLASS => 'PEM',
                DerCryptoFormatContent::TYPECLASS => 'DER',
                Pkcs12CryptoFormatContent::TYPECLASS => 'P12',
                default => null,
            };

            if ($curlType !== null) {
                yield $typeOption => $curlType;
            }
        }

        if ($passwordOption !== null && !is_empty_string($content->password)) {
            yield $passwordOption => $content->password;
        }
    }
}