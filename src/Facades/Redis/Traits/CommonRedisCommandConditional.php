<?php

namespace Magpie\Facades\Redis\Traits;

use Exception;
use Magpie\Exceptions\ConflictException;

/**
 * Common options for redis's commands to be conditional
 */
trait CommonRedisCommandConditional
{

    /**
     * @var bool When true, set value only if not yet exist
     */
    protected bool $ifNotYetExist = false;
    /**
     * @var bool When true, set value only if already exist
     */
    protected bool $ifAlreadyExist = false;


    /**
     * Set value only if not yet exist
     * @return $this
     * @throws Exception
     */
    public function ifNotYetExist() : static
    {
        $this->ifNotYetExist = true;
        $this->checkConflicts();
        return $this;
    }


    /**
     * Set value only if already exist
     * @return $this
     * @throws Exception
     */
    public function ifAlreadyExist() : static
    {
        $this->ifAlreadyExist = true;
        $this->checkConflicts();
        return $this;
    }


    /**
     * Check for conflicts
     * @return void
     * @throws Exception
     */
    protected function checkConflicts() : void
    {
        if ($this->ifAlreadyExist && $this->ifNotYetExist) throw new ConflictException();
    }
}