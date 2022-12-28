<?php

namespace Magpie\Models\Providers\Mysql;

use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Providers\Mysql\Impls\MysqlTableCreator;
use Magpie\Models\Providers\Mysql\Impls\MysqlTableEditor;
use Magpie\Models\Providers\Mysql\Impls\MysqlTableSchemaAtDatabase;
use Magpie\Models\Providers\Pdo\PdoConnection;
use Magpie\Models\Providers\Pdo\PdoTransactionGrammar;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Schemas\TableSchemaAtDatabase;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;
use PDO;

/**
 * MySQL specific connection
 */
#[FactoryTypeClass(MysqlConnection::TYPECLASS, Connection::class)]
class MysqlConnection extends PdoConnection
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'mysql';

    /**
     * @var MysqlConnectionConfig Associated configuration
     */
    protected MysqlConnectionConfig $config;


    /**
     * Constructor
     * @param MysqlConnectionConfig $config
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     * @throws ModelConnectionFailedException
     */
    protected function __construct(MysqlConnectionConfig $config, string $dsn, ?string $username, ?string $password, array $options = [])
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
     * Current default database
     * @return string|null
     */
    public function getDatabase() : ?string
    {
        return $this->config->database;
    }


    /**
     * @inheritDoc
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    public function getTableSchemaAtDatabase(string $tableName) : ?TableSchemaAtDatabase
    {
        $database = $this->getDatabase();
        if ($database === null) return null;

        $sql = 'SELECT * FROM information_schema.tables WHERE `table_schema` = ? AND `table_name` = ? AND `table_type` = ?';

        $command = $this->prepare($sql);
        $command->bind([
            $database,
            $tableName,
            'BASE TABLE',
        ]);

        $records = $command->query();
        foreach ($records as $record) {
            return new MysqlTableSchemaAtDatabase($this, $record);
        }

        return null;
    }


    /**
     * @inheritDoc
     */
    public function prepareTableCreator(string $tableName) : TableCreator
    {
        return new MysqlTableCreator($this, $tableName);
    }


    /**
     * Prepare a table editor
     * @param string $tableName
     * @return TableEditor
     */
    public function prepareTableEditor(string $tableName) : TableEditor
    {
        return new MysqlTableEditor($this, $tableName);
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
    public function getQueryGrammar() : MysqlQueryGrammar
    {
        return new MysqlQueryGrammar();
    }


    /**
     * @inheritDoc
     */
    protected static function onInitialize(ConnectionConfig $config) : static
    {
        if (!$config instanceof MysqlConnectionConfig) throw new NotOfTypeException($config, MysqlConnectionConfig::class);

        $dsn = 'mysql:';
        $dsn .= 'host=' . $config->hostname;
        if ($config->port !== null) $dsn .= ';port=' . $config->port;
        if ($config->database !== null) $dsn .= ';dbname=' . $config->database;
        if ($config->charset !== null) $dsn .= ';charset=' . $config->charset;

        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = \'+00:00\'',
        ];

        return new static($config, $dsn, $config->username, $config->password, $options);
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