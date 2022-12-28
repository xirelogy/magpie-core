<?php

namespace Magpie\Objects\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\BaseQueryConditionable;
use Magpie\Models\Exceptions\ModelSafetyException;

/**
 * Anything that can be applied on query
 */
interface QueryApplicable
{
    /**
     * Apply condition/specification on given query
     * @param BaseQueryConditionable $query
     * @return void
     * @throws SafetyCommonException
     * @throws ModelSafetyException
     */
    public function applyOnQuery(BaseQueryConditionable $query) : void;
}