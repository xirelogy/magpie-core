<?php

namespace Magpie\HttpServer\Concepts;

use Magpie\General\Concepts\PrimitiveBinaryContentable;

/**
 * May extract variable from post body content
 */
interface PostBodyContentable
{
    /**
     * Extract post variables from post content
     * @return iterable<string, array<string|PrimitiveBinaryContentable>|string|PrimitiveBinaryContentable>
     */
    public function getVariables() : iterable;
}