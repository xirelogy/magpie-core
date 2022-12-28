<?php

namespace Magpie\Models;

use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\QuerySelectable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;
use Magpie\Models\Impls\SqlFormat;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\TableSchema;

/**
 * Column name specification
 */
class ColumnName implements QuerySelectable, PreferStringable
{
    /**
     * @var TableSchema|null Associated table (if any)
     */
    public readonly ?TableSchema $table;
    /**
     * @var string Column name
     */
    public readonly string $name;


    /**
     * Constructor
     * @param TableSchema|null $table
     * @param string $name
     */
    protected function __construct(?TableSchema $table, string $name)
    {
        $this->table = $table;
        $this->name = $name;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->name;
    }


    /**
     * Associated column schema for this column, with given reference (fallback) model
     * @param TableSchema|null $refSchema
     * @return ColumnSchema|null
     * @throws SafetyCommonException
     */
    public function getSchema(?TableSchema $refSchema) : ?ColumnSchema
    {
        if ($this->table !== null) {
            return $this->table->getColumn($this->name);
        }

        return $refSchema?->getColumn($this->name) ?? null;
    }


    /**
     * @inheritDoc
     * @internal
     */
    public function _finalize(QueryContext $context) : QueryStatement
    {
        // Declare the corresponding cast if model finalizer is present
        if ($context->modelFinalizer !== null) {
            $castClass = $this->getSchema($context->tableSchema)?->getEffectiveCastClass();
            if ($castClass !== null) {
                $context->modelFinalizer->addCast($this->name, $castClass);
            }
        }

        $sql = $this->toSql($context->tableSchema);
        return new QueryStatement($sql);
    }


    /**
     * Expressed in SQL
     * @param TableSchema|null $refSchema
     * @return string
     */
    public function toSql(?TableSchema $refSchema) : string
    {
        if ($this->table === null || $refSchema === null) {
            // No specific model, or no reference model, always assume fallback
            return SqlFormat::backTick($this->name);
        }

        if ($this->table->getName() === $refSchema->getName()) {
            // The specific model is the same as the reference, no need for table specification
            return SqlFormat::backTick($this->name);
        }

        // Qualified column name with table name
        return SqlFormat::backTick($this->table->getName()) . '.' . SqlFormat::backTick($this->name);
    }


    /**
     * Construct from specific name
     * @param string $name
     * @return static
     */
    public static function from(string $name) : static
    {
        return new static(null, $name);
    }


    /**
     * Construct from specific name of given table
     * @param TableSchema $table
     * @param string $name
     * @return static
     */
    public static function fromTable(TableSchema $table, string $name) : static
    {
        return new static($table, $name);
    }


    /**
     * Construct from specific name of given model (or model class name)
     * @param Model|string $modelSpec
     * @param string $name
     * @return static
     * @throws SafetyCommonException
     */
    public static function fromModel(Model|string $modelSpec, string $name) : static
    {
        return new static(TableSchema::from($modelSpec), $name);
    }
}