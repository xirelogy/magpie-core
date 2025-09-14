<?php

namespace Magpie\Models\Providers\Pgsql\Impls;

use Magpie\General\Sugars\Quote;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Schemas\ColumnSchemaAtDatabase;

/**
 * PostgreSQL column schema at database level
 * @internal
 */
class PgsqlColumnSchemaAtDatabase extends ColumnSchemaAtDatabase
{
    /**
     * @var PgsqlTableMetadataAtDatabase Table's metadata
     */
    protected readonly PgsqlTableMetadataAtDatabase $tableMeta;
    /**
     * @var array Associated record
     */
    protected readonly array $record;


    /**
     * Constructor
     * @param PgsqlConnection $connection
     * @param PgsqlTableMetadataAtDatabase $tableMeta
     * @param array $record
     */
    public function __construct(PgsqlConnection $connection, PgsqlTableMetadataAtDatabase $tableMeta, array $record)
    {
        _used($connection);
        $this->tableMeta = $tableMeta;
        $this->record = $record;
    }


    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->record['column_name'];
    }


    /**
     * @inheritDoc
     */
    public function getDefinitionType() : string
    {
        $ret = strtolower($this->record['udt_name']);

        switch ($ret) {
            case 'char':
            case 'varchar':
                $cMaxLength = $this->record['character_maximum_length'];
                if ($cMaxLength !== null) $ret .= Quote::bracket($cMaxLength);
                break;
        }

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function isNonNull() : bool
    {
        return strtoupper($this->record['is_nullable']) === 'NO';
    }


    /**
     * @inheritDoc
     */
    public function isPrimaryKey() : bool
    {
        return $this->tableMeta->isPrimaryKey($this->getName());
    }


    /**
     * @inheritDoc
     */
    public function isUnique() : bool
    {
        return $this->tableMeta->isUnique($this->getName());
    }


    /**
     * @inheritDoc
     */
    public function isAutoIncrement() : bool
    {
        if ($record['is_identity'] = 'YES') {
            $identityGeneration = $record['identity_generation'];
            if ($identityGeneration === 'BY DEFAULT' || $identityGeneration === 'ALWAYS') {
                return true;
            }
        }

        $columnDefault = $record['column_default'];
        if ($columnDefault !== null) {
            if (str_starts_with($columnDefault, 'nextval(')) {
                return true;
            }
        }

        return false;
    }


    /**
     * Normalize column name
     * @param string $name
     * @return string
     */
    public static function normalizeName(string $name) : string
    {
        return strtolower($name);
    }
}