<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;

/**
 * Database query for model
 * @template T
 */
abstract class ModelQuery extends Query
{
    /**
     * Update, assigning specific key values
     * @param array<string, mixed> $assignments
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function update(array $assignments) : void
    {
        $statement = $this->prepareUpdateStatement($assignments);
        $statement->execute();
    }


    /**
     * Delete from database (for those matching the deletion condition)
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function delete() : void
    {
        $statement = $this->prepareDeleteStatement();
        $statement->execute();
    }


    /**
     * Prepare the 'UPDATE' query statement
     * @param array<string, mixed> $assignments
     * @return Statement
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected abstract function prepareUpdateStatement(array $assignments) : Statement;


    /**
     * Prepare the 'DELETE' query statement
     * @return Statement
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected abstract function prepareDeleteStatement() : Statement;
}