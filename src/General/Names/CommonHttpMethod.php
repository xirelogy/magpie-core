<?php

/** @noinspection PhpUnused */

namespace Magpie\General\Names;

use Magpie\General\Traits\StaticClass;

/**
 * Common HTTP methods
 */
class CommonHttpMethod
{
    use StaticClass;


    /**
     * HTTP/GET
     */
    public const GET = 'GET';
    /**
     * HTTP/POST
     */
    public const POST = 'POST';
    /**
     * HTTP/PUT
     */
    public const PUT = 'PUT';
    /**
     * HTTP/DELETE
     */
    public const DELETE = 'DELETE';
    /**
     * HTTP/PATCH
     */
    public const PATCH = 'PATCH';
    /**
     * HTTP/HEAD
     */
    public const HEAD = 'HEAD';
    /**
     * HTTP/OPTIONS
     */
    public const OPTIONS = 'OPTIONS';
}