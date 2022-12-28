<?php

namespace Magpie\Models\Concepts;

use Magpie\Models\ColumnExpression;

/**
 * Concept of specifier for column database
 */
interface ColumnDatabaseSpecifiable
{
    /**
     * Specify definition type
     * @param string $defType
     * @return $this
     */
    public function withDefinitionType(string $defType) : static;


    /**
     * Specify that type is not null
     * @param bool $isNonNull
     * @return $this
     */
    public function withNonNull(bool $isNonNull = true) : static;


    /**
     * Specify that column is part of primary key
     * @param bool $isPrimaryKey
     * @return $this
     */
    public function withPrimaryKey(bool $isPrimaryKey = true) : static;


    /**
     * Specify that column has constraint for unique values
     * @param bool $isUnique
     * @return $this
     */
    public function withUnique(bool $isUnique = true) : static;


    /**
     * Specify that column is auto-increment
     * @param bool $isAutoIncrement
     * @return $this
     */
    public function withAutoIncrement(bool $isAutoIncrement = true) : static;


    /**
     * Specify that column has automatic timestamp during creation
     * @param bool $isCreateTimestamp
     * @return $this
     */
    public function withCreateTimestamp(bool $isCreateTimestamp = true) : static;


    /**
     * Specify that column has automatic timestamp during update
     * @param bool $isUpdateTimestamp
     * @return $this
     */
    public function withUpdateTimestamp(bool $isUpdateTimestamp = true) : static;


    /**
     * Specify default value
     * @param ColumnExpression|string|int|float|bool|null $defaultValue
     * @return $this
     */
    public function withDefaultValue(ColumnExpression|string|int|float|bool|null $defaultValue) : static;


    /**
     * Specify comments
     * @param string|null $comments
     * @return $this
     */
    public function withComments(?string $comments) : static;
}