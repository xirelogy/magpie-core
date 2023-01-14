<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\AttributeInitializable;
use Magpie\Models\Concepts\ModelStorageProvidable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * Default model storage implementation
 * @internal
 */
class DefaultModelStorageProvider implements ModelStorageProvidable
{
    /**
     * @var class-string<Model> Model's class name
     */
    protected readonly string $modelClassName;
    /**
     * @var bool If this storage corresponds to newly created model
     */
    protected bool $isNew;
    /**
     * @var array<string, mixed> Attributes
     */
    protected array $attributes = [];
    /**
     * @var array<string, mixed> Primary key attributes
     */
    protected array $primaryKeyAttributes = [];
    /**
     * @var array<string, bool> Changed attributes
     */
    protected array $changedAttributes = [];


    /**
     * Constructor
     * @param class-string<Model> $modelClassName
     * @param bool $isNew
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $primaryKeyAttributes
     */
    protected function __construct(string $modelClassName, bool $isNew, array $attributes, array $primaryKeyAttributes = [])
    {
        $this->modelClassName = $modelClassName;
        $this->isNew = $isNew;
        $this->attributes = $attributes;
        $this->primaryKeyAttributes = $primaryKeyAttributes;
    }


    /**
     * @inheritDoc
     */
    public function getTableSchema() : TableSchema
    {
        return TableSchema::from($this->modelClassName);
    }


    /**
     * @inheritDoc
     */
    public function isNew() : bool
    {
        return $this->isNew;
    }


    /**
     * @inheritDoc
     */
    public function getAttributes() : iterable
    {
        foreach ($this->attributes as $key => $value) {
            yield $key => $value;
        }
    }


    /**
     * @inheritDoc
     */
    public function hasAttribute(string $key) : bool
    {
        return array_key_exists($key, $this->attributes);
    }


    /**
     * @inheritDoc
     */
    public function getAttribute(string $key) : mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        trigger_error("Undefined property: $key", E_USER_WARNING);

        throw new UnexpectedException();
    }


    /**
     * @inheritDoc
     */
    public function setAttribute(string $key, mixed $value) : void
    {
        // Check changes for non-new models
        if (!$this->isNew && array_key_exists($key, $this->attributes)) {
            if ($this->attributes[$key] === $value) return;
        }

        $this->attributes[$key] = $value;
        $this->changedAttributes[$key] = true;
    }


    /**
     * @inheritDoc
     */
    public function getIdentifyingAttributes() : iterable
    {
        yield from $this->primaryKeyAttributes;
    }


    /**
     * @inheritDoc
     */
    public function getChangedAttributes() : iterable
    {
        // Initialize attributes as required
        if ($this->isNew) {
            foreach ($this->getTableSchema()->getColumns() as $column) {
                $primaryInitClass = $column->getPrimaryInitClass();
                if ($primaryInitClass === null) continue;
                if (!is_subclass_of($primaryInitClass, AttributeInitializable::class)) continue;

                $columnName = $column->getName();
                if (!array_key_exists($columnName, $this->attributes)) continue;
                if ($this->attributes[$columnName] !== null) continue;

                // Generate manually
                $generated = $primaryInitClass::generate();

                // Cast the value if required
                $castClass = $column->getEffectiveCastClass();
                if ($castClass !== null && is_subclass_of($castClass, AttributeCastable::class)) {
                    $generated = $castClass::fromDb($columnName, $generated);
                }

                // Save to attribute
                $this->attributes[$columnName] = $generated;
            }
        }

        foreach ($this->attributes as $key => $value) {
            if ($this->isNew) {
                // New model, ignore null values
                if ($value === null) continue;
            } else {
                // Existing model, ignore unchanged attributes
                if (!array_key_exists($key, $this->changedAttributes)) continue;
            }

            yield $key => $value;
        }
    }


    /**
     * @inheritDoc
     */
    public function resetChanges(array $savedAttributes) : void
    {
        $tableSchema = $this->getTableSchema();

        // Absorb the saved attributes
        foreach ($savedAttributes as $savedKey => $savedValue) {
            $this->attributes[$savedKey] = $savedValue;

            // Update primary key value if relevant
            if ($tableSchema->getColumn($savedKey)?->isPrimaryKey() ?? false) {
                $this->primaryKeyAttributes[$savedKey] = $savedValue;
            }
        }

        $this->isNew = false;   // Any new model is no longer new upon reset
        $this->changedAttributes = [];
    }


    /**
     * @inheritDoc
     */
    public function destroy() : void
    {
        // The storage is reset into something not really usable
        $this->isNew = true;
        $this->attributes = [];
        $this->primaryKeyAttributes = [];
        $this->changedAttributes = [];
    }


    /**
     * Initialize an empty (initial) storage
     * @param TableSchema $tableSchema
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(TableSchema $tableSchema) : static
    {
        $attributes = [];

        foreach ($tableSchema->getColumns() as $columnSchema) {
            $attributes[$columnSchema->getName()] = $columnSchema->initialize();
        }

        return new static($tableSchema->getModelClassName(), true, $attributes);
    }


    /**
     * Hydrate a storage
     * @param TableSchema $tableSchema
     * @param array<string, mixed> $values
     * @param array<string, class-string<AttributeCastable>> $extraCasts
     * @return static
     * @throws SafetyCommonException
     */
    public static function hydrate(TableSchema $tableSchema, array $values, array $extraCasts) : static
    {
        $translatedValues = static::translateDatabaseValues($tableSchema, $values, $extraCasts, $primaryKeyValues);

        return new static($tableSchema->getModelClassName(), false, $translatedValues, $primaryKeyValues);
    }


    /**
     * Translate database values according to schema
     * @param TableSchema $tableSchema
     * @param array<string, mixed> $values
     * @param array<string, class-string<AttributeCastable>> $extraCasts
     * @param array<string, mixed>|null $outPrimaryKeys
     * @return array<string, mixed>
     * @throws SafetyCommonException
     */
    protected static function translateDatabaseValues(TableSchema $tableSchema, array $values, array $extraCasts, ?array &$outPrimaryKeys = null) : array
    {
        $ret = [];
        $outPrimaryKeys = [];

        foreach ($tableSchema->getColumns() as $columnSchema) {
            $key = $columnSchema->getName();
            if (array_key_exists($key, $values)) {
                $ret[$key] = $columnSchema->fromDb($values[$key]);
            } else {
                $ret[$key] = null;
            }

            if ($columnSchema->isPrimaryKey()) $outPrimaryKeys[$key] = $ret[$key];
        }

        // All additional attributes are stacked behind
        foreach ($values as $key => $value) {
            if (array_key_exists($key, $ret)) continue;
            if (array_key_exists($key, $extraCasts)) {
                $castClass = $extraCasts[$key];
                if (!is_subclass_of($castClass, AttributeCastable::class)) throw new ClassNotOfTypeException($castClass, AttributeCastable::class);
                $ret[$key] = $castClass::fromDb($key, $value);
            } else {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }
}