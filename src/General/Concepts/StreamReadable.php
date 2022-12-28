<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\StreamException;

/**
 * Readable stream
 */
interface StreamReadable
{
    /**
     * If data available for read
     * @return bool
     */
    public function hasData() : bool;


    /**
     * Read data from stream
     * @param int|null $max If specified, maximum number of bytes to be read
     * @return string Data read
     * @throws StreamException
     */
    public function read(?int $max = null) : string;
}