<?php

namespace Magpie\Models\Schemas;

use Magpie\Exceptions\AttributeNotFoundException;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Models\Annotations\Column as ColumnAttribute;
use Magpie\Models\Annotations\Table as TableAttribute;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Model;
use Magpie\Models\Schemas\Configs\SchemaPreference;
use Magpie\Models\Statement;
use Magpie\System\Kernel\Kernel;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Table schema
 */
class TableSchema implements Packable
{
    use CommonPackable;

    /**
     * @var array<string, static> Cache of schema instances
     */
    private static array $cachedSchemas = [];

    /**
     * @var class-string<Model> Associated model class name
     */
    protected readonly string $modelClass;
    /**
     * @var SchemaPreference Associated schema preference
     */
    protected readonly SchemaPreference $preference;
    /**
     * @var ReflectionClass Associated class's reflection
     */
    protected readonly ReflectionClass $class;
    /**
     * @var ReflectionAttribute Associated reflection attribute associated with this schema
     */
    protected readonly ReflectionAttribute $attribute;
    /**
     * @var TableAttribute Attribute instance
     */
    protected readonly TableAttribute $attributeInstance;
    /**
     * @var array<string, ColumnSchema>|null Cache columns
     */
    protected ?array $cacheColumns = null;
    /**
     * @var array<ColumnSchema> Cached columns with auto-increment
     */
    protected array $cacheAutoIncrementColumns = [];
    /**
     * @var array<ColumnSchema> Cached columns for creation timestamp
     */
    protected array $cacheCreateTimestampColumns = [];
    /**
     * @var array<ColumnSchema> Cached columns for update timestamp
     */
    protected array $cacheUpdateTimestampColumns = [];


    /**
     * Constructor
     * @param Model|string $modelSpec
     * @throws SafetyCommonException
     * @throws ReflectionException
     */
    protected function __construct(Model|string $modelSpec)
    {
        if ($modelSpec instanceof Model) {
            $this->modelClass = $modelSpec::class;
        } else {
            if (!is_subclass_of($modelSpec, Model::class)) throw new ClassNotOfTypeException($modelSpec, Model::class);
            $this->modelClass = $modelSpec;
        }

        $this->preference = static::getSchemaPreference($modelSpec::getConnectionName());
        $this->class = new ReflectionClass($modelSpec);
        $this->attribute = static::findTableAttribute($this->class);
        $this->attributeInstance = $this->attribute->newInstance();
    }


    /**
     * Table name
     * @return string
     */
    public function getName() : string
    {
        return $this->attributeInstance->name;
    }


    /**
     * Class name of associated model
     * @return string
     */
    public function getModelClassName() : string
    {
        return $this->modelClass;
    }


    /**
     * All columns
     * @return iterable<ColumnSchema>
     * @throws SafetyCommonException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function getColumns() : iterable
    {
        foreach ($this->class->getAttributes(ColumnAttribute::class) as $attribute) {
            yield new class($this, $this->preference, $attribute) extends ColumnSchema {
                /**
                 * Constructor
                 * @param TableSchema $parentTable
                 * @param SchemaPreference $preference
                 * @param ReflectionAttribute $attribute
                 * @throws SafetyCommonException
                 */
                public function __construct(TableSchema $parentTable, SchemaPreference $preference, ReflectionAttribute $attribute)
                {
                    parent::__construct($parentTable, $preference, $attribute);
                }
            };
        }
    }


    /**
     * Get particular column
     * @param string $name
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getColumn(string $name) : ?ColumnSchema
    {
        $this->ensureColumnCache();

        return $this->cacheColumns[$name] ?? null;
    }


    /**
     * The column with auto-increment
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getAutoIncrementColumn() : ?ColumnSchema
    {
        $this->ensureColumnCache();

        return iter_first($this->cacheAutoIncrementColumns);
    }


    /**
     * The column with creation timestamp
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getCreateTimestampColumn() : ?ColumnSchema
    {
        $this->ensureColumnCache();

        return iter_first($this->cacheCreateTimestampColumns);
    }


    /**
     * The column with update timestamp
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getUpdateTimestampColumn() : ?ColumnSchema
    {
        $this->ensureColumnCache();

        return iter_first($this->cacheUpdateTimestampColumns);
    }


    /**
     * Get table schema at the database
     * @param Connection $connection
     * @return TableSchemaAtDatabase|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function getSchemaAtDatabase(Connection $connection) : ?TableSchemaAtDatabase
    {
        return $connection->getTableSchemaAtDatabase($this->getName());
    }


    /**
     * Compile statements to synchronize
     * @param Connection $connection
     * @param bool $isUseTransaction
     * @param ModelCheckListenable|null $listener
     * @return iterable<Statement>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function compileStatementsAtDatabase(Connection $connection, bool &$isUseTransaction, ?ModelCheckListenable $listener = null) : iterable
    {
        $grammar = $connection->getQueryGrammar();
        $schemaAtDb = $this->getSchemaAtDatabase($connection);

        $modelClassName = $this->getModelClassName();
        $tableName = $this->getName();

        $listener?->notifyCheckTable($modelClassName, $tableName, $schemaAtDb !== null);
        $isUseTransaction = false;

        if ($schemaAtDb === null) {
            // Create new table
            $creator = $connection->prepareTableCreator($tableName, $this->getColumns());
            foreach ($this->getColumns() as $column) {
                $listener?->notifyCheckColumn($modelClassName, $tableName, $column->getName(), $column->getDefinitionType(), false);
                $creator->addColumnFromSchema($column);
            }
            $isUseTransaction = $creator->isUseTransaction();
            return $creator->compile($grammar);
        } else {
            // Alter table
            $editor = $connection->prepareTableEditor($tableName, $this->getColumns());
            $lastColumn = null;
            foreach ($this->getColumns() as $column) {
                $columnEditor = $editor->addCheckedColumnFromSchema($column, $schemaAtDb, $lastColumn, $columnAtDb);
                if ($columnEditor !== null) {
                    $listener?->notifyCheckColumn($modelClassName, $tableName, $column->getName(), $column->getDefinitionType(), $columnAtDb !== null);
                }
                $lastColumn = $column;
            }
            if ($editor->hasColumn()) {
                $isUseTransaction = $editor->isUseTransaction();
                return $editor->compile($grammar);
            }
        }

        return [];
    }


    /**
     * Export a block of property comments corresponding to each table column
     * @param string|null $columnMethodPrefix
     * @return array<string>
     * @throws SafetyCommonException
     */
    public function exportPropertyComments(?string $columnMethodPrefix = null) : array
    {
        $ret = [];
        $ret[] = '/**';

        foreach ($this->getColumns() as $column) {
            $columnName = $column->getName();
            $nativeType = $column->getNativeType();
            if (!$column->isNonNull()) $nativeType .= '|null';
            $currentProperty = ' * @property ' . $nativeType . ' $' . $columnName;

            $comments = $column->getComments();
            if ($comments !== null) $currentProperty .= ' ' . $comments;

            $ret[] = $currentProperty;
        }

        if ($columnMethodPrefix !== null) {
            $ret[] = ' *';
            foreach ($this->getColumns() as $column) {
                $columnName = $column->getName();
                $ret[] = ' * @method static ColumnName ' . $columnMethodPrefix . $columnName . '()';
            }
        }

        $ret[] = ' */';

        return $ret;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->name = $this->getName();
        $ret->columns = $this->getColumns();
    }


    /**
     * Ensure column cache is available
     * @return void
     * @throws SafetyCommonException
     */
    protected function ensureColumnCache() : void
    {
        if ($this->cacheColumns !== null) return;

        $this->cacheColumns = [];
        foreach ($this->getColumns() as $column) {
            $this->cacheColumns[$column->getName()] = $column;

            if ($column->isAutoIncrement()) $this->cacheAutoIncrementColumns[] = $column;
            if ($column->isCreateTimestamp()) $this->cacheCreateTimestampColumns[] = $column;
            if ($column->isUpdateTimestamp()) $this->cacheUpdateTimestampColumns[] = $column;
        }
    }


    /**
     * Initialize from model
     * @param Model|class-string<Model> $modelSpec
     * @return static
     * @throws SafetyCommonException
     */
    public static function from(Model|string $modelSpec) : static
    {
        try {
            return static::fromNative($modelSpec);
        } catch (ReflectionException $ex) {
            throw new UnexpectedException(previous: $ex);
        }
    }


    /**
     * Initialize from model (natively)
     * @param Model|class-string<Model> $modelSpec
     * @return static
     * @throws SafetyCommonException
     * @throws ReflectionException
     */
    public static function fromNative(Model|string $modelSpec) : static
    {
        $key = $modelSpec instanceof Model ? $modelSpec::class : $modelSpec;

        if (!array_key_exists($key, static::$cachedSchemas)) {
            static::$cachedSchemas[$key] = new static($modelSpec);
        }

        return static::$cachedSchemas[$key];
    }


    /**
     * Find table attribute
     * @param ReflectionClass $class
     * @return ReflectionAttribute
     * @throws SafetyCommonException
     */
    protected static function findTableAttribute(ReflectionClass $class) : ReflectionAttribute
    {
        foreach ($class->getAttributes(TableAttribute::class) as $attribute) {
            return $attribute;
        }

        throw new AttributeNotFoundException(TableAttribute::class);
    }


    /**
     * Get schema preference for given connection
     * @param ConnectionResolvable|string $connection
     * @return SchemaPreference
     */
    protected static function getSchemaPreference(ConnectionResolvable|string $connection) : SchemaPreference
    {
        if ($connection instanceof ConnectionResolvable) return $connection->getSchemaPreference();
        return Kernel::current()->getConfig()->getModelSchemaPreference($connection) ?? SchemaPreference::default();
    }
}