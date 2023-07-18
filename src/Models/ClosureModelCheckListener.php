<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * Implementation of ModelCheckListenable by forwarding to closures
 */
class ClosureModelCheckListener implements ModelCheckListenable
{
    /**
     * @var Closure Forwarded closure for notifyCheckTable()
     */
    protected readonly Closure $notifyCheckTableFn;
    /**
     * @var Closure Forwarded closure for notifyCheckColumn()
     */
    protected readonly Closure $notifyCheckColumnFn;


    /**
     * Constructor
     * @param callable(string,string,bool):void $notifyCheckTableFn
     * @param callable(string,string,string,string|ModelDefinition|null,bool):void $notifyCheckColumnFn
     */
    public function __construct(callable $notifyCheckTableFn, callable $notifyCheckColumnFn)
    {
        $this->notifyCheckTableFn = $notifyCheckTableFn;
        $this->notifyCheckColumnFn = $notifyCheckColumnFn;
    }


    /**
     * @inheritDoc
     */
    public function notifyCheckTable(string $className, string $tableName, bool $isTableExisting) : void
    {
        ($this->notifyCheckTableFn)($className, $tableName, $isTableExisting);
    }


    /**
     * @inheritDoc
     */
    public function notifyCheckColumn(string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void
    {
        ($this->notifyCheckColumnFn)($className, $tableName, $columnName, $columnDef, $isColumnExisting);
    }
}