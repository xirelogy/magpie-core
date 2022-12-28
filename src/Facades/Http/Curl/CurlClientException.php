<?php

namespace Magpie\Facades\Http\Curl;

use Exception;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\System\Concepts\ExceptionContextLocalizable;
use Throwable;

/**
 * Exceptions related to HTTP client raised by CURL
 */
class CurlClientException extends ClientException implements ExceptionContextLocalizable
{
    /**
     * @var int CURL error code
     */
    protected int $curlError;
    /**
     * @var string CURL error message
     */
    protected string $curlMessage;


    /**
     * Constructor
     * @param int $curlError
     * @param string $curlMessage
     * @param Throwable|null $previous
     */
    public function __construct(int $curlError, string $curlMessage, ?Throwable $previous = null)
    {
        $message = static::formatMessage($curlError, $curlMessage);

        parent::__construct($message, $previous);

        $this->curlError = $curlError;
        $this->curlMessage = $curlMessage;
    }


    /**
     * @inheritDoc
     */
    public function exceptionLocalize() : Exception
    {
        return new static($this->curlError, $this->curlMessage, $this->getPrevious());
    }


    /**
     * Format message
     * @param int $curlError
     * @param string $curlMessage
     * @return string
     */
    protected static function formatMessage(int $curlError, string $curlMessage) : string
    {
        return "[$curlError] $curlMessage";
    }
}