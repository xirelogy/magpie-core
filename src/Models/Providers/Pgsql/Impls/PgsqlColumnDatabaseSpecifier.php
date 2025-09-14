<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseSpecifier;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * PostgreSQL specifier for column database
 * @internal
 */
class PgsqlColumnDatabaseSpecifier extends ColumnDatabaseSpecifier
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
     * Column comments
     * @return string|null
     */
    public function getComments() : ?string
    {
        return $this->comments;
    }


    /**
     * Compile the column specifier
     * @param PgsqlConnection $connection
     * @return string
     * @throws SafetyCommonException
     */
    public function _compile(PgsqlConnection $connection) : string
    {
        $q = $connection->getQueryGrammar()->getIdentifierQuote();

        $ret = $q->quote($this->name) . ' ' . static::exportDefinitionType($this->defType, $this->isAutoIncrement);
        if ($this->isNonNull) {
            $ret .= ' NOT NULL';
        }
        if ($this->isUnique) {
            $ret .= ' UNIQUE';
        }

        if ($this->defaultValue !== null) {
            $ret .= ' DEFAULT ' . static::exportDefaultValue($connection, $this->defaultValue);
        } else if ($this->isUpdateTimestamp) {
            $ret .= ' DEFAULT now()';
        } else if ($this->isCreateTimestamp) {
            $ret .= ' DEFAULT now()';
        } else if ($this->isNonNull && $this->defType === 'timestamp') {
            $ret .= ' DEFAULT now()';
        }

        return $ret;
    }


    /**
     * Export default value with literals considered
     * @param PgsqlConnection $connection
     * @param ColumnExpression|string|int|float|bool $defaultValue
     * @return string
     * @throws SafetyCommonException
     */
    protected static function exportDefaultValue(PgsqlConnection $connection, ColumnExpression|string|int|float|bool $defaultValue) : string
    {
        if (is_numeric($defaultValue)) return "$defaultValue";
        if (is_bool($defaultValue)) return ($defaultValue ? 'TRUE' : 'FALSE');
        if (is_string($defaultValue)) return $connection->quoteString($defaultValue);

        if ($defaultValue instanceof ColumnExpression) {
            $context = new QueryContext($connection, null);
            $ret = $defaultValue->_finalize($context);
            if (count($ret->values) > 0) throw new UnsupportedException();
            return $ret->sql;
        }

        throw new UnexpectedException();    // Should not reach here
    }


    /**
     * Export native type
     * @param string $nativeType
     * @param bool $isAutoIncrement
     * @return string
     * @throws SafetyCommonException
     */
    protected static function exportDefinitionType(string $nativeType, bool $isAutoIncrement) : string
    {
        if ($nativeType === 'timestamp') return 'TIMESTAMPTZ';

        $def = ModelDefinition::parse($nativeType);

        // Special case: auto incremental key
        if ($isAutoIncrement) {
            switch (strtolower($def->baseType)) {
                case 'tinyint':
                case 'utinyint':
                case 'smallint':
                case 'usmallint':
                    return 'SMALLSERIAL';
                case 'int':
                case 'uint':
                    return 'SERIAL';
                case 'bigint':
                case 'ubigint':
                    return 'BIGSERIAL';
            }

            throw new UnsupportedException();
        }

        switch (strtolower($def->baseType)) {
            case 'ubigint':
                $def = $def->cloneWithBaseType('bigint');
                break;
            case 'uint':
                $def = $def->cloneWithBaseType('int');
                break;
            case 'tinyint':
            case 'utinyint':
            case 'usmallint':
                $def = $def->cloneWithBaseType('smallint');
                break;
        }

        return strtoupper($def->__toString());
    }
}