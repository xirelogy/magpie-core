<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\SingletonInstance;
use Magpie\Models\Providers\CommonQueryIdentifierQuote;

/**
 * MySQL's implementation to quote identifier in SQL query (using backticks)
 * @internal
 */
class BacktickQueryIdentifierQuote extends CommonQueryIdentifierQuote
{
    use SingletonInstance;

    /**
     * @inheritDoc
     */
    protected function onQuote(string $identifier) : string
    {
        return Quote::backTick($identifier);
    }
}
