<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Statement;

/**
 * May compile into statement
 */
interface StatementCompilable
{
    /**
     * Compile as statement
     * @param QueryGrammar $grammar
     * @return iterable<Statement>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function compile(QueryGrammar $grammar) : iterable;
}