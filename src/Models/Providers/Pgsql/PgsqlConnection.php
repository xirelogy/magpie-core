<?php

namespace Magpie\Models\Providers\Pgsql;

use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Casts\BooleanAttributeCast;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Providers\Pdo\PdoConnection;
use Magpie\Models\Providers\Pdo\PdoTransactionGrammar;
use Magpie\Models\Providers\Pgsql\Impls\PgsqlTableCreator;
use Magpie\Models\Providers\Pgsql\Impls\PgsqlTableEditor;
use Magpie\Models\Providers\Pgsql\Impls\PgsqlTableSchemaAtDatabase;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Schemas\TableSchemaAtDatabase;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * PostgreSQL specific connection
 */
#[FactoryTypeClass(PgsqlConnection::TYPECLASS, Connection::class)]
class PgsqlConnection extends PdoConnection
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'pgsql';

    /**
     * @var PgsqlConnectionConfig Associated configuration
     */
    protected PgsqlConnectionConfig $config;


    /**
     * Constructor
     * @param PgsqlConnectionConfig $config
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     * @throws ModelConnectionFailedException
     */
    protected function __construct(PgsqlConnectionConfig $config, string $dsn, ?string $username, ?string $password, array $options = [])
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
     * Current database schema (in PostgreSQL context)
     * @return string
     */
    public function getSchema() : string
    {
        return $this->config->schema;
    }


    /**
     * @inheritDoc
     */
    public function getActualCastClass(string $castClassName) : string
    {
        if ($castClassName === BooleanAttributeCast::class) return PgsqlBooleanAttributeCast::class;

        return parent::getActualCastClass($castClassName);
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function getTableSchemaAtDatabase(string $tableName) : ?TableSchemaAtDatabase
    {
        $schema = $this->getSchema();

        $sql = 'SELECT * FROM "information_schema"."tables" WHERE "table_schema" = ? AND "table_name" = ? AND "table_type" = ?';

        $command = $this->prepare($sql);
        $command->bind([
            $schema,
            $tableName,
            'BASE TABLE',
        ]);

        $records = $command->query();
        foreach ($records as $record) {
            return new PgsqlTableSchemaAtDatabase($this, $record);
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public function prepareTableCreator(string $tableName, iterable $columns) : TableCreator
    {
        return new PgsqlTableCreator($this, $tableName);
    }


    /**
     * @inheritDoc
     */
    public function prepareTableEditor(string $tableName, iterable $columns) : TableEditor
    {
        return new PgsqlTableEditor($this, $tableName, $columns);
    }


    /**
     * @inheritDoc
     */
    protected function getPdoTransactionGrammar() : PdoTransactionGrammar
    {
        return new PdoTransactionGrammar(
            'START TRANSACTION',
            'COMMIT',
            'ROLLBACK',
        );
    }


    /**
     * @inheritDoc
     */
    public function getQueryGrammar() : PgsqlQueryGrammar
    {
        return new PgsqlQueryGrammar();
    }


    /**
     * @inheritDoc
     */
    protected static function onInitialize(ConnectionConfig $config) : static
    {
        if (!$config instanceof PgsqlConnectionConfig) throw new NotOfTypeException($config, PgsqlConnectionConfig::class);

        $dsn = 'pgsql:';
        $dsn .= 'host=' . $config->hostname;
        if ($config->port !== null) $dsn .= ';port=' . $config->port;
        if ($config->database !== null) $dsn .= ';dbname=' . $config->database;
        if ($config->charset !== null) $dsn .= ';options=' . Quote::single('--client_encoding=' . $config->charset);

        $ret = new static($config, $dsn, $config->username, $config->password, []);

        if ($config->schema != 'public') {
            $ret->pdo->exec('SET search_path TO ' . Quote::double($config->schema) . ', ' . Quote::double('public'));
        }
        $ret->pdo->exec('SET TIMEZONE TO ' . Quote::single('UTC'));

        return $ret;
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