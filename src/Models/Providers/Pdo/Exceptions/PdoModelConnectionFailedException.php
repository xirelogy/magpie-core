<?php

namespace Magpie\Models\Providers\Pdo\Exceptions;

use Magpie\Models\Exceptions\ModelConnectionFailedException;
use Magpie\Models\Providers\Pdo\PdoError;
use Magpie\Models\Providers\Pdo\Traits\CommonPdoExceptionMessage;
use PDOException;

/**
 * Exception due to model connection failure using PDO driver
 */
class PdoModelConnectionFailedException extends ModelConnectionFailedException
{
    use CommonPdoExceptionMessage;


    /**
     * @var PdoError PDO specific error
     */
    public readonly PdoError $error;


    /**
     * Constructor
     * @param PDOException $ex
     */
    public function __construct(PDOException $ex)
    {
        $this->error = PdoError::_from($ex);

        $message = static::formatMessage($this->error);
        parent::__construct($message, $ex);
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
            _l('Failed connecting to PDO model provider: {{0}}'),
            _l('Failed connecting to PDO model provider'),
        );
    }
}