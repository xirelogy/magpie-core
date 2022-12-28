<?php

namespace Magpie\Facades\Http\Curl\Impls;

use CurlHandle;
use CurlMultiHandle;
use Fiber;
use Magpie\Facades\Http\Curl\CurlClientException;
use Magpie\General\Concepts\Dispatchable;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Kernel\ExceptionHandler;
use Throwable;
use function curl_errno;
use function curl_error;
use function curl_multi_getcontent;
use function curl_multi_remove_handle;

/**
 * Dispatch indicating asynchronous execution is completed
 * @internal
 */
class CurlAsyncCompletedDispatch implements Dispatchable
{
    /**
     * @var CurlMultiHandle Associated CURL's multi handle
     */
    protected readonly CurlMultiHandle $mh;
    /**
     * @var int The result code (one of the CURLE_* constants)
     */
    protected readonly int $result;
    /**
     * @var CurlHandle CURL's handle
     */
    protected readonly CurlHandle $ch;
    /**
     * @var Fiber Destination fiber
     */
    protected readonly Fiber $fiber;


    /**
     * Constructor
     * @param CurlMultiHandle $mh
     * @param int $result
     * @param CurlHandle $ch
     * @param Fiber $fiber
     */
    public function __construct(CurlMultiHandle $mh, int $result, CurlHandle $ch, Fiber $fiber)
    {
        $this->mh = $mh;
        $this->result = $result;
        $this->ch = $ch;
        $this->fiber = $fiber;
    }


    /**
     * @inheritDoc
     */
    public function dispatch() : void
    {
        try {
            $this->onDispatch();
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        } finally {
            curl_multi_remove_handle($this->mh, $this->ch);
        }
    }


    /**
     * Actually dispatch the current item
     * @return void
     * @throws Throwable
     */
    protected function onDispatch() : void
    {
        if ($this->result === CURLE_OK) {
            // Operation is successful
            $content = curl_multi_getcontent($this->ch) ?? '';
            EasyFiber::resume($this->fiber, $content);
        } else {
            // Some errors
            $curlError = curl_errno($this->ch);
            $curlMessage = curl_error($this->ch);
            EasyFiber::throw($this->fiber, new CurlClientException($curlError, $curlMessage));
        }
    }
}