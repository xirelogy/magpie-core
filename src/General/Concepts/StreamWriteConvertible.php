<?php

namespace Magpie\General\Concepts;

/**
 * May provide writable stream
 */
interface StreamWriteConvertible
{
    /**
     * Get corresponding stream for writing
     * @return StreamWriteable
     */
    public function getWriteStream() : StreamWriteable;
}