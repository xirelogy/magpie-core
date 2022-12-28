<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\RawStatement;

/**
 * Listenable to log query statements
 */
interface StatementLogListenable
{
    /**
     * Log for statement
     * @param RawStatement $statement
     * @return void
     */
    public function logStatement(RawStatement $statement) : void;
}