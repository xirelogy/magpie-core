<?php

namespace Magpie\Models\Commands\Features;

use Exception;
use Magpie\General\Traits\StaticClass;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Models\ClosureModelCheckListener;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Checks\TableSchemaCommenter;
use Magpie\Models\Schemas\Checks\TableSchemaSynchronizer;
use Magpie\Models\Schemas\ModelDefinition;
use Magpie\System\HardCore\AutoloadReflection;
use Magpie\System\Kernel\Kernel;

/**
 * Features to support database command
 */
class DatabaseCommandFeature
{
    use StaticClass;

    /**
     * Create standard listener for refresh comments
     * @param Loggable $logger
     * @return ModelCheckListenable
     */
    public static function createRefreshCommentsListener(Loggable $logger) : ModelCheckListenable
    {
        return new ClosureModelCheckListener(
            function (string $className, string $tableName, bool $isTableExisting) use ($logger) : void {
                _used($className, $isTableExisting);
                $logger->info(_l('Processing table: ') . $tableName);
            },
            function (string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) : void {
                // nop
            },
        );
    }


    /**
     * Refresh model source files comments
     * @param ModelCheckListenable $listener
     * @param array<string>|null $paths
     * @return void
     * @throws Exception
     */
    public static function refreshComments(ModelCheckListenable $listener, ?array $paths = null) : void
    {
        $paths = $paths ?? iter_flatten(Kernel::current()->getConfig()->getModelSourceDirectories(), false);

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            if (!$class->isSubclassOf(Model::class)) continue;

            $model = new $class->name;
            TableSchemaCommenter::apply($model, $listener);
        }
    }


    /**
     * Create standard listener for sync schema
     * @param Loggable $logger
     * @return ModelCheckListenable
     */
    public static function createSyncSchemaListener(Loggable $logger) : ModelCheckListenable
    {
        return new ClosureModelCheckListener(
            function (string $className, string $tableName, bool $isTableExisting) use ($logger) : void {
                $suffix = $isTableExisting ? '' : ('  ' . _l('**NEW**'));
                $logger->info(_l('Processing table: ') . $tableName . $suffix);
            },
            function (string $className, string $tableName, string $columnName, string|ModelDefinition|null $columnDef, bool $isColumnExisting) use ($logger) : void {
                $action = $isColumnExisting ? _l('Update column: ') : _l('New column: ');
                $logger->info("   $action$columnName");
            },
        );
    }


    /**
     * Synchronize database schema to database
     * @param ModelCheckListenable $listener
     * @param array<string>|null $paths
     * @return void
     * @throws Exception
     */
    public static function syncSchema(ModelCheckListenable $listener, ?array $paths = null) : void
    {
        $paths = $paths ?? iter_flatten(Kernel::current()->getConfig()->getModelSourceSyncDirectories(), false);

        foreach (AutoloadReflection::instance()->expandDiscoverySourcesReflection($paths) as $class) {
            if (!$class->isSubclassOf(Model::class)) continue;

            $model = new $class->name;
            TableSchemaSynchronizer::apply($model, $listener);
        }
    }
}