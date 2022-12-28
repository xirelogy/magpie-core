<?php

namespace Magpie\HttpServer;

/**
 * Common HTTP methods
 */
enum CommonMethod : string
{
    /**
     * HTTP/GET
     */
    case GET = 'GET';
    /**
     * HTTP/POST
     */
    case POST = 'POST';
    /**
     * HTTP/PUT
     */
    case PUT = 'PUT';
    /**
     * HTTP/DELETE
     */
    case DELETE = 'DELETE';
    /**
     * HTTP/PATCH
     */
    case PATCH = 'PATCH';
    /**
     * HTTP/HEAD
     */
    case HEAD = 'HEAD';
    /**
     * HTTP/OPTIONS
     */
    case OPTIONS = 'OPTIONS';
}