<?php

namespace Magpie\Models;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\DuplicatedKeyException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\JointSpecifiable;
use Magpie\Models\Enums\JointType;
use Magpie\Models\Exceptions\ModelJoinAcrossDifferentConnectionsException;
use Magpie\Models\Impls\ActualJointModelQuery;
use Magpie\Models\Impls\ActualJointSpecification;
use Magpie\Models\Schemas\TableSchema;

/**
 * Definition for joint model query
 */
class JointModelDefinition
{
    /**
     * @var string Model connection
     */
    protected string $connection;
    /**
     * @var array<string, TableSchema> All table schemas in the join
     */
    protected array $tableSchemas = [];
    /**
     * @var array<ActualJointSpecification> All joint specifications
     */
    protected array $jointSpecs = [];


    /**
     * Constructor
     * @param Model|class-string<Model> $modelSpec
     * @throws SafetyCommonException
     */
    protected function __construct(Model|string $modelSpec)
    {
        $tableSchema = TableSchema::from($modelSpec);
        $this->connection = static::getConnectionNameFromSchema($tableSchema);

        $this->tableSchemas[$tableSchema->getModelClassName()] = $tableSchema;
    }


    /**
     * Join a table to the definition
     * @param Model|string $jointModelSpec
     * @param JointType $type
     * @return JointSpecifiable
     * @throws SafetyCommonException
     */
    public final function join(Model|string $jointModelSpec, JointType $type = JointType::INNER) : JointSpecifiable
    {
        $jointSchema = TableSchema::from($jointModelSpec);
        if ($this->connection != static::getConnectionNameFromSchema($jointSchema)) {
            throw new ModelJoinAcrossDifferentConnectionsException();
        }
        if (array_key_exists($jointSchema->getModelClassName(), $this->tableSchemas)) {
            throw new DuplicatedKeyException($jointSchema->getModelClassName());
        }

        $spec = new ActualJointSpecification(iter_first($this->tableSchemas), $jointSchema, $type);
        $this->tableSchemas[$jointSchema->getModelClassName()] = $jointSchema;
        $this->jointSpecs[] = $spec;

        return $spec;
    }


    /**
     * Create query for current joint
     * @return Query<JointModel>
     */
    public final function query() : Query
    {
        return new ActualJointModelQuery($this->connection, $this->tableSchemas, $this->jointSpecs);
    }


    /**
     * Initialize from given model
     * @param Model|class-string<Model> $modelSpec
     * @return static
     * @throws SafetyCommonException
     */
    public final static function from(Model|string $modelSpec) : static
    {
        return new static($modelSpec);
    }


    /**
     * Retrieve connection name from table schema
     * @param TableSchema $schema
     * @return string
     * @throws ClassNotOfTypeException
     */
    protected static function getConnectionNameFromSchema(TableSchema $schema) : string
    {
        $modelClassName = $schema->getModelClassName();
        if (!is_subclass_of($modelClassName, Model::class)) throw new ClassNotOfTypeException($modelClassName, Model::class);

        return $modelClassName::getConnectionName();
    }
}