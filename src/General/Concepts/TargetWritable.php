<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * A writable target
 */
interface TargetWritable
{
    /**
     * Create a corresponding writable stream
     * @return StreamWriteable
     * @throws SafetyCommonException
     */
    public function createStream() : StreamWriteable;
}