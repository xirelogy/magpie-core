<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * Anything that can be closed
 */
interface Closeable
{
    /**
     * Close
     * @return void
     * @throws SafetyCommonException
     */
    public function close() : void;
}