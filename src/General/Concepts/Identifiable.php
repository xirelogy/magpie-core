<?php

namespace Magpie\General\Concepts;

use Magpie\Models\Identifier;

/**
 * Anything identifiable
 */
interface Identifiable
{
    /**
     * Unique identifier of current item
     * @return Identifier|string|int
     */
    public function getId() : Identifier|string|int;
}