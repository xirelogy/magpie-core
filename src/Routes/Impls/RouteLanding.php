<?php

namespace Magpie\Routes\Impls;

use Magpie\General\Sugars\Excepts;
use Magpie\Routes\Concepts\RouteHandleable;
use Magpie\Routes\Handlers\ControllerMethodRouteHandler;
use Magpie\System\Concepts\SourceCacheTranslatable;
use Stringable;

/**
 * Route landing
 * @internal
 */
class RouteLanding implements SourceCacheTranslatable, Stringable
{
    /**
     * @var string Type class
     */
    public readonly string $typeClass;
    /**
     * @var array<string, mixed> Arguments
     */
    public readonly array $arguments;
    /**
     * @var RouteMiddlewareCollection Middleware collection
     */
    public readonly RouteMiddlewareCollection $middlewares;
    /**
     * @var array<string> Argument names
     */
    public array $argumentNames = [];


    /**
     * Constructor
     * @param string $typeClass
     * @param array<string, mixed> $arguments
     * @param RouteMiddlewareCollection $middlewares
     */
    public function __construct(string $typeClass, array $arguments, RouteMiddlewareCollection $middlewares)
    {
        $this->typeClass = $typeClass;
        $this->arguments = $arguments;
        $this->middlewares = $middlewares;
    }


    /**
     * Create corresponding handler
     * @return RouteHandleable|null
     */
    public function createHandler() : ?RouteHandleable
    {
        return match ($this->typeClass) {
            ControllerMethodRouteHandler::TYPECLASS => static::createControllerMethodRouteHandler($this->arguments),
            default => null,
        };
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return match ($this->typeClass) {
            ControllerMethodRouteHandler::TYPECLASS => static::formatControllerMethodRouteHandler($this->arguments),
            default => '?',
        };
    }


    /**
     * @inheritDoc
     */
    public function sourceCacheExport() : array
    {
        return [
            'typeClass' => $this->typeClass,
            'arguments' => $this->arguments,
            'middlewares' => $this->middlewares->sourceCacheExport(),
            'argumentNames' => [...$this->argumentNames],
        ];
    }


    /**
     * Create route handler for controller-method
     * @param array $arguments
     * @return RouteHandleable|null
     */
    protected static function createControllerMethodRouteHandler(array $arguments) : ?RouteHandleable
    {
        return Excepts::noThrow(function() use($arguments) {
            $controllerClassName = $arguments['class'] ?? null;
            $methodName = $arguments['method'] ?? null;
            if (is_empty_string($controllerClassName) || is_empty_string($methodName)) return null;
            return new ControllerMethodRouteHandler($controllerClassName, $methodName);
        });
    }


    /**
     * Format route handler for controller-method
     * @param array $arguments
     * @return string
     */
    protected static function formatControllerMethodRouteHandler(array $arguments) : string
    {
        $errorReturn = '<err>';

        return Excepts::noThrow(function() use($arguments, $errorReturn) {
            $controllerClassName = $arguments['class'] ?? null;
            $methodName = $arguments['method'] ?? null;
            if (is_empty_string($controllerClassName) || is_empty_string($methodName)) return $errorReturn;
            return "$controllerClassName::$methodName";
        }, $errorReturn);
    }


    /**
     * @inheritDoc
     */
    public static function sourceCacheImport(array $data) : static
    {
        $typeClass = $data['typeClass'];
        $arguments = $data['arguments'];
        $middlewares = RouteMiddlewareCollection::sourceCacheImport($data['middlewares']);

        $ret = new static($typeClass, $arguments, $middlewares);
        $ret->argumentNames = $data['argumentNames'];

        return $ret;
    }
}