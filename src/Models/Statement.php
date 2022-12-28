<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\QueryRawExportable;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;

/**
 * A database statement
 */
abstract class Statement implements QueryRawExportable
{
    /**
     * Bind values to the statement
     * @param array $values
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function bind(array $values) : void;


    /**
     * Execute the statement
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function execute() : void;


    /**
     * Perform query
     * @return iterable<array<string, mixed>>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function query() : iterable;
}