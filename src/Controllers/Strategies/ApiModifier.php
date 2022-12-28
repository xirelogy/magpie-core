<?php

namespace Magpie\Controllers\Strategies;

use Magpie\Codecs\ParserHosts\ParserHost;

/**
 * Handle for modification (create/edit) from API
 */
abstract class ApiModifier
{
    /**
     * @var ApiCrudContext Associated context
     */
    public ApiCrudContext $context;
    /**
     * @var ApiCrudState Associated state
     */
    public ApiCrudState $crudState;
    /**
     * @var ParserHost Associated parser host
     */
    public readonly ParserHost $parserHost;


    /**
     * Constructor
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param ParserHost $parserHost
     */
    public function __construct(ApiCrudContext $context, ApiCrudState $crudState, ParserHost $parserHost)
    {
        $this->context = $context;
        $this->crudState = $crudState;
        $this->parserHost = $parserHost;
    }
}