<?php

namespace Magpie\General\Concepts;

/**
 * Interface to anything that can provide binary content
 */
interface BinaryContentable extends PrimitiveBinaryContentable
{
    /**
     * Binary content data size
     * @return int
     */
    public function getDataSize() : int;
}