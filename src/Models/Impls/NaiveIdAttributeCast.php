<?php

namespace Magpie\Models\Impls;

use Magpie\Models\Casts\IdAttributeCast;
use Magpie\Models\Identifier;

/**
 * A naive implementation of identifier cast
 * @internal
 */
class NaiveIdAttributeCast extends IdAttributeCast
{
    /**
     * @inheritDoc
     */
    protected static function createIdentifier(int|string $rawValue) : Identifier
    {
        return Identifier::fromRaw($rawValue);
    }


    /**
     * @inheritDoc
     */
    protected static function acceptIdentifier(int|string $displayValue) : Identifier
    {
        return Identifier::fromDisplay($displayValue);
    }
}