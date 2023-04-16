<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * May provide readable stream
 */
interface StreamReadConvertible
{
    /**
     * Get corresponding stream for reading
     * @return StreamReadable
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function getReadStream() : StreamReadable;
}