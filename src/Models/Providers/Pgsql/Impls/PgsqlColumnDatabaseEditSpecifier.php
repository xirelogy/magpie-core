<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseEditAction;

/**
 * PostgreSQL editable specifier for column database
 * @internal
 */
class PgsqlColumnDatabaseEditSpecifier extends PgsqlColumnDatabaseSpecifier implements ColumnDatabaseEditSpecifiable
{
    /**
     * @var ColumnDatabaseEditAction|null The editing action
     */
    protected ?ColumnDatabaseEditAction $editAction = null;


    /**
     * Current edit action
     * @return ColumnDatabaseEditAction|null
     */
    public function getEditAction() : ?ColumnDatabaseEditAction
    {
        return $this->editAction;
    }


    /**
     * @inheritDoc
     */
    public function withEditAction(ColumnDatabaseEditAction $action) : static
    {
        $this->editAction = $action;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withColumnPositionAfter(?string $columnName) : static
    {
        return $this;
    }
}