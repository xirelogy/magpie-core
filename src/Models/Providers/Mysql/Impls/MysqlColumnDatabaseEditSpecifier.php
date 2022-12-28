<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Models\Concepts\ColumnDatabaseEditSpecifiable;
use Magpie\Models\Impls\SqlFormat;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\DatabaseEdits\AddColumnDatabaseEditAction;
use Magpie\Models\Schemas\DatabaseEdits\ChangeColumnDatabaseEditAction;
use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseEditAction;

/**
 * MySQL editable specifier for column database
 * @internal
 */
class MysqlColumnDatabaseEditSpecifier extends MysqlColumnDatabaseSpecifier implements ColumnDatabaseEditSpecifiable
{
    /**
     * @var ColumnDatabaseEditAction|null The editing action
     */
    protected ?ColumnDatabaseEditAction $editAction = null;
    /**
     * @var bool If there is relative column position
     */
    protected bool $hasRelativePosition = false;
    /**
     * @var string|null Relative column position
     */
    protected ?string $columnPositionAfter = null;


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
        $this->hasRelativePosition = true;
        $this->columnPositionAfter = $columnName;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function _compile(MysqlConnection $connection) : string
    {
        $ret = $this->compileEditAction();
        $ret .= ' ' . parent::_compile($connection);

        if ($this->hasRelativePosition) {
            if ($this->columnPositionAfter !== null) {
                $ret .= ' AFTER ' . SqlFormat::backTick($this->columnPositionAfter);
            } else {
                $ret .= ' FIRST';
            }
        }

        return $ret;
    }


    /**
     * Compile the edit action of column specifier
     * @return string
     * @throws SafetyCommonException
     */
    protected function compileEditAction() : string
    {
        if ($this->editAction === null) throw new MissingArgumentException('editAction');

        if ($this->editAction instanceof AddColumnDatabaseEditAction) {
            return 'ADD COLUMN';
        }

        if ($this->editAction instanceof ChangeColumnDatabaseEditAction) {
            return 'CHANGE COLUMN ' . SqlFormat::backTick($this->editAction->columnName);
        }

        throw new UnsupportedValueException($this->editAction, 'edit action');
    }
}