<?php

namespace Magpie\General\Concepts;

use Exception;

/**
 * Anything that can be closed
 */
interface Closeable
{
    /**
     * Close
     * @return void
     * @throws Exception
     */
    public function close() : void;
}