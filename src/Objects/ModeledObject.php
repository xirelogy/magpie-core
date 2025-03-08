<?php

/** @noinspection PhpUndefinedFieldInspection */

namespace Magpie\Objects;

use Exception;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\GeneralPersistenceException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Identifiable;
use Magpie\General\Concepts\Savable;
use Magpie\General\Packs\PackContext;
use Magpie\Models\ColumnName;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Identifier;
use Magpie\Models\Model;
use Magpie\Models\Query;
use Magpie\Objects\Supports\QueryOptions;
use Magpie\Objects\Supports\QueryOrderCondition;

/**
 * A modeled object
 * @template MT
 */
abstract class ModeledObject extends CommonObject implements Identifiable, Savable
{
    /**
     * @inheritDoc
     */
    public function getId() : Identifier|string|int
    {
        try {
            return $this->getBaseModel()->{static::$_traitNameOf_Id};
        } catch (Exception) {
            return static::getDefaultInvalidId();
        }
    }


    /**
     * @inheritDoc
     */
    public final function save() : void
    {
        $this->onBeforeSave();
        $this->onSave();
        $this->onAfterSave();
    }


    /**
     * Handling before saving
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function onBeforeSave() : void
    {
        _throwable(1) ?? throw new NullException();
        _throwable(2) ?? throw new GeneralPersistenceException();
    }


    /**
     * Handle saving
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function onSave() : void
    {
        foreach ($this->getModels() as $model) {
            $model->save();
        }
    }


    /**
     * Handling after saving
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function onAfterSave() : void
    {
        _throwable(1) ?? throw new NullException();
        _throwable(2) ?? throw new GeneralPersistenceException();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->id = $this->getId();
    }


    /**
     * The base model
     * @return Model
     * @throws NullException
     */
    protected final function getBaseModel() : Model
    {
        return iter_first($this->getModels()) ?? throw new NullException();
    }


    /**
     * All related models
     * @return iterable<Model>
     */
    protected abstract function getModels() : iterable;


    /**
     * Find instance with given ID, with compulsory return expected
     * @param Identifier|string|int|null $id
     * @param QueryOptions|null $options
     * @return static
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public static function findRequired(Identifier|string|int|null $id, ?QueryOptions $options = null) : static
    {
        $options = $options ?? QueryOptions::default();
        $options->withSoftDeletesIncluded();

        return static::find($id, $options) ?? throw new NullException();
    }


    /**
     * Find instance with given ID, with compulsory return expected, only when ID is valid
     * @param Identifier|string|int|null $id
     * @param QueryOptions|null $options
     * @return static|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public static function findRequiredWhenIdValid(Identifier|string|int|null $id, ?QueryOptions $options = null) : ?static
    {
        if ($id === null) return null;

        return static::findRequired($id, $options);
    }


    /**
     * Find instance with given ID
     * @param Identifier|string|int|null $id
     * @param QueryOptions|null $options
     * @return static|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public static function find(Identifier|string|int|null $id, ?QueryOptions $options = null) : ?static
    {
        if ($id === null) return null;

        $idColumn = ColumnName::fromModel(static::getBaseModelClassName(), static::$_traitNameOf_Id);
        $query = static::createQuery($options)->where($idColumn, $id);

        $db = $query->first();
        return static::fromModel($db);
    }


    /**
     * Create a database query with the given query options
     * @param QueryOptions|null $options
     * @return Query<MT>
     * @throws SafetyCommonException
     */
    protected static final function createQuery(?QueryOptions &$options = null) : Query
    {
        $options = $options ?? QueryOptions::default();

        $query = static::createModelQuery();

        if (!$options->isSoftDeletesIncluded && isset(static::$_traitEnabled_DeleteTimestamp)) {
            $deleteColumn = ColumnName::fromModel(static::getBaseModelClassName(), static::$_traitNameOf_DeletedAt);
            $query->where($deleteColumn, Query::null());
        }

        static::onUpdateQueryOptions($options);
        $options->withDefaultOrderCondition(static::onDefaultQueryOrderCondition());

        $options->applyOnQuery($query);

        return $query;
    }


    /**
     * Update the query options
     * @param QueryOptions $options
     * @return void
     * @throws SafetyCommonException
     */
    protected static function onUpdateQueryOptions(QueryOptions $options) : void
    {
        _throwable() ?? throw new NullException();
        _used($options);
    }


    /**
     * Get the default query order condition for query options
     * @return QueryOrderCondition|null
     */
    protected static function onDefaultQueryOrderCondition() : ?QueryOrderCondition
    {
        return null;
    }


    /**
     * Create a database query for the model
     * @return Query<MT>
     * @throws SafetyCommonException
     */
    protected static function createModelQuery() : Query
    {
        return static::createBaseQuery();
    }


    /**
     * Create a database query for the base model
     * @return Query<MT>
     * @throws SafetyCommonException
     */
    protected static function createBaseQuery() : Query
    {
        $modelClassName = static::getBaseModelClassName();
        if (!is_subclass_of($modelClassName, Model::class)) throw new ClassNotOfTypeException($modelClassName, Model::class);
        return $modelClassName::query();
    }


    /**
     * The base class name for model
     * @return class-string<Model>
     * @throws SafetyCommonException
     */
    protected static abstract function getBaseModelClassName() : string;


    /**
     * Try to create instances from given models
     * @param iterable $dbs
     * @param bool $isAllowNull If null values allowed
     * @return iterable<static>
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected static final function fromModels(iterable $dbs, bool $isAllowNull = false) : iterable
    {
        foreach ($dbs as $db) {
            $object = static::fromModel($db);
            if (!$isAllowNull && ($object === null)) continue;
            yield $object;
        }
    }


    /**
     * Try to create a new instance from given model
     * @param mixed $db
     * @return static|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected static abstract function fromModel(mixed $db) : ?static;


    /**
     * Construct from multiple models
     * @param Model ...$dbs
     * @return static
     * @throws SafetyCommonException
     */
    protected static function constructFromModels(Model ...$dbs) : static
    {
        _throwable() ?? throw new NullException();

        return new static(...$dbs);
    }


    /**
     * The ID to be used for invalid identifier
     * @return Identifier|string|int
     */
    protected static function getDefaultInvalidId() : Identifier|string|int
    {
        return 0;
    }


    /**
     * Check for equality between two objects
     * @param Identifiable $lhs
     * @param Identifiable $rhs
     * @return bool
     */
    public static function isEqual(Identifiable $lhs, Identifiable $rhs) : bool
    {
        return Identifier::isEqual($lhs->getId(), $rhs->getId());
    }
}