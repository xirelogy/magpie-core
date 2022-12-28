<?php

namespace Magpie\Models\Providers\Pdo\Exceptions;

use Magpie\Models\Exceptions\ModelWriteException;
use Magpie\Models\Providers\Pdo\PdoError;
use Magpie\Models\Providers\Pdo\Traits\CommonPdoExceptionMessage;
use PDOException as PhpPdoException;

/**
 * Exception due to error during write (using PDO)
 */
class PdoModelWriteException extends ModelWriteException
{
    use CommonPdoExceptionMessage;


    /**
     * @var PdoError PDO specific error
     */
    public readonly PdoError $error;


    /**
     * Constructor
     * @param PhpPdoException $ex
     */
    public function __construct(PhpPdoException $ex)
    {
        $this->error = PdoError::_from($ex);

        $message = static::formatMessage($this->error);
        parent::__construct($message, previous: $ex);
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
            _l('PDO write error: {{0}}'),
            _l('PDO write error'),
        );
    }
}