<?php

namespace Magpie\Models\Providers\Sqlite\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnexpectedException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\ColumnExpression;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Models\Schemas\DatabaseEdits\ColumnDatabaseSpecifier;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * SQLite specifier for column database
 * @internal
 */
class SqliteColumnDatabaseSpecifier extends ColumnDatabaseSpecifier
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
     * Compile the column specifier
     * @param SqliteConnection $connection
     * @return string
     * @throws SafetyCommonException
     */
    public function _compile(SqliteConnection $connection) : string
    {
        $ret = SqliteGrammar::escapeName($this->name) . ' ' . static::exportDefinitionType($this->defType);
        if ($this->isAutoIncrement) {
            // Force the type to become INTEGER
            if (!static::isTypeSupportsAutoIncrement($this->defType)) throw new UnsupportedValueException($this->defType, _l('auto increment'));
            $ret = SqliteGrammar::escapeName($this->name) . ' INTEGER';
        }

        if ($this->isNonNull) $ret .= ' NOT NULL';

        if ($this->isAutoIncrement) {
            if (!$this->isPrimaryKey) throw new UnsupportedException();
            $ret .= ' PRIMARY KEY AUTOINCREMENT';
        }

        if ($this->defaultValue !== null) {
            $ret .= ' DEFAULT ' . static::exportDefaultValue($connection, $this->defaultValue);
        } else if ($this->isUpdateTimestamp || $this->isCreateTimestamp) {
            $ret .= ' DEFAULT (CURRENT_TIMESTAMP)';
        }

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
        $def = ModelDefinition::parse($nativeType);
        return strtoupper($def->__toString());
    }


    /**
     * Export default value with literals considered
     * @param SqliteConnection $connection
     * @param ColumnExpression|string|int|float|bool $defaultValue
     * @return string
     * @throws SafetyCommonException
     */
    protected static function exportDefaultValue(SqliteConnection $connection, ColumnExpression|string|int|float|bool $defaultValue) : string
    {
        if (is_numeric($defaultValue)) return "$defaultValue";
        if (is_bool($defaultValue)) return ($defaultValue ? '1' : '0');
        if (is_string($defaultValue)) return $connection->quoteString($defaultValue);

        if ($defaultValue instanceof ColumnExpression) {
            $context = new QueryContext($connection, null);
            $ret = $defaultValue->_finalize($context);
            if (count($ret->values) > 0) throw new UnsupportedException();
            return Quote::bracket($ret->sql);
        }

        throw new UnexpectedException();    // Should not reach here
    }


    /**
     * Check for auto increment support
     * @param string $type
     * @return bool
     */
    protected static function isTypeSupportsAutoIncrement(string $type) : bool
    {
        $type = strtolower($type);
        if ($type === 'int') return true;
        if ($type === 'integer') return true;

        return false;
    }
}