<?php

/** @noinspection PhpUnused */

namespace Magpie\General\Names;

use Magpie\General\Traits\StaticClass;

/**
 * Common HTTP status code
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
 */
class CommonHttpStatusCode
{
    use StaticClass;

    /**
     * HTTP/100 Continue
     */
    public const CONTINUE = 100;
    /**
     * HTTP/101 Switching Protocols
     */
    public const SWITCHING_PROTOCOLS = 101;
    /**
     * HTTP/200 OK
     */
    public const OK = 200;
    /**
     * HTTP/201 Created
     */
    public const CREATED = 201;
    /**
     * HTTP/202 Accepted
     */
    public const ACCEPTED = 202;
    /**
     * HTTP/203 Non-Authoritative Information
     */
    public const NON_AUTHORITATIVE = 203;
    /**
     * HTTP/204 No Content
     */
    public const NO_CONTENT = 204;
    /**
     * HTTP/205 Reset Content
     */
    public const RESET_CONTENT = 205;
    /**
     * HTTP/206 Partial Content
     */
    public const PARTIAL_CONTENT = 206;
    /**
     * HTTP/300 Multiple Choices
     */
    public const MULTIPLE_CHOICES = 300;
    /**
     * HTTP/301 Moved Permanently
     */
    public const MOVED_PERMANENTLY = 301;
    /**
     * HTTP/302 Found
     */
    public const FOUND = 302;
    /**
     * HTTP/303 See Other
     */
    public const SEE_OTHER = 303;
    /**
     * HTTP/304 Not Modified
     */
    public const NOT_MODIFIED = 304;
    /**
     * HTTP/307 Temporary Redirect
     */
    public const TEMPORARY_REDIRECT = 307;
    /**
     * HTTP/308 Permanent Redirect
     */
    public const PERMANENT_REDIRECT = 308;
    /**
     * HTTP/400 Bad Request
     */
    public const BAD_REQUEST = 400;
    /**
     * HTTP/401 Unauthorized
     */
    public const UNAUTHORIZED = 401;
    /**
     * HTTP/403 Forbidden
     */
    public const FORBIDDEN = 403;
    /**
     * HTTP/404 Not Found
     */
    public const NOT_FOUND = 404;
    /**
     * HTTP/405 Method Not Allowed
     */
    public const METHOD_NOT_ALLOWED = 405;
    /**
     * HTTP/406 Not Acceptable
     */
    public const NOT_ACCEPTABLE = 406;
    /**
     * HTTP/407 Proxy Authentication Required
     */
    public const PROXY_AUTH_REQUIRED = 407;
    /**
     * HTTP/408 Request Timeout
     */
    public const REQUEST_TIMEOUT = 408;
    /**
     * HTTP/409 Conflict
     */
    public const CONFLICT = 409;
    /**
     * HTTP/410 Gone
     */
    public const GONE = 410;
    /**
     * HTTP/411 Length Required
     */
    public const LENGTH_REQUIRED = 411;
    /**
     * HTTP/412 Precondition Failed
     */
    public const PRECONDITION_FAILED = 412;
    /**
     * HTTP/413 Payload Too Large
     */
    public const PAYLOAD_TOO_LARGE = 413;
    /**
     * HTTP/414 URI Too Long
     */
    public const URI_TOO_LONG = 414;
    /**
     * HTTP/415 Unsupported Media Type
     */
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    /**
     * HTTP/416 Range Not Satisfiable
     */
    public const RANGE_NOT_SATISFIABLE = 416;
    /**
     * HTTP/417 Expectation Failed
     */
    public const EXPECTATION_FAILED = 417;
    /**
     * HTTP/426 Upgrade Required
     */
    public const UPGRADE_REQUIRED = 426;
    /**
     * HTTP/427 Precondition Required
     */
    public const PRECONDITION_REQUIRED = 427;
    /**
     * HTTP/429 Too Many Requests
     */
    public const TOO_MANY_REQUESTS = 429;
    /**
     * HTTP/431 Request Header Fields Too Large
     */
    public const HEADER_FIELDS_TOO_LARGE = 431;
    /**
     * HTTP/500 Internal Server Error
     */
    public const INTERNAL_SERVER_ERROR = 500;
    /**
     * HTTP/501 Not Implemented
     */
    public const NOT_IMPLEMENTED = 501;
    /**
     * HTTP/502 Bad Gateway
     */
    public const BAD_GATEWAY = 502;
    /**
     * HTTP/503 Service Unavailable
     */
    public const SERVICE_UNAVAILABLE = 503;
    /**
     * HTTP/504 Gateway Timeout
     */
    public const GATEWAY_TIMEOUT = 504;
    /**
     * HTTP/505 HTTP Version Not Supported
     */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    /**
     * HTTP/506 Variant Also Negotiates
     */
    public const VARIANT_ALSO_NEGOTIATES = 506;
    /**
     * HTTP/510 Not Extended
     */
    public const NOT_EXTENDED = 510;
    /**
     * HTTP/511 Network Authentication Required
     */
    public const NETWORK_AUTH_REQUIRED = 511;


    /**
     * If the given HTTP status code is considered as successful
     * @param int $code
     * @return bool
     */
    public static function isSuccessful(int $code) : bool
    {
        return $code >= 100 && $code <= 399;
    }
}