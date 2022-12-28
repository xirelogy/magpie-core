<?php

namespace Magpie\Objects\Traits;

use Carbon\CarbonInterface;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Enable common object timestamps stored in relevant model
 * @requires \Magpie\Objects\ModeledObject
 */
trait CommonModeledObjectTimestamping
{
    /**
     * When created
     * @return CarbonInterface
     * @throws SafetyCommonException
     */
    public function getCreatedAt() : CarbonInterface
    {
        return $this->getBaseModel()->{static::$_traitNameOf_CreatedAt} ?? throw new NullException();
    }


    /**
     * When last updated
     * @return CarbonInterface
     * @throws SafetyCommonException
     */
    public function getUpdatedAt() : CarbonInterface
    {
        return $this->getBaseModel()->{static::$_traitNameOf_UpdatedAt} ?? throw new NullException();
    }
}