<?php

namespace Magpie\Models\Concepts;

/**
 * May specify filtering
 */
interface QueryFilterable
{
    /**
     * Specify the filter
     * @param QueryFilterApplicable $filter
     * @return $this
     */
    public function filterWith(QueryFilterApplicable $filter) : static;
}