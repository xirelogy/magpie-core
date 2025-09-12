<?php

namespace Magpie\Models\Providers;

use Magpie\Models\Concepts\QueryIdentifierQuotable;

/**
 * Common implementation to quote identifier in SQL query
 */
abstract class CommonQueryIdentifierQuote implements QueryIdentifierQuotable
{
    /**
     * @inheritDoc
     */
    public final function quote(string $identifier) : string
    {
        if ($identifier === '*') return $identifier;

        return $this->onQuote($identifier);
    }


    /**
     * Apply quoting to identifier
     * @param string $identifier
     * @return string
     */
    protected abstract function onQuote(string $identifier) : string;
}