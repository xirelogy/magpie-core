<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * Classifier class to support ActualJointModelFinalizer for specific model
 * @internal
 */
class ActualJointModelClassifier
{
    /**
     * @var TableSchema Associated table schema
     */
    protected readonly TableSchema $tableSchema;
    /**
     * @var array<string, mixed> Temporary values receiver
     */
    protected array $values;


    /**
     * Constructor
     * @param TableSchema $tableSchema
     */
    public function __construct(TableSchema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
        $this->values = [];
    }


    /**
     * Receive value into temporary storage
     * @param string $columnName
     * @param mixed $value
     * @return void
     */
    public function receive(string $columnName, mixed $value) : void
    {
        $this->values[$columnName] = $value;
    }


    /**
     * Finalize into model
     * @param class-string<Model> $tableClassName
     * @return Model
     * @throws ClassNotOfTypeException
     * @throws SafetyCommonException
     */
    public function finalize(string $tableClassName) : Model
    {
        $storage = ModelBinder::hydrateStorage($this->tableSchema, $this->values, []);
        $this->values = [];

        return new $tableClassName($storage);
    }
}