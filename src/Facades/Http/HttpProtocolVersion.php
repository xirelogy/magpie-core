<?php

namespace Magpie\Facades\Http;

use Magpie\General\Traits\StaticClass;

/**
 * HTTP protocol version
 */
class HttpProtocolVersion
{
    use StaticClass;

    /**
     * HTTP/1.0
     */
    public const VER_1_0 = '1.0';
    /**
     * HTTP/1.1
     */
    public const VER_1_1 = '1.1';
    /**
     * HTTP/2.0
     */
    public const VER_2_0 = '2.0';
}