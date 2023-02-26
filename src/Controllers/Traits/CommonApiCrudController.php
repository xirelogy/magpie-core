<?php

/** @noinspection PhpUnused */

namespace Magpie\Controllers\Traits;

use Exception;
use Magpie\Codecs\ParserHosts\ParserHost;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Controllers\Strategies\ApiCrudContext;
use Magpie\Controllers\Strategies\ApiCrudContextAndState;
use Magpie\Controllers\Strategies\ApiCrudNames;
use Magpie\Controllers\Strategies\ApiCrudPurpose;
use Magpie\Controllers\Strategies\ApiCrudState;
use Magpie\Exceptions\CrudNotCreatableException;
use Magpie\Exceptions\CrudNotDeletableException;
use Magpie\Exceptions\CrudNotEditableException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Deletable;
use Magpie\General\Packs\PackTag;
use Magpie\HttpServer\Request;
use Magpie\Models\Filters\Paginator;
use Magpie\Objects\CommonObject;
use Magpie\Objects\Concepts\ApiCreatable;
use Magpie\Objects\Concepts\ApiEditable;
use Magpie\Routes\Annotations\RouteEntry;
use Magpie\Routes\Annotations\RouteIf;

/**
 * Common CRUD functionalities
 */
trait CommonApiCrudController
{
    /**
     * Get all objects of this kind
     * @param Request $request
     * @return object|null
     * @throws Exception
     */
    #[RouteEntry('/')]
    public final function getRoot(Request $request) : ?object
    {
        $contextAndState = $this->onGetContextAndState($request, ApiCrudPurpose::READ);

        $intercepted = $this->onInterceptGetRoot($request, $contextAndState->context, $contextAndState->crudState);
        if ($intercepted !== null) return $intercepted;

        $paginator = static::crudGetPaginatorFromRequest($request);
        $objects = $this->onListObjects($request, $contextAndState->context, $contextAndState->crudState, $paginator);

        return $this->onResponseGetRoot($request, $contextAndState->crudState, $paginator, $objects);
    }


    /**
     * Intercept getRoot()
     * @param Request $request
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @return object|null
     * @throws Exception
     */
    protected function onInterceptGetRoot(Request $request, ApiCrudContext $context, ApiCrudState $crudState) : ?object
    {
        _used($request, $context, $crudState);

        return null;
    }


    /**
     * Add additional selections to response
     * @param Request $request
     * @param ApiCrudState $crudState
     * @return iterable<string>
     */
    protected function onResponseAddSelections(Request $request, ApiCrudState $crudState) : iterable
    {
        _used($request, $crudState);

        return [];
    }


    /**
     * Provide response for getRoot()
     * @param Request $request
     * @param ApiCrudState $crudState
     * @param Paginator|null $paginator
     * @param iterable<CommonObject> $objects
     * @return object|null
     * @throws Exception
     */
    protected function onResponseGetRoot(Request $request, ApiCrudState $crudState, ?Paginator $paginator, iterable $objects) : ?object
    {
        $ret = obj();
        if ($paginator !== null) $ret->pages = $paginator;

        $retObjects = $objects;
        $retSelections = iter_flatten($this->onResponseAddSelections($request, $crudState), false);
        if (count($retSelections) > 0) {
            $retObjects = PackTag::for($retObjects)->select(...$retSelections);
        }

        $pluralName = static::crudGetNames()->pluralName;
        $ret->{$pluralName} = $retObjects;

        return $ret;
    }


    /**
     * Get particular object of this kind
     * @param Request $request
     * @return object|null
     * @throws Exception
     */
    #[RouteEntry('/{@id}')]
    #[RouteIf('hasGetItem')]
    public final function getItem(Request $request) : ?object
    {
        $contextAndState = $this->onGetContextAndState($request, ApiCrudPurpose::READ);

        $idArgName = $request->routeContext->getRouteVariable('id');
        $object = $request->routeArguments->requires($idArgName, $this->onCreateObjectParser($contextAndState->context, $contextAndState->crudState));

        return $this->onResponseGetItem($request, $contextAndState->crudState, $object);
    }


    /**
     * Provide response for getItem() / similar ones
     * @param Request $request
     * @param ApiCrudState $crudState
     * @param CommonObject $object
     * @return object|null
     */
    protected function onResponseGetItem(Request $request, ApiCrudState $crudState, CommonObject $object) : ?object
    {
        _used($request, $crudState);

        $ret = obj();

        $retObject = PackTag::full($object);
        $retSelections = iter_flatten($this->onResponseAddSelections($request, $crudState), false);
        if (count($retSelections) > 0) $retObject = $retObject->select(...$retSelections);

        $singularName = static::crudGetNames()->singularName;
        $ret->{$singularName} = $retObject;

        return $ret;
    }


    /**
     * Create new object of this kind
     * @param Request $request
     * @return object|null
     * @throws Exception
     */
    #[RouteEntry('/', 'post')]
    #[RouteIf('hasPostRoot')]
    public final function postRoot(Request $request) : ?object
    {
        $parserHost = static::crudGetParserHostFromRequest($request);
        $contextAndState = $this->onGetContextAndState($request, ApiCrudPurpose::CREATE);

        $className = static::crudGetCreatableClassName($contextAndState->context, $contextAndState->crudState, $parserHost);
        if (!is_subclass_of($className, ApiCreatable::class)) throw new CrudNotCreatableException();

        $object = $className::createFromApi($contextAndState->context, $contextAndState->crudState, $parserHost);

        return $this->onResponsePostRoot($request, $contextAndState->crudState, $object);
    }


    /**
     * Provide response for postRoot()
     * @param Request $request
     * @param ApiCrudState $crudState
     * @param CommonObject $object
     * @return object|null
     */
    protected function onResponsePostRoot(Request $request, ApiCrudState $crudState, CommonObject $object) : ?object
    {
        return $this->onResponseGetItem($request, $crudState, $object);
    }


    /**
     * Edit particular object of this kind
     * @param Request $request
     * @return object|null
     * @throws Exception
     */
    #[RouteEntry('/{@id}', 'put')]
    #[RouteIf('hasPutItem')]
    public final function putItem(Request $request) : ?object
    {
        $parserHost = static::crudGetParserHostFromRequest($request);
        $contextAndState = $this->onGetContextAndState($request, ApiCrudPurpose::EDIT);

        $idArgName = $request->routeContext->getRouteVariable('id');
        /** @var CommonObject $object */
        $object = $request->routeArguments->requires($idArgName, $this->onCreateObjectParser($contextAndState->context, $contextAndState->crudState));

        if (!$object instanceof ApiEditable) throw new CrudNotEditableException();
        $object->editFromApi($contextAndState->context, $contextAndState->crudState, $parserHost);

        return $this->onResponsePutItem($request, $contextAndState->crudState, $object);
    }


    /**
     * Provide response for putItem()
     * @param Request $request
     * @param ApiCrudState $crudState
     * @param CommonObject $object
     * @return object|null
     */
    protected function onResponsePutItem(Request $request, ApiCrudState $crudState, CommonObject $object) : ?object
    {
        return $this->onResponseGetItem($request, $crudState, $object);
    }


    /**
     * Delete particular object of this kind
     * @param Request $request
     * @return object|null
     * @throws Exception
     */
    #[RouteEntry('/{@id}', 'delete')]
    #[RouteIf('hasDeleteItem')]
    public final function deleteItem(Request $request) : ?object
    {
        $contextAndState = $this->onGetContextAndState($request, ApiCrudPurpose::DELETE);

        $idArgName = $request->routeContext->getRouteVariable('id');
        $object = $request->routeArguments->requires($idArgName, $this->onCreateObjectParser($contextAndState->context, $contextAndState->crudState));

        if (!$object instanceof Deletable) throw new CrudNotDeletableException();
        $object->delete();

        return $this->onResponseDeleteItem($request, $contextAndState->crudState);
    }


    /**
     * Provide response for deleteItem()
     * @param Request $request
     * @param ApiCrudState $crudState
     * @return object|null
     */
    protected function onResponseDeleteItem(Request $request, ApiCrudState $crudState) : ?object
    {
        _used($request, $crudState);

        return null;
    }


    /**
     * List all objects of this kind
     * @param Request $request
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param Paginator|null $paginator
     * @return iterable<CommonObject>
     * @throws Exception
     */
    protected abstract function onListObjects(Request $request, ApiCrudContext $context, ApiCrudState $crudState, ?Paginator $paginator) : iterable;


    /**
     * Create an object parser
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @return Parser
     */
    protected function onCreateObjectParser(ApiCrudContext $context, ApiCrudState $crudState) : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) use ($context, $crudState) : CommonObject {
            return $this->onParseObject($context, $crudState, $value, $hintName);
        });
    }


    /**
     * Parse object
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param mixed $value
     * @param string|null $hintName
     * @return CommonObject
     * @throws Exception
     */
    protected abstract function onParseObject(ApiCrudContext $context, ApiCrudState $crudState, mixed $value, ?string $hintName) : CommonObject;


    /**
     * Get CRUD context and state
     * @param Request $request
     * @param ApiCrudPurpose $purpose
     * @return ApiCrudContextAndState
     * @throws Exception
     */
    protected abstract function onGetContextAndState(Request $request, ApiCrudPurpose $purpose) : ApiCrudContextAndState;


    /**
     * Get CRUD names
     * @return ApiCrudNames
     */
    protected static abstract function crudGetNames() : ApiCrudNames;


    /**
     * Get creatable class name, for postRoot()
     * @param ApiCrudContext $context
     * @param ApiCrudState $crudState
     * @param ParserHost $parserHost
     * @return class-string<ApiCreatable>
     * @throws SafetyCommonException
     */
    protected static function crudGetCreatableClassName(ApiCrudContext $context, ApiCrudState $crudState, ParserHost $parserHost) : string
    {
        _used($context, $crudState, $parserHost);
        _throwable() ?? throw new NullException();

        return static::crudGetBaseClassName();
    }


    /**
     * Get primary base class name
     * @return class-string<CommonObject>
     */
    protected static abstract function crudGetBaseClassName() : string;


    /**
     * Get input parser host
     * @param Request $request
     * @return ParserHost
     * @throws SafetyCommonException
     */
    protected static abstract function crudGetParserHostFromRequest(Request $request) : ParserHost;


    /**
     * Get list paginator
     * @param Request $request
     * @return Paginator|null
     * @throws SafetyCommonException
     */
    protected static abstract function crudGetPaginatorFromRequest(Request $request) : ?Paginator;
}