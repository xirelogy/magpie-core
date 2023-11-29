<?php

namespace Magpie\Models\Providers\Pdo\Exceptions;

use Magpie\Models\Exceptions\ModelOperationFailedException;
use Magpie\Models\Providers\Pdo\PdoError;
use Magpie\Models\Providers\Pdo\Traits\CommonPdoExceptionMessage;
use PDOException as PhpPdoException;

/**
 * Exception due to failure preparing PDO statements
 */
class PdoPrepareStatementFailedException extends ModelOperationFailedException
{
    use CommonPdoExceptionMessage;


    /**
     * @var PdoError PDO specific error
     */
    public readonly PdoError $error;
    /**
     * @var string|null Associated SQL statement
     */
    public readonly ?string $sql;


    /**
     * Constructor
     * @param PhpPdoException $ex
     * @param string|null $sql
     */
    public function __construct(PhpPdoException $ex, ?string $sql = null)
    {
        $this->error = PdoError::_from($ex);

        $message = static::formatMessage($this->error);
        parent::__construct($message, $ex);

        $this->sql = $sql;
    }


    /**
     * Format message
     * @param PdoError $error
     * @return string
     */
    protected static function formatMessage(PdoError $error) : string
    {
        return static::formatMessageUsing(
            $error,
            _l('Failed preparing PDO statement: {{0}}'),
            _l('Failed preparing PDO statement'),
        );
    }
}