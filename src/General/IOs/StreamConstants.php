<?php

namespace Magpie\General\IOs;

use Magpie\General\Traits\StaticClass;

/**
 * Stream related constants
 */
class StreamConstants
{
    use StaticClass;


    /**
     * A safe default chunk size during stream read
     */
    public const DEFAULT_CHUNK_SIZE = 10240;
}