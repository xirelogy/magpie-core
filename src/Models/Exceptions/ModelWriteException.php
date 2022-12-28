<?php

namespace Magpie\Models\Exceptions;

use Magpie\Exceptions\PersistenceWriteException;

/**
 * Exception due to failure in database write
 */
abstract class ModelWriteException extends PersistenceWriteException
{

}