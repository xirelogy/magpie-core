<?php

namespace Magpie\Models\Concepts;

/**
 * May patch model initializer
 */
interface ModelInitializePatchable
{
    /**
     * Try to initialize a column
     * @param string $tableModelClass Table's model class name
     * @param string $columnName Column name
     * @param mixed|null $result Outgoing result
     * @return bool If initialization happens
     */
    public function tryInitializeColumn(string $tableModelClass, string $columnName, mixed &$result = null) : bool;
}