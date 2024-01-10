<?php

namespace Magpie\Models;

use Exception;
use Magpie\Commands\CommandRegistry;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Facades\Random;
use Magpie\General\Concepts\Identifiable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Randoms\RandomCharset;
use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Concepts\DirectTransactionable;
use Magpie\Models\Concepts\StatementLogListenable;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelSafetyException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Impls\ConnectionsCache;
use Magpie\Models\Providers\QueryGrammar;
use Magpie\Models\Schemas\ColumnSchema;
use Magpie\Models\Schemas\DatabaseEdits\TableCreator;
use Magpie\Models\Schemas\DatabaseEdits\TableEditor;
use Magpie\Models\Schemas\TableSchemaAtDatabase;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\BootContext;

/**
 * A database connection
 */
abstract class Connection implements Identifiable, TypeClassable, SystemBootable
{
    /**
     * @var string Unique identity
     */
    protected readonly string $id;
    /**
     * @var array<StatementLogListenable> Listeners for statement log
     */
    protected array $statementLogListeners = [];


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->id = Random::string(16, RandomCharset::LOWER_ALPHANUM);
    }


    /**
     * @inheritDoc
     */
    public function getId() : string
    {
        return $this->id;
    }


    /**
     * Subscribe to statement log
     * @param StatementLogListenable $listener
     * @return void
     */
    public function subscribeStatementLog(StatementLogListenable $listener) : void
    {
        $this->statementLogListeners[] = $listener;
    }


    /**
     * Expose the listener associated with this connection
     * @return StatementLogListenable
     */
    protected function getStatementLogListener() : StatementLogListenable
    {
        return ClosureStatementLogListener::create(function (RawStatement $statement) {
            foreach ($this->statementLogListeners as $statementLogListener) {
                $statementLogListener->logStatement($statement);
            }
        });
    }


    /**
     * Prepare a database query statement
     * @param string $sql
     * @param StatementOptions|null $options
     * @return Statement
     * @throws ModelSafetyException
     */
    public abstract function prepare(string $sql, ?StatementOptions $options = null) : Statement;


    /**
     * The last inserted ID
     * @param string|null $name
     * @return string|int|null
     * @throws ModelSafetyException
     */
    public abstract function lastInsertId(?string $name = null) : string|int|null;


    /**
     * Get table schema at database end
     * @param string $tableName
     * @return TableSchemaAtDatabase|null
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function getTableSchemaAtDatabase(string $tableName) : ?TableSchemaAtDatabase;


    /**
     * Prepare a table creator
     * @param string $tableName
     * @param iterable<ColumnSchema> $columns
     * @return TableCreator
     */
    public abstract function prepareTableCreator(string $tableName, iterable $columns) : TableCreator;


    /**
     * Prepare a table editor
     * @param string $tableName
     * @param iterable<ColumnSchema> $columns
     * @return TableEditor
     */
    public abstract function prepareTableEditor(string $tableName, iterable $columns) : TableEditor;


    /**
     * Create a transaction for this connection
     * @return Transaction
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public final function createTransaction() : Transaction
    {
        return new Transaction($this);
    }


    /**
     * Get direct transaction service interface
     * @return DirectTransactionable
     */
    public abstract function getDirectTransaction() : DirectTransactionable;


    /**
     * Get associated query grammar
     * @return QueryGrammar
     */
    public abstract function getQueryGrammar() : QueryGrammar;


    /**
     * Get connection from given specification
     * @param ConnectionResolvable|string $spec
     * @return static
     * @throws SafetyCommonException
     */
    public final static function from(ConnectionResolvable|string $spec) : static
    {
        if ($spec instanceof ConnectionResolvable) return $spec->resolve();
        return ConnectionsCache::fromName($spec);
    }


    /**
     * Initialize connection using given configuration
     * @param ConnectionConfig $config
     * @return static
     * @throws ModelConnectionFailedException
     * @throws SafetyCommonException
     */
    public final static function initialize(ConnectionConfig $config) : static
    {
        $className = ClassFactory::resolve($config->getTypeClass(), self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::onInitialize($config);
    }


    /**
     * Initialize connection using given configuration
     * @param ConnectionConfig $config
     * @return static
     * @throws ModelConnectionFailedException
     * @throws SafetyCommonException
     */
    protected abstract static function onInitialize(ConnectionConfig $config) : static;


    /**
     * @inheritDoc
     */
    public static final function systemBoot(BootContext $context) : void
    {
        CommandRegistry::includeDirectory(__DIR__ . '/Commands');

        static::onSystemBoot($context);
    }


    /**
     * Specific system boot-up
     * @param BootContext $context Boot up context
     * @return void
     * @throws Exception
     */
    protected static abstract function onSystemBoot(BootContext $context) : void;
}