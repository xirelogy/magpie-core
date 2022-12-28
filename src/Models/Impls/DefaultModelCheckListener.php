<?php

namespace Magpie\Models\Impls;

use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * Default implementation of ModelCheckListenable that does nothing
 * @internal
 */
class DefaultModelCheckListener implements ModelCheckListenable
{
    /**
     * @inheritDoc
     */
    public function notifyCheckTable(string $className, string $tableName, bool $isTableExisting) : void
    {
        // nop
    }


    /**
     * @inheritDoc
     */
    public function notifyCheckColumn(string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void
    {
        // nop
    }
}