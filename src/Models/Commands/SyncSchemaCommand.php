<?php

namespace Magpie\Models\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Models\ClosureModelCheckListener;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Checks\TableSchemaSynchronizer;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\Kernel\Kernel;

/**
 * Synchronize database schema to database
 */
#[CommandSignature('db:sync-schema')]
#[CommandDescriptionL('Synchronize database schema to database')]
class SyncSchemaCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $listener = new ClosureModelCheckListener(
            function (string $className, string $tableName, bool $isTableExisting) : void {
                $suffix = $isTableExisting ? '' : ('  ' . _l('**NEW**'));
                Console::info(_l('Processing table: ') . $tableName . $suffix);
            },
            function (string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void {
                $action = $isColumnExisting ? _l('Update column: ') : _l('New column: ');
                Console::info("   $action$columnName");
            },
        );

        $paths = iter_flatten(Kernel::current()->getConfig()->getModelSourceSyncDirectories());

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            if (!$class->isSubclassOf(Model::class)) continue;

            $model = new $class->name;
            TableSchemaSynchronizer::apply($model, $listener);
        }
    }
}