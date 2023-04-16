<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * A readable target
 */
interface TargetReadable
{
    /**
     * Create a corresponding readable stream
     * @return StreamReadable
     * @throws SafetyCommonException
     */
    public function createStream() : StreamReadable;
}