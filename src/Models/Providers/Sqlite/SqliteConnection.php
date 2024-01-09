<?php

namespace Magpie\Models\Providers\Sqlite;

use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Providers\Pdo\PdoConnection;
use Magpie\Models\Providers\Pdo\PdoTransactionGrammar;
use Magpie\Models\Providers\Sqlite\Impls\SqliteTableCreator;
use Magpie\Models\Providers\Sqlite\Impls\SqliteTableEditor;
use Magpie\Models\Providers\Sqlite\Impls\SqliteTableSchemaAtDatabase;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Schemas\TableSchemaAtDatabase;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * SQLite specific connection
 */
#[FactoryTypeClass(SqliteConnection::TYPECLASS, Connection::class)]
class SqliteConnection extends PdoConnection
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'sqlite';

    /**
     * @var SqliteConnectionConfig Associated configuration
     */
    protected SqliteConnectionConfig $config;


    /**
     * Constructor
     * @param SqliteConnectionConfig $config
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     * @throws ModelConnectionFailedException
     */
    protected function __construct(SqliteConnectionConfig $config, string $dsn, ?string $username, ?string $password, array $options = [])
    {
        parent::__construct($dsn, $username, $password, $options);

        $this->config = $config;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function getTableSchemaAtDatabase(string $tableName) : ?TableSchemaAtDatabase
    {
        $masterSql = "SELECT * FROM sqlite_master WHERE type = ? AND name = ?";

        $masterCommand = $this->prepare($masterSql);
        $masterCommand->bind([
            'table',
            $tableName,
        ]);

        $masterRecords = $masterCommand->query();
        foreach ($masterRecords as $masterRecord) {
            $pragmaSql = "PRAGMA TABLE_INFO ($tableName)";
            $pragmaCommand = $this->prepare($pragmaSql);
            $pragmaRecords = $pragmaCommand->query();

            return new SqliteTableSchemaAtDatabase($this, $masterRecord, $pragmaRecords);
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public function prepareTableCreator(string $tableName, iterable $columns) : TableCreator
    {
        return new SqliteTableCreator($this, $tableName);
    }


    /**
     * @inheritDoc
     */
    public function prepareTableEditor(string $tableName, iterable $columns) : TableEditor
    {
        return new SqliteTableEditor($this, $tableName, $columns);
    }


    /**
     * @inheritDoc
     */
    protected function getPdoTransactionGrammar() : PdoTransactionGrammar
    {
        return new PdoTransactionGrammar(
            'BEGIN TRANSACTION',
            'COMMIT',
            'ROLLBACK',
        );
    }


    /**
     * @inheritDoc
     */
    public function getQueryGrammar() : SqliteQueryGrammar
    {
        return new SqliteQueryGrammar();
    }


    /**
     * @inheritDoc
     */
    protected static function onInitialize(ConnectionConfig $config) : static
    {
        if (!$config instanceof SqliteConnectionConfig) throw new NotOfTypeException($config, SqliteConnectionConfig::class);

        $dsn = 'sqlite:';
        $dsn .= $config->path;

        return new static($config, $dsn, null, null, []);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar->provides(Connection::class);
        return true;
    }


    /**
     * @inheritDoc
     */
    protected static function onSystemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
    }
}