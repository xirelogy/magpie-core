<?php

namespace Magpie\Models\Schemas;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Bitmask;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;
use Magpie\Models\Annotations\Column as ColumnAttribute;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\AttributeInitializable;
use Magpie\Models\Impls\DefaultDataTypes;
use Magpie\Models\Impls\PatchHost;
use Magpie\Models\Schemas\Configs\SchemaPreference;
use ReflectionAttribute;

/**
 * Table column schema
 */
abstract class ColumnSchema implements Packable
{
    use CommonPackable;

    /**
     * @var TableSchema Parent table that current column belongs to
     */
    public readonly TableSchema $parentTable;
    /**
     * @var SchemaPreference Associated schema preference
     */
    protected readonly SchemaPreference $preference;
    /**
     * @var ReflectionAttribute Associated reflection attribute associated with this schema
     */
    protected readonly ReflectionAttribute $attribute;
    /**
     * @var ColumnAttribute Attribute instance
     */
    protected ColumnAttribute $attributeInstance;
    /**
     * @var DataType Data type descriptor
     */
    protected DataType $dataType;
    /**
     * @var ModelDefinition Definition type
     */
    protected ModelDefinition $defType;


    /**
     * Constructor
     * @param TableSchema $parentTable
     * @param SchemaPreference $preference
     * @param ReflectionAttribute $attribute
     * @throws SafetyCommonException
     */
    protected function __construct(TableSchema $parentTable, SchemaPreference $preference, ReflectionAttribute $attribute)
    {
        $this->parentTable = $parentTable;
        $this->preference = $preference;
        $this->attribute = $attribute;
        $this->attributeInstance = $attribute->newInstance();

        $rawModelDef = ModelDefinition::parse($this->attributeInstance->def);

        $lastAliasedDef = null;
        for (;;) {
            $aliasedDef = $this->preference->resolveAlias($rawModelDef);
            if ($aliasedDef === null) break;

            $lastAliasedDef = $aliasedDef;
            $rawModelDef = ModelDefinition::parse($aliasedDef->definition);
        }

        $rawModelDef = DefaultDataTypes::resolveAlias($rawModelDef);
        $this->dataType = DefaultDataTypes::resolve($rawModelDef) ?? throw new UnsupportedValueException($this->attributeInstance->def, _l('schema definition'));

        if ($lastAliasedDef !== null) {
            $this->dataType->nativeType = $lastAliasedDef->nativeType;
            if ($lastAliasedDef->castClass !== null) $this->dataType->castClass = $lastAliasedDef->castClass;
            if ($lastAliasedDef->initClass !== null) $this->dataType->initClass = $lastAliasedDef->initClass;
            if ($lastAliasedDef->primaryInitClass !== null) $this->dataType->primaryInitClass = $lastAliasedDef->primaryInitClass;
        }

        $this->defType = $rawModelDef->cloneWithBaseType($this->dataType->defBaseType);

        if ($this->attributeInstance->cast !== null) $this->dataType->castClass = $this->attributeInstance->cast;
        if ($this->attributeInstance->init !== null) $this->dataType->initClass = $this->attributeInstance->init;
    }


    /**
     * Column name
     * @return string
     */
    public function getName() : string
    {
        return $this->attributeInstance->name;
    }


    /**
     * Column's native type
     * @return string
     */
    public function getNativeType() : string
    {
        return $this->dataType->nativeType;
    }


    /**
     * Column's definition type
     * @return string
     */
    public function getDefinitionType() : string
    {
        return $this->defType;
    }


    /**
     * If column value is not null
     * @return bool
     */
    public function isNonNull() : bool
    {
        return $this->attributeInstance->isNonNull;
    }


    /**
     * If current column is the primary key / part of the primary key
     * @return bool
     */
    public function isPrimaryKey() : bool
    {
        return Bitmask::isSet($this->attributeInstance->attrs, ColumnAttribute::ATTR_PRIMARY_KEY);
    }


    /**
     * If this column has a unique value constraint
     * @return bool
     */
    public function isUnique() : bool
    {
        return Bitmask::isSet($this->attributeInstance->attrs, ColumnAttribute::ATTR_UNIQUE);
    }


    /**
     * If this column is auto-increment
     * @return bool
     */
    public function isAutoIncrement() : bool
    {
        return Bitmask::isSet($this->attributeInstance->attrs, ColumnAttribute::ATTR_AUTO_INCREMENT);
    }


    /**
     * If this column corresponds to creation timestamp
     * @return bool
     */
    public function isCreateTimestamp() : bool
    {
        return Bitmask::isSet($this->attributeInstance->attrs, ColumnAttribute::ATTR_FN_CREATED);
    }


    /**
     * If this column corresponds to update timestamp
     * @return bool
     */
    public function isUpdateTimestamp() : bool
    {
        return Bitmask::isSet($this->attributeInstance->attrs, ColumnAttribute::ATTR_FN_UPDATED);
    }


    /**
     * Get the effective cast class
     * @return class-string<AttributeCastable>|null
     */
    public function getEffectiveCastClass() : ?string
    {
        return $this->attributeInstance->cast ?? $this->dataType->castClass;
    }


    /**
     * Default value
     * @return ColumnExpression|string|int|float|bool|null
     */
    public function getDefaultValue() : ColumnExpression|string|int|float|bool|null
    {
        $defaultValueAttr = $this->attributeInstance->defaultValue;
        if ($defaultValueAttr === null) return null;

        if (is_array($defaultValueAttr)) {
            if (array_key_exists(ColumnAttribute::DEFAULT_EXPR, $defaultValueAttr)) {
                return static::getDefaultValueAsExpression($defaultValueAttr[ColumnAttribute::DEFAULT_EXPR]);
            } else if (array_key_exists(ColumnAttribute::DEFAULT_VALUE, $defaultValueAttr)) {
                $defaultValueAttr = $defaultValueAttr[ColumnAttribute::DEFAULT_VALUE];
            } else {
                return null;
            }
        }

        return $defaultValueAttr;
    }


    /**
     * Parse default value specification as expression
     * @param string $defaultValue
     * @return ColumnExpression
     */
    protected static function getDefaultValueAsExpression(string $defaultValue) : ColumnExpression
    {
        return ColumnExpression::raw($defaultValue);
    }


    /**
     * Column comments
     * @return string|null
     */
    public function getComments() : ?string
    {
        return $this->attributeInstance->comments;
    }


    /**
     * Accept value from database
     * @param mixed $value
     * @return mixed
     * @throws SafetyCommonException
     */
    public function fromDb(mixed $value) : mixed
    {
        if ($value === null) return null;

        $columnName = $this->attributeInstance->name;

        $castClassName = $this->dataType->castClass;
        if (!is_subclass_of($castClassName, AttributeCastable::class)) throw new ClassNotOfTypeException($castClassName, AttributeCastable::class);

        return $castClassName::fromDb($columnName, $value);
    }


    /**
     * Convert value to database
     * @param mixed $value
     * @return mixed
     * @throws SafetyCommonException
     */
    public function toDb(mixed $value) : mixed
    {
        if ($value === null) return null;

        $columnName = $this->attributeInstance->name;

        $castClassName = $this->dataType->castClass;
        if (!is_subclass_of($castClassName, AttributeCastable::class)) throw new ClassNotOfTypeException($castClassName, AttributeCastable::class);

        return $castClassName::toDb($columnName, $value);
    }


    /**
     * Initialize this column for given model
     * @return mixed
     * @throws SafetyCommonException
     */
    public function initialize() : mixed
    {
        if (PatchHost::tryInitializeColumn($this->parentTable->getModelClassName(), $this->getName(), $outResult)) {
            return $outResult;
        }

        $initClassName = $this->dataType->initClass;
        if ($initClassName === null) return null;

        if (!is_subclass_of($initClassName, AttributeInitializable::class)) throw new ClassNotOfTypeException($initClassName, AttributeInitializable::class);
        return $initClassName::generate();
    }


    /**
     * Primary initialization class, if any
     * @return class-string<AttributeInitializable>|null
     */
    public function getPrimaryInitClass() : ?string
    {
        if (!$this->isPrimaryKey()) return null;
        if ($this->attributeInstance->foreignModel !== null) return null;

        return $this->dataType->primaryInitClass;
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->name = $this->getName();
        $ret->nativeType = $this->getNativeType();
    }
}