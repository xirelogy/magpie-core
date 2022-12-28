<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\StreamException;

/**
 * Writable stream that can be finalized into a readable stream
 */
interface StreamWriteFinalizable extends StreamWriteable
{
    /**
     * Finalize current writable stream and handover to a readable stream
     * @return StreamReadable
     * @throws StreamException
     */
    public function finalize() : StreamReadable;
}