<?php

namespace Magpie\Objects\Concepts;

use Magpie\Codecs\ParserHosts\ParserHost;
use Magpie\Controllers\Strategies\ApiCrudContext;
use Magpie\Controllers\Strategies\ApiCrudState;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;

/**
 * Editable from API request
 */
interface ApiEditable
{
    /**
     * Edit (update) current object from API request
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param ParserHost $parserHost
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function editFromApi(ApiCrudContext $context, ApiCrudState $crudState, ParserHost $parserHost) : void;
}