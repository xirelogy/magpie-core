<?php

namespace Magpie\Models\Concepts;

/**
 * May quote identifier in SQL query
 */
interface QueryIdentifierQuotable
{
    /**
     * Apply quoting to identifier
     * @param string $identifier
     * @return string
     */
    public function quote(string $identifier) : string;
}