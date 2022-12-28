<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseEditAction;

/**
 * Concept of editable specifier for column database
 */
interface ColumnDatabaseEditSpecifiable extends ColumnDatabaseSpecifiable
{
    /**
     * Specify the edit action
     * @param ColumnDatabaseEditAction $action
     * @return $this
     */
    public function withEditAction(ColumnDatabaseEditAction $action) : static;


    /**
     * Specify the relative column position
     * @param string|null $columnName
     * @return $this
     */
    public function withColumnPositionAfter(?string $columnName) : static;
}