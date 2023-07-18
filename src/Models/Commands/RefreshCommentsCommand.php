<?php

namespace Magpie\Models\Commands;

use Magpie\Commands\Attributes\CommandDescriptionL;
use Magpie\Commands\Attributes\CommandSignature;
use Magpie\Commands\Command;
use Magpie\Commands\Request;
use Magpie\Facades\Console;
use Magpie\Models\ClosureModelCheckListener;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Checks\TableSchemaCommenter;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\Kernel\Kernel;

/**
 * Refresh model source files comments
 */
#[CommandSignature('db:refresh-comments')]
#[CommandDescriptionL('Refresh model source files comments')]
class RefreshCommentsCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function onRun(Request $request) : void
    {
        $listener = new ClosureModelCheckListener(
            function (string $className, string $tableName, bool $isTableExisting) : void {
                _used($className, $isTableExisting);
                Console::info(_l('Processing table: ') . $tableName);
            },
            function (string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void {
                // nop
            },
        );

        $paths = iter_flatten(Kernel::current()->getConfig()->getModelSourceDirectories());

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            if (!$class->isSubclassOf(Model::class)) continue;

            $model = new $class->name;
            TableSchemaCommenter::apply($model, $listener);
        }
    }
}