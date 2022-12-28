<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\QueryFilterService;

/**
 * May apply filter on query
 */
interface QueryFilterApplicable
{
    /**
     * Apply filter using given filtering service
     * @param QueryFilterService $service
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function apply(QueryFilterService $service) : void;
}