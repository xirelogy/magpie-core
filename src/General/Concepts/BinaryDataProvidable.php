<?php

namespace Magpie\General\Concepts;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * Interface to anything that can provide binary data
 */
interface BinaryDataProvidable
{
    /**
     * Binary data
     * @return string
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function getData() : string;
}