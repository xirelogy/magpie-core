<?php

namespace Magpie\Models\Commands;

use Magpie\Commands\Attributes\CommandDescription;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Checks\TableSchemaCommenter;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\Kernel\Kernel;

/**
 * Apply comments to model source files
 */
#[CommandSignature('db:refresh-comments')]
#[CommandDescription('Apply comments to model source files')]
class RefreshCommentsCommand extends Command
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
                Console::info("Processing table: $tableName");
            }


            /**
             * @inheritDoc
             */
            public function notifyCheckColumn(string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void
            {
                // nop
            }
        };

        $paths = iter_flatten(Kernel::current()->getConfig()->getModelSourceDirectories());

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            $model = new $class->name;
            if (!$model instanceof Model) continue;

            TableSchemaCommenter::apply($model, $listener);
        }
    }
}