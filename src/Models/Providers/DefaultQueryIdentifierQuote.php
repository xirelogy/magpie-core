<?php

namespace Magpie\Models\Providers;

use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\SingletonInstance;

/**
 * Default implementation to quote identifier in SQL query
 */
class DefaultQueryIdentifierQuote extends CommonQueryIdentifierQuote
{
    use SingletonInstance;

    /**
     * @inheritDoc
     */
    protected function onQuote(string $identifier) : string
    {
        return Quote::double($identifier);
    }
}