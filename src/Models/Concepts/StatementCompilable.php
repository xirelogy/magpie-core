<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Statement;

/**
 * May compile into statement
 */
interface StatementCompilable
{
    /**
     * Compile as statement
     * @return iterable<Statement>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function compile() : iterable;
}