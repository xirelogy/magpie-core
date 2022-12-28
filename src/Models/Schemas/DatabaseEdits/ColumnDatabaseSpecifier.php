<?php

namespace Magpie\Models\Schemas\DatabaseEdits;

use Magpie\General\Str;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Concepts\ColumnDatabaseSpecifiable;

/**
 * Specifier for column database
 */
abstract class ColumnDatabaseSpecifier implements ColumnDatabaseSpecifiable
{
    /**
     * @var string Column name
     */
    protected string $name;
    /**
     * @var string|null Definition type
     */
    protected ?string $defType = null;
    /**
     * @var bool If type is not null
     */
    protected bool $isNonNull = false;
    /**
     * @var bool If column is part of primary key
     */
    protected bool $isPrimaryKey = false;
    /**
     * @var bool If column has constraint for unique values
     */
    protected bool $isUnique = false;
    /**
     * @var bool If column is auto increment
     */
    protected bool $isAutoIncrement = false;
    /**
     * @var bool If column has automatic timestamp during creation
     */
    protected bool $isCreateTimestamp = false;
    /**
     * @var bool If column has automatic timestamp during update
     */
    protected bool $isUpdateTimestamp = false;
    /**
     * @var ColumnExpression|string|int|float|bool|null Column default value
     */
    protected ColumnExpression|string|int|float|bool|null $defaultValue = null;
    /**
     * @var string|null Column comments
     */
    protected ?string $comments = null;


    /**
     * Constructor
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }


    /**
     * Column name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }


    /**
     * @inheritDoc
     */
    public function withDefinitionType(string $defType) : static
    {
        $this->defType = $defType;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withNonNull(bool $isNonNull = true) : static
    {
        $this->isNonNull = $isNonNull;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withPrimaryKey(bool $isPrimaryKey = true) : static
    {
        $this->isPrimaryKey = $isPrimaryKey;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withUnique(bool $isUnique = true) : static
    {
        $this->isUnique = $isUnique;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withAutoIncrement(bool $isAutoIncrement = true) : static
    {
        $this->isAutoIncrement = $isAutoIncrement;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withCreateTimestamp(bool $isCreateTimestamp = true) : static
    {
        $this->isCreateTimestamp = $isCreateTimestamp;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withUpdateTimestamp(bool $isUpdateTimestamp = true) : static
    {
        $this->isUpdateTimestamp = $isUpdateTimestamp;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withDefaultValue(ColumnExpression|string|int|float|bool|null $defaultValue) : static
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withComments(?string $comments) : static
    {
        $this->comments = Str::trimWithEmptyAsNull($comments);
        return $this;
    }
}