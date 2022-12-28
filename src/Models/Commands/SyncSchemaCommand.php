<?php

namespace Magpie\Models\Commands;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Checks\TableSchemaSynchronizer;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\Kernel\Kernel;

/**
 * Synchronize database schema to database
 */
#[CommandSignature('db:sync-schema')]
#[CommandDescription('Synchronize database schema to database')]
class SyncSchemaCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $listener = new class implements ModelCheckListenable {
            /**
             * @inheritDoc
             */
            public function notifyCheckTable(string $className, string $tableName, bool $isTableExisting) : void
            {
                Console::info("Processing table: $tableName" . ($isTableExisting ? '' : '  **NEW**'));
            }


            /**
             * @inheritDoc
             */
            public function notifyCheckColumn(string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void
            {
                $action = $isColumnExisting ? 'Update' : 'New';
                Console::info("   $action column: $columnName");
            }
        };

        $paths = iter_flatten(Kernel::current()->getConfig()->getModelSourceSyncDirectories());

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            $model = new $class->name;
            if (!$model instanceof Model) continue;

            TableSchemaSynchronizer::apply($model, $listener);
        }
    }
}