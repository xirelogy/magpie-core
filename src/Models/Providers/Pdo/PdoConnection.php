<?php

namespace Magpie\Models\Providers\Pdo;

use Exception;
use Magpie\Models\Concepts\DirectTransactionable;
use Magpie\Models\Concepts\StatementLogListenable;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Exceptions\ModelOperationFailedException;
use Magpie\Models\Exceptions\ModelSafetyException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoModelConnectionFailedException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoModelOperationFailedException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoPrepareStatementFailedException;
use Magpie\Models\RawStatement;
use Magpie\Models\StatementOptions;
use PDO;
use PDOException as PhpPdoException;
use PDOStatement as PhpPdoStatement;

/**
 * Database connection based on PDO (PHP Data Object) provider
 */
abstract class PdoConnection extends Connection
{
    /**
     * @var PDO Associated PDO object
     */
    protected PDO $pdo;


    /**
     * Constructor
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     * @throws ModelConnectionFailedException
     */
    protected function __construct(string $dsn, ?string $username, ?string $password, array $options = [])
    {
        parent::__construct();

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PhpPdoException $ex) {
            throw new PdoModelConnectionFailedException($ex);
        } catch (Exception $ex) {
            throw new ModelConnectionFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function prepare(string $sql, ?StatementOptions $options = null) : PdoStatement
    {
        $statement = $this->safePrepare($sql, $options);
        $listener = $this->getStatementLogListener();

        return new class($statement, $listener) extends PdoStatement {
            /**
             * Constructor
             * @param PhpPdoStatement $pdoStmt
             * @param StatementLogListenable $statementLogListener
             */
            public function __construct(PhpPdoStatement $pdoStmt, StatementLogListenable $statementLogListener)
            {
                parent::__construct($pdoStmt, $statementLogListener);
            }
        };
    }


    /**
     * @param string $sql
     * @param StatementOptions|null $options
     * @return PhpPdoStatement
     * @throws ModelSafetyException
     */
    private function safePrepare(string $sql, ?StatementOptions $options) : PhpPdoStatement
    {
        try {
            $outOptions = $this->translateStatementOptions($options);

            $statement = $this->pdo->prepare($sql, $outOptions);
            if ($statement === false) throw new ModelOperationFailedException();

            return $statement;
        } catch (PhpPdoException $ex) {
            throw new PdoPrepareStatementFailedException($ex, $sql);
        }
    }


    /**
     * @inheritDoc
     */
    public function lastInsertId(?string $name = null) : string|int|null
    {
        try {
            $ret = $this->pdo->lastInsertId($name);
        } catch (PhpPdoException $ex) {
            throw new PdoModelOperationFailedException($ex);
        }

        if ($ret === false) return null;
        if ($ret === '') return null;

        if (is_numeric($ret)) return intval($ret);

        return $ret;
    }


    /**
     * Get transaction grammar
     * @return PdoTransactionGrammar
     */
    protected abstract function getPdoTransactionGrammar() : PdoTransactionGrammar;


    /**
     * @inheritDoc
     */
    public function getDirectTransaction() : DirectTransactionable
    {
        $listener = $this->getStatementLogListener();
        $grammar = $this->getPdoTransactionGrammar();

        return new class($this->pdo, $listener, $grammar) implements DirectTransactionable
        {
            /**
             * @var PDO Associated PDO object
             */
            protected readonly PDO $pdo;
            /**
             * @var StatementLogListenable Listener to statement log
             */
            protected StatementLogListenable $statementLogListener;
            /**
             * @var PdoTransactionGrammar Grammar
             */
            protected readonly PdoTransactionGrammar $grammar;


            /**
             * Constructor
             * @param PDO $pdo
             * @param StatementLogListenable $statementLogListener
             * @param PdoTransactionGrammar $grammar
             */
            public function __construct(PDO $pdo, StatementLogListenable $statementLogListener, PdoTransactionGrammar $grammar)
            {
                $this->pdo = $pdo;
                $this->statementLogListener = $statementLogListener;
                $this->grammar = $grammar;
            }


            /**
             * @inheritDoc
             */
            public function beginTransaction() : void
            {
                $this->logStatement($this->grammar->beginTransactionSql);
                $this->pdo->beginTransaction();
            }


            /**
             * @inheritDoc
             */
            public function commit() : void
            {
                $this->logStatement($this->grammar->commitSql);
                $this->pdo->commit();
            }


            /**
             * @inheritDoc
             */
            public function rollback() : void
            {
                $this->logStatement($this->grammar->rollbackSql);
                $this->pdo->rollBack();
            }


            /**
             * Log a simple SQL statement
             * @param string $sql
             * @return void
             */
            protected function logStatement(string $sql) : void
            {
                $this->statementLogListener->logStatement(new RawStatement($sql));
            }
        };
    }


    /**
     * Accept and translate statement options
     * @param StatementOptions|null $options
     * @return array
     */
    protected function translateStatementOptions(?StatementOptions $options) : array
    {
        _used($options);

        return [];
    }


    /**
     * Quote and escape string
     * @param string $value
     * @return string
     */
    public function quoteString(string $value) : string
    {
        return $this->pdo->quote($value);
    }


    /**
     * @inheritDoc
     */
    public abstract function getQueryGrammar() : PdoQueryGrammar;
}