<?php

namespace Magpie\Objects\Concepts;

use Magpie\Codecs\ParserHosts\ParserHost;
use Magpie\Controllers\Strategies\ApiCrudContext;
use Magpie\Controllers\Strategies\ApiCrudState;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * Creatable from API request
 */
interface ApiCreatable
{
    /**
     * Create a new object from API request
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param ParserHost $parserHost
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static function createFromApi(ApiCrudContext $context, ApiCrudState $crudState, ParserHost $parserHost) : static;
}