<?php

namespace Magpie\Models\Impls;

use Closure;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Models\JointModel;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * Joint model finalizer function
 * @internal
 */
class ActualJointModelFinalizer extends ModelFinalizer
{
    /**
     * @var array<class-string<Model>, ActualJointModelClassifier> All model classifiers
     */
    protected array $modelClassifiers = [];
    /**
     * @var array<string, Closure> All column classifiers
     */
    protected array $columnClassifiers = [];


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->markAllColumnsSelected();
    }


    /**
     * Declare a selection alias made
     * @param TableSchema $tableSchema
     * @param string $columnName
     * @param string $aliasName
     * @return void
     * @throws ClassNotOfTypeException
     */
    public function declareSelectAlias(TableSchema $tableSchema, string $columnName, string $aliasName) : void
    {
        $tableClassName = $tableSchema->getModelClassName();

        if (!array_key_exists($tableClassName, $this->modelClassifiers)) {
            if (!is_subclass_of($tableClassName, Model::class)) throw new ClassNotOfTypeException($tableClassName, Model::class);
            $this->modelClassifiers[$tableClassName] = new ActualJointModelClassifier($tableSchema);
        }

        $modelClassifier = $this->modelClassifiers[$tableClassName];
        $this->columnClassifiers[$aliasName] = function(mixed $value) use($modelClassifier, $columnName) {
            $modelClassifier->receive($columnName, $value);
        };
    }


    /**
     * @inheritDoc
     */
    public function finalize(array $values) : JointModel
    {
        foreach ($values as $valueKey => $value) {
            $columnClassifier = $this->columnClassifiers[$valueKey] ?? null;
            if ($columnClassifier === null) continue;

            $columnClassifier($value);
        }

        $outModels = [];
        foreach ($this->modelClassifiers as $tableClassName => $modelClassifier) {
            $outModels[$tableClassName] = $modelClassifier->finalize($tableClassName);
        }

        // Pack into joint model
        return new class($outModels) extends JointModel {
            /**
             * Constructor
             * @param array $models
             */
            public function __construct(array $models)
            {
                parent::__construct($models);
            }
        };
    }
}