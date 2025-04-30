<?php

namespace Magpie\Controllers;

use Exception;
use Magpie\Codecs\Formats\Formatter;
use Magpie\Codecs\Formats\JsonGeneralFormatter;
use Magpie\Codecs\ParserHosts\ObjectParserHost;
use Magpie\Codecs\ParserHosts\ParserHost;
use Magpie\Controllers\Concepts\ControllerCallable;
use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Contexts\ScopedCollection;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\HttpServer\CommonRenderable;
use Magpie\HttpServer\Exceptions\HttpResponseException;
use Magpie\HttpServer\JsonResponse;
use Magpie\HttpServer\Request;
use Magpie\HttpServer\Response;

/**
 * A controller to serve web request by providing JSON response, normally as an API endpoint
 */
abstract class JsonApiController extends Controller
{
    /**
     * @inheritDoc
     */
    protected final function onCall(ControllerCallable $callable, Request $request, array $routeArguments) : CommonRenderable
    {
        $scopes = new ScopedCollection($this->onSetupContextScopesBeforeCall($request, $routeArguments));

        try {
            $this->onBeforeCall($request, $routeArguments);
            $response = $callable->call($request, $routeArguments);
            $response = $this->onAfterCall($request, $routeArguments, $response);
            $response = static::_createResponse($this->createResponseFormatter(), $response);
            $response = $this->onAfterResponse($response);
            $scopes->succeeded();
            return $response;
        } catch (HttpResponseException $ex) {
            $scopes->crash($ex);
            return $this->onHandleHttpResponseException($ex);
        } catch (Exception $ex) {
            $scopes->crash($ex);
            $httpStatusCode = $this->getExceptionHttpStatusCode($ex);
            $payload = $this->createExceptionPayload($ex);
            $response = $this->createExceptionResponse($payload, $httpStatusCode);
            return Excepts::noThrow(fn () => $this->onAfterResponse($response), $response);
        } finally {
            $scopes->release();
        }
    }


    /**
     * Setup all the scopes
     * @param Request $request
     * @param array $routeArguments
     * @return iterable<Scoped>
     */
    protected function onSetupContextScopesBeforeCall(Request $request, array $routeArguments) : iterable
    {
        _used($request, $routeArguments);

        return [];
    }


    /**
     * Handle before route call
     * @param Request $request
     * @param array $routeArguments
     * @return void
     * @throws Exception
     */
    protected function onBeforeCall(Request $request, array $routeArguments) : void
    {

    }


    /**
     * Handle after route call
     * @param Request $request
     * @param array $routeArguments
     * @param mixed $response
     * @return mixed
     * @throws Exception
     */
    protected function onAfterCall(Request $request, array $routeArguments, mixed $response) : mixed
    {
        _used($request, $routeArguments);
        return $response;
    }


    /**
     * Handle response after formatting
     * @param CommonRenderable $response
     * @return CommonRenderable
     * @throws Exception
     */
    protected function onAfterResponse(CommonRenderable $response) : CommonRenderable
    {
        return $response;
    }


    /**
     * Handle HTTP response exception
     * @param HttpResponseException $ex
     * @return CommonRenderable
     * @throws Exception
     */
    protected function onHandleHttpResponseException(HttpResponseException $ex) : CommonRenderable
    {
        throw $ex;
    }


    /**
     * Create a parser host from given request
     * @param Request $request
     * @return ParserHost
     * @throws SafetyCommonException
     */
    protected static function createParserHostFromRequest(Request $request) : ParserHost
    {
        $contentType = $request->headers->optional(CommonHttpHeader::CONTENT_TYPE);
        $body = $request->getBody();

        // When content is provided URL encoded form, cannot be handled like JSON
        if ($contentType == CommonMimeType::FORM_URLENCODED) {
            if (!static::isMergeFormContentIntoBody()) throw new UnsupportedException();
            $decoded = obj();
            foreach ($request->posts->all() as $varKey => $value) {
                $decoded->{$varKey} = $value;
            }

            return new ObjectParserHost($decoded);
        }

        // Try to assume JSON received
        $decoded = $body !== '' ? SimpleJSON::decode($request->getBody()) : obj();
        if (!is_object($decoded)) throw new NotOfTypeException($decoded, _l('JSON object'));

        // May merge in from POST like an object
        if (static::isMergeFormContentIntoBody()) {
            foreach ($request->posts->all() as $varKey => $value) {
                if (isset($decoded->{$varKey})) continue;   // Merge but not override
                $decoded->{$varKey} = $value;
            }
        }

        return new ObjectParserHost($decoded);
    }


    /**
     * If to merge form content into request body
     * @return bool
     */
    protected static function isMergeFormContentIntoBody() : bool
    {
        return true;
    }


    /**
     * Get corresponding HTTP status code for given exception
     * @param Exception $ex
     * @return int|null
     */
    protected function getExceptionHttpStatusCode(Exception $ex) : ?int
    {
        $code = $ex->getCode();
        if (!is_int($code)) return null;
        if ($code <= 0) return null;

        return $code;
    }


    /**
     * Create the response payload for exception
     * @param Exception $ex
     * @return mixed
     */
    protected abstract function createExceptionPayload(Exception $ex) : mixed;


    /**
     * Create exception response
     * @param mixed $payload
     * @param int|null $httpStatusCode
     * @return CommonRenderable|Response
     * @throws InvalidJsonDataFormatException
     */
    protected function createExceptionResponse(mixed $payload, ?int $httpStatusCode = null) : CommonRenderable|Response
    {
        $httpStatusCode = $httpStatusCode ?? $this->createExceptionHttpStatusCode();
        return static::_createResponse($this->createResponseFormatter(), $payload, $httpStatusCode);
    }


    /**
     * The status code to response for exception
     * @return int|null
     */
    protected function createExceptionHttpStatusCode() : ?int
    {
        return CommonHttpStatusCode::BAD_REQUEST;
    }


    /**
     * Create a formatter to format the payload before converting to JSON
     * @return Formatter
     */
    protected function createResponseFormatter() : Formatter
    {
        return JsonGeneralFormatter::create();
    }


    /**
     * Create an API response
     * @param Formatter $formatter
     * @param mixed $payload
     * @param int|null $httpStatusCode
     * @return CommonRenderable
     * @throws InvalidJsonDataFormatException
     * @internal
     */
    private static function _createResponse(Formatter $formatter, mixed $payload, ?int $httpStatusCode = null) : CommonRenderable
    {
        // Do not format the payload if it is already renderable
        if ($payload instanceof CommonRenderable) return $payload;

        $payload = $formatter->format($payload);

        return new JsonResponse($payload, $httpStatusCode);
    }
}