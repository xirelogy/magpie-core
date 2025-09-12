<?php

namespace Magpie\Models;

use Carbon\Carbon;
use Error;
use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Concepts\Deletable;
use Magpie\General\Concepts\Savable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Sugars\Quote;
use Magpie\General\Traits\CommonPackable;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\Modelable;
use Magpie\Models\Concepts\ModelStorageProvidable;
use Magpie\Models\Exceptions\ModelCannotBeIdentifiedException;
use Magpie\Models\Exceptions\ModelNotFoundException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Impls\ClosureDeferringModelStorageProvider;
use Magpie\Models\Impls\ClosureQuerySetupListener;
use Magpie\Models\Impls\DeferringModelStorageProvider;
use Magpie\Models\Impls\ModelBinder;
use Magpie\Models\Impls\ActualModelQuery;
use Magpie\Models\Impls\PatchHost;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QuerySetupListener;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\TableSchema;
use Stringable;

/**
 * A database model
 */
abstract class Model implements Modelable, Savable, Deletable, Stringable
{
    use CommonPackable;

    /**
     * @var ModelStorageProvidable Model storage
     */
    private ModelStorageProvidable $_storage;


    /**
     * Constructor
     * @param ModelStorageProvidable|null $storage
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function __construct(?ModelStorageProvidable $storage = null)
    {
        $this->_storage = static::_initializeFromStorage($this, $storage);
    }


    /**
     * Check if attribute for model set
     * @param string $key
     * @return bool
     */
    public final function __isset(string $key) : bool
    {
        return $this->_storage->hasAttribute($key);
    }


    /**
     * Get attributes for model
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public final function __get(string $key) : mixed
    {
        return $this->_storage->getAttribute($key);
    }


    /**
     * Set attributes for model
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public final function __set(string $key, mixed $value) : void
    {
        $this->_storage->setAttribute($key, $value);
    }


    /**
     * Magic method to serialize()
     * @return array
     * @throws SafetyCommonException
     */
    public function __serialize() : array
    {
        $identifyingAttributes = iter_flatten($this->_storage->getIdentifyingAttributes());
        if (count($identifyingAttributes) <= 0) throw new UnsupportedException();

        return [
            'storageAttributes' => $identifyingAttributes,
        ];
    }


    /**
     * Magic method to unserialize()
     * @param array $data
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public function __unserialize(array $data) : void
    {
        $attributes = $data['storageAttributes'] ?? throw new InvalidDataException();
        if (!is_array($attributes)) throw new InvalidDataException();

        $query = static::query();
        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        $db = $query->first() ?? throw new NullException();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->_storage = $db->_storage;
    }


    /**
     * Static method invoking
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Exception
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public final static function __callStatic(string $name, array $arguments) : mixed
    {
        // Try to use column prefix
        $columnPrefix = static::getColumnMethodPrefix();
        if ($columnPrefix !== null && str_starts_with($name, $columnPrefix)) {
            $columnName = substr($name, strlen($columnPrefix));
            $retSchema = ModelBinder::defineColumnName(static::class, $columnName);
            if ($retSchema !== null) return $retSchema;
        }

        // Method not found
        $message = 'Call to undefined method ' . static::class . '::' . $name . '()';
        throw new Error($message);
    }


    /**
     * Connect to the database connection where the model depends on
     * @return Connection
     * @throws SafetyCommonException
     */
    public final function connect() : Connection
    {
        return Connection::from(static::getConnectionName());
    }


    /**
     * Table name for current model
     * @return string
     */
    public final function getTableName() : string
    {
        return Excepts::noThrow(fn () => $this->_storage->getTableSchema()->getName(), '');
    }


    /**
     * Column schema for specific column
     * @param string $columnName
     * @return ColumnSchema|null
     */
    public final function getColumnSchema(string $columnName) : ?ColumnSchema
    {
        return Excepts::noThrow(fn () => $this->_storage->getTableSchema()->getColumn($columnName));
    }


    /**
     * All key-values
     * @return iterable<string, mixed>
     */
    public final function getValues() : iterable
    {
        foreach ($this->_storage->getAttributes() as $key => $value) {
            yield $key => $value;
        }
    }


    /**
     * @inheritDoc
     */
    public final function save() : void
    {
        $changes = iter_flatten($this->_storage->getChangedAttributes());

        $savedAttributes = [];

        if ($this->_storage->isNew()) {
            // New model, invoke corresponding 'INSERT'
            $connectionInstance = Connection::from(static::getConnectionName());
            static::_onCreate($connectionInstance, $this->_storage->getTableSchema(), $changes);

            $this->_storage->resetChanges($changes);
            PatchHost::notifySave($this->_storage->getTableSchema()->getModelClassName());
        } else {
            // Existing model, invoke corresponding 'UPDATE'
            if (count($changes) <= 0) return;

            $listener = ClosureQuerySetupListener::create(function (array $attributes) use (&$savedAttributes) {
                $savedAttributes = $attributes;
            });

            $query = static::_createQuery($this, $listener);

            $hasIdentifying = false;
            foreach ($this->_storage->getIdentifyingAttributes() as $identKey => $identValue) {
                $query->where($identKey, $identValue);
                $hasIdentifying = true;
            }

            if (!$hasIdentifying) throw new ModelCannotBeIdentifiedException();

            $query->update($changes);

            $this->_storage->resetChanges($savedAttributes);
            PatchHost::notifySave($this->_storage->getTableSchema()->getModelClassName());
        }
    }


    /**
     * Save current model and return
     * @return $this
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public final function saveAndReturn() : static
    {
        $this->save();
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function delete() : void
    {
        // New storage cannot be deleted
        if ($this->_storage->isNew()) throw new ModelNotFoundException();

        // Try to identify the model
        $query = static::_createQuery($this);

        $hasIdentifying = false;
        foreach ($this->_storage->getIdentifyingAttributes() as $identKey => $identValue) {
            $query->where($identKey, $identValue);
            $hasIdentifying = true;
        }

        if (!$hasIdentifying) throw new ModelCannotBeIdentifiedException();

        // Delete and destroy
        $query->delete();
        $this->_storage->destroy();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        foreach ($this->_storage->getAttributes() as $key => $value) {
            $ret->{$key} = $value;
        }
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return Excepts::noThrow(fn () => SimpleJSON::encode($this->pack()), '');
    }


    /**
     * Create query for current model
     * @return ModelQuery<static>
     * @throws SafetyCommonException
     */
    public final static function query() : ModelQuery
    {
        $model = new static();
        return static::_createQuery($model);
    }


    /**
     * Create query for current model (with optional binding)
     * @param static $model
     * @param QuerySetupListener|null $listener
     * @return ModelQuery<static>
     * @throws SafetyCommonException
     * @internal
     */
    private static function _createQuery(self $model, ?QuerySetupListener $listener = null) : ModelQuery
    {
        // Link to table schema
        $tableSchema = TableSchema::from($model);

        // Declare the hydration function
        $hydrationFn = function(array $values, array $extraCasts) use($tableSchema) : static {
            $storage = ModelBinder::hydrateStorage($tableSchema, $values, $extraCasts);
            return new static($storage);
        };

        return new ActualModelQuery($model->getConnectionName(), $tableSchema, $hydrationFn, $listener);
    }


    /**
     * Create a new model
     * @param array<string, mixed> $assignments
     * @return static
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public final static function create(array $assignments) : static
    {
        $tableSchema = TableSchema::from(static::class);

        $deferStorage = new ClosureDeferringModelStorageProvider($tableSchema, function(Model $instance, string $connection) use($tableSchema, $assignments) {
            // Create the connection and defer to _onCreate()
            $connectionInstance = Connection::from($connection);
            static::_onCreate($connectionInstance, $tableSchema, $assignments);

            // Hydrate the storage in-place
            return ModelBinder::hydrateStorage($tableSchema, $assignments, []);
        });

        return new static($deferStorage);
    }


    /**
     * Create a new model (implementation)
     * @param Connection $connection
     * @param TableSchema $tableSchema
     * @param array<string, mixed> $assignments
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @internal
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    private static function _onCreate(Connection $connection, TableSchema $tableSchema, array &$assignments) : void
    {
        $tableName = $tableSchema->getName();
        $q = $connection->getQueryGrammar()->getIdentifierQuote();

        $context = new QueryContext($connection, $tableSchema);

        // Append creation timestamp if needed
        $createColumnSchema = $tableSchema->getCreateTimestampColumn();
        if ($createColumnSchema !== null) {
            $createColumnName = $createColumnSchema->getName();
            if (!array_key_exists($createColumnName, $assignments)) {
                $assignments[$createColumnName] = PatchHost::tryCreateTimestamp($tableSchema->getModelClassName()) ?? Carbon::now();
            }
        }

        // Append update timestamp if needed
        $updateColumnSchema = $tableSchema->getUpdateTimestampColumn();
        if ($updateColumnSchema !== null) {
            $updateColumnName = $updateColumnSchema->getName();
            if (!array_key_exists($updateColumnName, $assignments)) {
                $assignments[$updateColumnName] = PatchHost::tryUpdateTimestamp($tableSchema->getModelClassName()) ?? Carbon::now();
            }
        }

        // Process the assignments
        $isFirstAssign = true;
        $assignmentValues = [];
        $keysSql = '';
        $valuesSql = '';

        foreach ($assignments as $assignKey => $assignValue) {
            if ($isFirstAssign) {
                $isFirstAssign = false;
            } else {
                $keysSql .= ', ';
                $valuesSql .= ', ';
            }

            $keysSql .= $context->getColumnNameSql($assignKey);
            $valuesSql .= '?';

            $assignColumnSchema = $context->getColumnSchema($assignKey);
            $assignmentValues[] = $assignColumnSchema !== null ? $assignColumnSchema->toDb($assignValue, $connection) : $assignValue;
        }

        $sql = 'INSERT INTO ' . $q->quote($tableName) . ' ' . Quote::bracket($keysSql) . ' VALUES ' . Quote::bracket($valuesSql);

        // Prepare statement and execute
        $statement = $connection->prepare($sql);
        $statement->bind($assignmentValues);
        $statement->execute();

        // Fetch auto-increment result when applicable
        $autoIncrementColumn = $tableSchema->getAutoIncrementColumn();
        if ($autoIncrementColumn !== null) {
            $lastId = $connection->lastInsertId();
            $assignments[$autoIncrementColumn->getName()] = $lastId;
        }
    }


    /**
     * Truncate the database associated to the model (deleting all records and reset auto-increments)
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public static final function truncate() : void
    {
        $tableSchema = TableSchema::from(static::class);

        $deferStorage = new ClosureDeferringModelStorageProvider($tableSchema, function(Model $instance, string $connection) use($tableSchema) {
            $connectionInstance = Connection::from($connection);
            $q = $connectionInstance->getQueryGrammar()->getIdentifierQuote();

            $sql = 'TRUNCATE TABLE ' . $q->quote($tableSchema->getName());

            $statement = $connectionInstance->prepare($sql);
            $statement->execute();

            return $this;
        });

        new static($deferStorage);
    }


    /**
     * Initialize storage if not yet initialized
     * @param Model $instance
     * @param ModelStorageProvidable|null $storage
     * @return ModelStorageProvidable
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     * @internal
     */
    private static function _initializeFromStorage(Model $instance, ?ModelStorageProvidable $storage) : ModelStorageProvidable
    {
        if ($storage instanceof DeferringModelStorageProvider) {
            return $storage->defer($instance, $instance->getConnectionName());
        }

        if ($storage !== null) return $storage;

        return ModelBinder::initializeStorage($instance);
    }


    /**
     * Connection name that models based on current type will use
     * @return ConnectionResolvable|string
     */
    public abstract static function getConnectionName() : ConnectionResolvable|string;


    /**
     * Column method prefix
     * @return string|null
     */
    public static function getColumnMethodPrefix() : ?string
    {
        return 'col';
    }
}