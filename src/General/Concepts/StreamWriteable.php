<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\StreamException;

/**
 * Writable stream
 */
interface StreamWriteable extends Closeable
{
    /**
     * Write data into the stream
     * @param string $data Data to be written
     * @return int Number of bytes accepted
     * @throws StreamException
     */
    public function write(string $data) : int;
}