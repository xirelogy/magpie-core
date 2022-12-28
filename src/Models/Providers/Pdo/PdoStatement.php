<?php

namespace Magpie\Models\Providers\Pdo;

use Exception;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\StatementLogListenable;
use Magpie\Models\Exceptions\ModelOperationFailedException;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelSafetyException;
use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoModelOperationFailedException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoModelReadException;
use Magpie\Models\Providers\Pdo\Exceptions\PdoModelWriteException;
use Magpie\Models\RawStatement;
use Magpie\Models\Statement;
use PDO;
use PDOException as PhpPdoException;
use PDOStatement as PhpPdoStatement;

/**
 * Statements for PDO based connections
 */
abstract class PdoStatement extends Statement
{
    /**
     * @var PhpPdoStatement Underlying state
     */
    protected PhpPdoStatement $pdoStmt;
    /**
     * @var StatementLogListenable Listener for statement log
     */
    protected StatementLogListenable $statementLogListener;
    /**
     * @var array Bound values
     */
    protected array $boundValues = [];
    /**
     * @var bool If statement executed
     */
    protected bool $isExecuted = false;


    /**
     * Constructor
     * @param PhpPdoStatement $pdoStmt
     * @param StatementLogListenable $statementLogListener
     */
    protected function __construct(PhpPdoStatement $pdoStmt, StatementLogListenable $statementLogListener)
    {
        $this->pdoStmt = $pdoStmt;
        $this->statementLogListener = $statementLogListener;
    }


    /**
     * @inheritDoc
     */
    public function bind(array $values) : void
    {
        try {
            $this->boundValues = [...$values];

            $i = 1;
            foreach ($values as $value) {
                $type = $this->getBindType($value);
                $this->pdoStmt->bindValue($i, $value, $type);
                ++$i;
            }
        } catch (PhpPdoException $ex) {
            throw new PdoModelOperationFailedException($ex);
        } catch (Exception $ex) {
            throw new ModelOperationFailedException(previous: $ex);
        }
    }


    /**
     * Get bind type
     * @param mixed $value
     * @return int
     */
    protected function getBindType(mixed $value) : int
    {
        if ($value === null) return PDO::PARAM_NULL;

        if (is_integer($value)) return PDO::PARAM_INT;

        return PDO::PARAM_STR;
    }


    /**
     * @inheritDoc
     */
    public function exportRaw() : RawStatement
    {
        return new RawStatement($this->pdoStmt->queryString, $this->boundValues);
    }


    /**
     * @inheritDoc
     */
    public function execute() : void
    {
        $this->executeStatement(false);
    }


    /**
     * @inheritDoc
     */
    public function query() : iterable
    {
        $this->executeStatement(true);

        for (;;) {
            $row = $this->pdoStmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) return;

            yield $row;
        }
    }


    /**
     * Execute the statement
     * @param bool $isRead
     * @return void
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    protected function executeStatement(bool $isRead) : void
    {
        if ($this->isExecuted) throw new InvalidStateException();

        $this->statementLogListener->logStatement($this->exportRaw());

        try {
            if (!$this->pdoStmt->execute()) throw new ModelOperationFailedException();
            $this->isExecuted = true;
        } catch (ModelSafetyException $ex) {
            throw new $ex;
        } catch (PhpPdoException $ex) {
            if ($isRead) {
                throw new PdoModelReadException($ex);
            } else {
                throw new PdoModelWriteException($ex);
            }
        } catch (Exception $ex) {
            throw new ModelOperationFailedException(previous: $ex);
        }
    }
}