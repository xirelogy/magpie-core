<?php

namespace Magpie\Facades\Http;

use Magpie\General\Concepts\TypeClassable;

/**
 * HTTP client request option
 */
abstract class HttpClientRequestOption implements TypeClassable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }
}