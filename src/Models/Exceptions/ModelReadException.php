<?php

namespace Magpie\Models\Exceptions;

use Magpie\Exceptions\PersistenceReadException;

/**
 * Exception due to failure in database read
 */
abstract class ModelReadException extends PersistenceReadException
{

}