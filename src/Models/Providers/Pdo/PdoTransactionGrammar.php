<?php

namespace Magpie\Models\Providers\Pdo;

/**
 * Language grammar for transactions
 */
class PdoTransactionGrammar
{
    /**
     * @var string SQL command to begin transaction
     */
    public readonly string $beginTransactionSql;
    /**
     * @var string SQL command to commit transaction
     */
    public readonly string $commitSql;
    /**
     * @var string SQL command to rollback transaction
     */
    public readonly string $rollbackSql;


    /**
     * Constructor
     * @param string $beginTransactionSql
     * @param string $commitSql
     * @param string $rollbackSql
     */
    public function __construct(string $beginTransactionSql, string $commitSql, string $rollbackSql)
    {
        $this->beginTransactionSql = $beginTransactionSql;
        $this->commitSql = $commitSql;
        $this->rollbackSql = $rollbackSql;
    }
}