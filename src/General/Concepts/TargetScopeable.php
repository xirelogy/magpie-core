<?php

namespace Magpie\General\Concepts;

use Magpie\General\Contexts\Scoped;

/**
 * A readable/writable target with scope applicable
 */
interface TargetScopeable
{
    /**
     * All scopes
     * @return iterable<Scoped>
     */
    public function getScopes() : iterable;
}