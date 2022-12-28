<?php

namespace Magpie\Facades\Redis\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * Redis command with conditional support
 */
interface RedisConditionalCommandable
{
    /**
     * Set value only if not yet exist
     * @return $this
     * @throws SafetyCommonException
     */
    public function ifNotYetExist() : static;


    /**
     * Set value only if already exist
     * @return $this
     * @throws SafetyCommonException
     */
    public function ifAlreadyExist() : static;
}