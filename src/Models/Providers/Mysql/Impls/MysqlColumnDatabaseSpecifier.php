<?php

namespace Magpie\Models\Providers\Mysql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseSpecifier;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * MySQL specifier for column database
 * @internal
 */
class MysqlColumnDatabaseSpecifier extends ColumnDatabaseSpecifier
{
    /**
     * If column is primary key
     * @return bool
     */
    public function isPrimaryKey() : bool
    {
        return $this->isPrimaryKey;
    }


    /**
     * @return bool
     */
    public function isUnique() : bool
    {
        return $this->isUnique;
    }


    /**
     * Compile the column specifier
     * @param MysqlConnection $connection
     * @return string
     * @throws SafetyCommonException
     */
    public function _compile(MysqlConnection $connection) : string
    {
        $q = $connection->getQueryGrammar()->getIdentifierQuote();

        $ret = $q->quote($this->name) . ' ' . static::exportDefinitionType($this->defType);
        if ($this->isNonNull) {
            $ret .= ' NOT NULL';
        } else {
            $ret .= ' NULL';
        }
        if ($this->isAutoIncrement) $ret .= ' AUTO_INCREMENT';
        if ($this->defaultValue !== null) {
            $ret .= ' DEFAULT ' . static::exportDefaultValue($connection, $this->defaultValue);
        } else if ($this->isUpdateTimestamp) {
            $ret .= ' DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
        } else if ($this->isCreateTimestamp) {
            $ret .= ' DEFAULT CURRENT_TIMESTAMP';
        } else if ($this->isNonNull && $this->defType === 'timestamp') {
            $ret .= ' DEFAULT CURRENT_TIMESTAMP';
        }
        if ($this->comments !== null) $ret .= ' COMMENT ' . $connection->quoteString($this->comments);

        return $ret;
    }


    /**
     * Export native type
     * @param string $nativeType
     * @return string
     * @throws SafetyCommonException
     */
    protected static function exportDefinitionType(string $nativeType) : string
    {
        if ($nativeType === 'bool') $nativeType = 'tinyint(1)';

        $def = ModelDefinition::parse($nativeType);

        $isUnsigned = false;

        switch (strtolower($def->baseType)) {
            case 'ubigint':
                $def = $def->cloneWithBaseType('bigint');
                $isUnsigned = true;
                break;
            case 'uint':
                $def = $def->cloneWithBaseType('int');
                $isUnsigned = true;
                break;
            case 'usmallint':
                $def = $def->cloneWithBaseType('smallint');
                $isUnsigned = true;
                break;
            case 'utinyint':
                $def = $def->cloneWithBaseType('tinyint');
                $isUnsigned = true;
                break;
        }

        $ret = strtoupper($def->__toString());
        if ($isUnsigned) $ret .= ' UNSIGNED';
        return $ret;
    }


    /**
     * Export default value with literals considered
     * @param MysqlConnection $connection
     * @param ColumnExpression|string|int|float|bool $defaultValue
     * @return string
     * @throws SafetyCommonException
     */
    protected static function exportDefaultValue(MysqlConnection $connection, ColumnExpression|string|int|float|bool $defaultValue) : string
    {
        if (is_numeric($defaultValue)) return "$defaultValue";
        if (is_bool($defaultValue)) return ($defaultValue ? '1' : '0');
        if (is_string($defaultValue)) return $connection->quoteString($defaultValue);

        if ($defaultValue instanceof ColumnExpression) {
            $context = new QueryContext($connection, null);
            $ret = $defaultValue->_finalize($context);
            if (count($ret->values) > 0) throw new UnsupportedException();
            return $ret->sql;
        }

        throw new UnexpectedException();    // Should not reach here
    }
}