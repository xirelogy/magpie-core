<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Impls;

use ErrorException;
use Exception;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\DetailedOpenSslException;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\ErrorOpenSslException;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\GeneralOpenSslException;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\OpenSslException;
use Magpie\Cryptos\Providers\OpenSsl\Exceptions\SpecificOpenSslException;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Support for OpenSSL error handling
 * @internal
 */
class ErrorHandling
{
    use StaticClass;


    /**
     * Execute with proper error handling
     * @template T
     * @param callable():T $fn
     * @return T
     * @throws OpenSslException
     */
    public static function execute(callable $fn) : mixed
    {
        $scope = ExceptionHandler::setScopeErrorLevel();
        _used($scope);

        ErrorHandling::clearErrors();

        try {
            $ret = $fn();
        } catch (Exception $ex) {
            // Some exceptions are probably caused by E_WARNING/E_ERROR trigger
            throw static::filterErrorException($ex);
        }

        // Also check for any missed exception
        if ($ret === false) throw static::captureError();
        return $ret;
    }


    /**
     * Filter for ErrorException, which is how OpenSSL errors may be now reported
     * @param Exception $ex
     * @return OpenSslException
     */
    protected static function filterErrorException(Exception $ex) : OpenSslException
    {
        if (!$ex instanceof ErrorException) return new ErrorOpenSslException($ex);

        $source = null;
        $errorMessage = $ex->getMessage();

        $fnPos = strpos($errorMessage, '():');
        if ($fnPos !== false) {
            $lhs = substr($errorMessage, 0, $fnPos + 2);
            if (!str_contains($lhs, ' ')) {
                $source = $lhs;
                $errorMessage = substr($errorMessage, $fnPos + 3);
            }
        }

        return new SpecificOpenSslException($errorMessage, $source, previous: $ex);
    }


    /**
     * Clear all pending errors in the OpenSSL library
     * @return void
     */
    public static function clearErrors() : void
    {
        for (;;) {
            $err = openssl_error_string();
            if ($err === false) return;
        }
    }


    /**
     * Capture errors into an exception
     * @return OpenSslException
     */
    public static function captureError() : OpenSslException
    {
        $errors = static::getErrors();
        if ($errors === null) return new GeneralOpenSslException();

        return new DetailedOpenSslException($errors);
    }


    /**
     * Get all errors
     * @return array<string>|null
     */
    protected static function getErrors() : ?array
    {
        $ret = null;
        for (;;) {
            $err = openssl_error_string();
            if ($err === false) return $ret;

            if ($ret === null) $ret = [];
            $ret[] = $err;
        }
    }
}