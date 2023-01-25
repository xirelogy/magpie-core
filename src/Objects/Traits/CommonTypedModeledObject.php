<?php

namespace Magpie\Objects\Traits;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Factories\ClassFactory;
use Magpie\Models\ColumnName;
use Magpie\Models\Concepts\ModelAccessible;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Identifier;
use Magpie\Models\JointModel;
use Magpie\Models\JointModelDefinition;
use Magpie\Models\Model;
use Magpie\Models\ModelMap;
use Magpie\Models\Query;
use Magpie\Objects\Supports\QueryOptions;
use Magpie\System\HardCore\ClassReflection;

/**
 * Common multi-typed modeled object
 * @template MT
 * @requires \Magpie\Objects\ModeledObject
 */
trait CommonTypedModeledObject
{
    /**
     * Create a database query for the base model
     * @return Query<MT>
     * @throws SafetyCommonException
     */
    protected static final function createBaseQuery() : Query
    {
        $totalModelClasses = count(iter_flatten(static::getModelClassNames(), false));
        if ($totalModelClasses > 1) return static::createJointDefinition()->query();

        $baseModelClassName = static::getBaseModelClassName();
        if (!is_subclass_of($baseModelClassName, Model::class)) throw new ClassNotOfTypeException($baseModelClassName, Model::class);

        return $baseModelClassName::query();
    }


    /**
     * Try to create a new instance from given model
     * @param mixed $db
     * @return static|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected static final function fromModel(mixed $db) : ?static
    {
        if ($db instanceof JointModel) return static::fromMultiModels(static::getIdFromJointModel($db), $db);

        $baseModelClassName = static::getBaseModelClassName();
        if (!is_subclass_of($baseModelClassName, Model::class)) throw new ClassNotOfTypeException($baseModelClassName, Model::class);
        if (!is_a($db, $baseModelClassName)) return null;

        $models = ModelMap::for([$db]);
        $modelId = $db->{static::getIdColumnName()};

        if (ClassReflection::isConcrete(static::class)) {
            return static::fromMultiModels($modelId, $models);
        } else {
            $typeClass = $db->{static::getTypeClassColumnName()};

            $className = ClassFactory::resolve($typeClass, static::getBaseClassName());
            if (!is_subclass_of($className, static::class)) return null;

            return $className::fromMultiModels($modelId, $models);
        }
    }


    /**
     * Try to create a new instance from given multiple model
     * @param Identifier|string|int $id
     * @param ModelAccessible $dbs
     * @return static|null
     * @throws NullException
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected static final function fromMultiModels(Identifier|string|int $id, ModelAccessible $dbs) : ?static
    {
        $retDbs = [];

        if (ClassReflection::isConcrete(static::class)) {
            $finalClassName = static::class;
        } else {
            $baseModelClassName = static::getBaseModelClassName();
            if (!is_subclass_of($baseModelClassName, Model::class)) throw new ClassNotOfTypeException($baseModelClassName, Model::class);
            $baseDb = $dbs->accessModel($baseModelClassName);
            if ($baseDb === null) {
                $baseDb = $baseModelClassName::query()->where(static::getIdColumnName(), $id)->first();
                if ($baseDb === null) return null;
            }
            $typeClass = $baseDb->{static::getTypeClassColumnName()};
            $className = ClassFactory::resolve($typeClass, static::getBaseClassName());
            if (!is_subclass_of($className, static::class)) return null;

            $finalClassName = $className;
        }

        foreach ($finalClassName::getModelClassNames() as $modelClass) {
            $db = $dbs->accessModel($modelClass);
            if ($db === null) {
                if (!is_subclass_of($modelClass, Model::class)) throw new ClassNotOfTypeException($modelClass, Model::class);
                $db = $modelClass::query()->where(static::getIdColumnName(), $id)->first();
                if ($db === null) return null;
            }

            $retDbs[] = $db;
        }

        if (count($retDbs) <= 0) return null;

        return $finalClassName::constructFromModels(...$retDbs);
    }


    /**
     * Extract ID from joint model
     * @param JointModel $db
     * @return Identifier|string|int
     * @throws SafetyCommonException
     */
    protected static function getIdFromJointModel(JointModel $db) : Identifier|string|int
    {
        foreach ($db->getModels() as $model) {
            return $model->{static::getIdColumnName()};
        }

        throw new NullException();
    }


    /**
     * Update the query options for multi-typed setup
     * @param QueryOptions $options
     * @return void
     * @throws SafetyCommonException
     */
    protected static function onUpdateTypeClassQueryOptions(QueryOptions $options) : void
    {
        if (ClassReflection::isConcrete(static::class)) {
            $baseModelClassName = static::getBaseModelClassName();
            if (!is_subclass_of($baseModelClassName, Model::class)) throw new ClassNotOfTypeException($baseModelClassName, Model::class);

            $columnName = ColumnName::fromModel($baseModelClassName, static::getTypeClassColumnName());
            $options->withSimpleEqualCondition($columnName, static::getTypeClass());
        }
    }


    /**
     * Create a joint-model database query with the given query options
     * @param QueryOptions|null $options
     * @return Query
     * @throws SafetyCommonException
     */
    protected static final function createJointQuery(?QueryOptions &$options = null) : Query
    {
        $options = $options ?? QueryOptions::default();

        $def = static::createJointDefinition();
        $query = $def->query();

        static::onUpdateQueryOptions($options);

        $options->applyOnQuery($query);

        return $query;
    }


    /**
     * Create a joint-model definition
     * @return JointModelDefinition
     * @throws SafetyCommonException
     */
    protected static final function createJointDefinition() : JointModelDefinition
    {
        $idColumnName = static::getIdColumnName();

        $def = null;
        foreach (static::getModelClassNames() as $modelClass) {
            if ($def === null) {
                $def = JointModelDefinition::from($modelClass);
            } else {
                $def->join($modelClass)->on($idColumnName, $idColumnName);
            }
        }

        if ($def === null) throw new NullException();
        return $def;
    }


    /**
     * The base class name where class factory is based on
     * @return string
     */
    protected static abstract function getBaseClassName() : string;


    /**
     * The base class name for model
     * @return class-string<Model>
     * @throws SafetyCommonException
     */
    protected static final function getBaseModelClassName() : string
    {
        return iter_first(static::getModelClassNames()) ?? throw new NullException();
    }


    /**
     * All model classes
     * @return iterable<class-string<Model>>
     */
    protected static abstract function getModelClassNames() : iterable;


    /**
     * The column name containing the 'TypeClass' at the base model
     * @return string
     */
    protected static abstract function getTypeClassColumnName() : string;


    /**
     * The column name containing the 'Id' that links all models together
     * @return string
     */
    protected static function getIdColumnName() : string
    {
        return static::$_traitNameOf_Id;
    }
}