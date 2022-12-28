<?php

namespace Magpie\General\Concepts;

use Exception;

/**
 * May provide readable stream
 */
interface StreamReadConvertible
{
    /**
     * Get corresponding stream for reading
     * @return StreamReadable
     * @throws Exception
     */
    public function getReadStream() : StreamReadable;
}