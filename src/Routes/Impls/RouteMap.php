<?php

namespace Magpie\Routes\Impls;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Facades\Log;
use Magpie\General\Names\CommonHttpMethod;
use Magpie\Routes\Annotations\RouteEntry;
use Magpie\Routes\Annotations\RouteIf;
use Magpie\Routes\Annotations\RoutePrefix;
use Magpie\Routes\Annotations\RoutePrefixSection;
use Magpie\Routes\Annotations\RouteUseMiddleware;
use Magpie\Routes\Annotations\RouteVariable;
use Magpie\Routes\Annotations\RouteVariableDefault;
use Magpie\Routes\Annotations\RouteVariableSet;
use Magpie\Routes\Handlers\ControllerMethodRouteHandler;
use Magpie\Routes\RouteMiddleware;
use Magpie\System\Concepts\SourceCacheTranslatable;
use Magpie\System\HardCore\AutoloadReflection;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Route map
 * @internal
 */
class RouteMap implements SourceCacheTranslatable
{
    /**
     * @var RouteNode Root node
     */
    protected readonly RouteNode $root;
    /**
     * @var array<class-string, array<string, mixed>> Class route variables
     */
    protected array $routeVariables = [];


    /**
     * Constructor
     * @param RouteNode|null $root
     */
    protected function __construct(?RouteNode $root = null)
    {
        $this->root = $root ?? RouteNode::createRootNode();
    }


    /**
     * Discover from the target controller class and include them in the map
     * @param ReflectionClass $class
     * @param RouteMiddlewareCollection $domainMiddlewares
     * @param string|null $prefix
     * @param string|null $routeGroupId
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     * @throws ReflectionException
     * @throws UnsupportedException
     */
    public function discover(ReflectionClass $class, RouteMiddlewareCollection $domainMiddlewares, ?string $prefix = null, ?string $routeGroupId = null) : void
    {
        $routePrefixes = static::findRoutePrefixesFromAttribute($class);

        // Add all class middlewares
        $controllerMiddlewares = $domainMiddlewares->clone();
        $controllerMiddlewares->mergeIn(static::listRouteUseMiddlewareClassNamesFromAttribute($class));

        $variables = [];

        // RouteVariableDefault population
        foreach (static::listRouteVariableDefaultFromAttribute($class) as $routeVariableDefault) {
            $variables[$routeVariableDefault->name] = $routeVariableDefault->value;
        }

        // Process the route variables calculations
        foreach ($class->getMethods() as $method) {
            $routeVariable = static::findRouteVariableFromAttribute($method);
            if ($routeVariable === null) continue;

            $name = $routeVariable->name;
            $variables[$name] = static::evaluateAsVariable($method);
        }

        // RouteVariableSet is overriding
        foreach (static::listRouteVariableSetFromAttribute($class) as $routeVariableSet) {
            $variables[$routeVariableSet->name] = $routeVariableSet->value;
        }

        // Process the route entries
        foreach ($class->getMethods() as $method) {
            $routeMiddlewares = $controllerMiddlewares->clone();

            $routeEntry = static::findRouteEntryFromAttribute($method);
            if ($routeEntry === null) continue;

            $routeIf = static::findRouteIfFromAttribute($method);
            if ($routeIf !== null) {
                $name = $routeIf->name;

                if (!array_key_exists($name, $variables)) {
                    Log::warning("Expecting variable '$name' but missing, assumed not satisfied");
                    continue;
                }

                $value = $variables[$name];
                if (!is_bool($value)) {
                    Log::warning("Expecting variable '$name' to be a boolean but not, assumed not satisfied");
                    continue;
                }

                if (!$value) continue;
            }

            $routeMiddlewares->mergeIn(static::listRouteUseMiddlewareClassNamesFromAttribute($method));

            $this->addControllerMethodRouteEntry($class, $method, $prefix, $routePrefixes, $routeEntry, $routeMiddlewares, $variables, $routeGroupId);
        }

        $this->routeVariables[$class->name] = $variables;
    }


    /**
     * Try to land a path
     * @param string $requestPath
     * @param array|null $routeArguments
     * @return array<string, RouteLanding>|null
     */
    public function land(string $requestPath, ?array &$routeArguments = null) : ?array
    {
        $routeArguments = [];

        $requestPathSections = static::getPathSections($requestPath);

        return $this->root->landRoute($requestPathSections, $routeArguments);
    }


    /**
     * All routes
     * @param string|null $domain
     * @return iterable<RouteInfo>
     */
    public function all(?string $domain) : iterable
    {
        yield from $this->root->expandRoute($domain, '/');
    }


    /**
     * Obtain the route variables for given class
     * @param string $className
     * @return array<string, mixed>|null
     */
    public function getRouteVariables(string $className) : ?array
    {
        return $this->routeVariables[$className] ?? null;
    }


    /**
     * @inheritDoc
     */
    public function sourceCacheExport() : array
    {
        return [
            'root' => $this->root->sourceCacheExport(),
            'routeVariables' => $this->routeVariables,
        ];
    }


    /**
     * Add a route entry to a specific method in a controller class
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     * @param string|null $globalPrefix
     * @param array<string>|null $routePrefixes
     * @param RouteEntry $routeEntry
     * @param RouteMiddlewareCollection $middlewares
     * @param array<string, mixed> $reflectedVariables
     * @param string|null $routeGroupId
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     */
    protected function addControllerMethodRouteEntry(ReflectionClass $class, ReflectionMethod $method, ?string $globalPrefix, ?array $routePrefixes, RouteEntry $routeEntry, RouteMiddlewareCollection $middlewares, array $reflectedVariables, ?string $routeGroupId) : void
    {
        $requestPath = static::combineRoutes($globalPrefix, $routePrefixes, $routeEntry->path);
        $requestMethods = static::flattenRequestMethods($routeEntry->method);

        $this->addRouteEntry($requestPath, $requestMethods, ControllerMethodRouteHandler::TYPECLASS, [
            'class' => $class->name,
            'method' => $method->name,
        ], $middlewares, $reflectedVariables, $routeGroupId);
    }


    /**
     * Add a route entry
     * @param string $requestPath
     * @param array<string> $requestMethods
     * @param string $handlerTypeClass
     * @param array<string, mixed> $handlerArgs
     * @param RouteMiddlewareCollection $middlewares
     * @param array<string, mixed> $reflectedVariables
     * @param string|null $routeGroupId
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     */
    protected function addRouteEntry(string $requestPath, array $requestMethods, string $handlerTypeClass, array $handlerArgs, RouteMiddlewareCollection $middlewares, array $reflectedVariables, ?string $routeGroupId) : void
    {
        $requestPathSections = static::getPathSections($requestPath);

        $landing = new RouteLanding($handlerTypeClass, $handlerArgs, $middlewares, $routeGroupId);
        $this->root->mergeRoute($requestPathSections, $reflectedVariables, [], $requestMethods, $landing);
    }


    /**
     * Break into path sections
     * @param string $requestPath
     * @return array<string>
     */
    public static function getPathSections(string $requestPath) : array
    {
        // Switch to trailing slash
        if (!str_ends_with($requestPath, '/')) $requestPath = "$requestPath/";
        if (str_starts_with($requestPath, '/')) $requestPath = substr($requestPath, 1);

        // Break into sections
        return explode('/', $requestPath);
    }


    /**
     * Flatten request method specification
     * @param string|array<string>|null $methodSpec
     * @return array
     */
    public static function flattenRequestMethods(string|array|null $methodSpec) : array
    {
        $ret = [];
        foreach (static::expandRequestMethods($methodSpec) as $method) {
            $method = trim(strtoupper($method));    // Normalize
            if (array_key_exists($method, $ret)) continue;

            $ret[$method] = $method;
        }

        return array_values($ret);
    }


    /**
     * Expand request method specification
     * @param string|array<string>|null $methodSpec
     * @return iterable<string>
     */
    protected static function expandRequestMethods(string|array|null $methodSpec) : iterable
    {
        if ($methodSpec === null) {
            yield static::methodStringOf(CommonHttpMethod::GET);
            return;
        }

        if (is_string($methodSpec)) {
            yield static::methodStringOf($methodSpec);
            return;
        }

        foreach ($methodSpec as $method) {
            yield static::methodStringOf($method);
        }
    }


    /**
     * Convert to string
     * @param string $method
     * @return string
     */
    protected static function methodStringOf(string $method) : string
    {
        return strtoupper($method);
    }


    /**
     * Combine multiple paths into a path
     * @param string|array<string>|null ...$paths
     * @return string
     * @noinspection PhpDocSignatureInspection
     */
    public static function combineRoutes(string|array|null ...$paths) : string
    {
        $expandedPaths = function() use ($paths) {
            foreach ($paths as $pathSpec) {
                if ($pathSpec === null) continue;

                if (is_array($pathSpec)) {
                    yield from $pathSpec;
                } else {
                    yield $pathSpec;
                }
            }
        };

        $ret = '';
        foreach ($expandedPaths() as $path) {
            // Remove all double-slash
            for (;;) {
                $doubleSlashPos = strpos($path, '//');
                if ($doubleSlashPos === false) break;
                $path = substr($path, 0, $doubleSlashPos) . substr($path, $doubleSlashPos + 1);
            }

            // Remove all trailing and leading 'slashes'
            while (str_ends_with($path, '/')) {
                $path = substr($path, 0, -1);
            }
            while (str_starts_with($path, '/')) {
                $path = substr($path, 1);
            }

            if (is_empty_string($path)) continue;

            // Then concatenate with prefix slash
            $ret .= "/$path";
        }

        return $ret;
    }


    /**
     * Create route map by discovering from given paths
     * @param iterable<string> $paths
     * @param RouteMiddlewareCollection $middlewares
     * @return static
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     * @throws ReflectionException
     * @throws UnsupportedException
     */
    public static function from(iterable $paths, RouteMiddlewareCollection $middlewares) : static
    {
        $ret = new static();

        $paths = iter_flatten($paths);

        $autoload = AutoloadReflection::instance();
        foreach ($autoload->expandDiscoverySourcesReflection($paths) as $class) {
            $ret->discover($class, $middlewares);
        }

        return $ret;
    }


    /**
     * Extract route prefix from class attributes
     * @param ReflectionClass|false $class
     * @return array<string>
     */
    public static function findRoutePrefixesFromAttribute(ReflectionClass|false $class) : array
    {
        if ($class === false) return [];

        // Override attribute exist for backwards compatibility
        $overrideAttribute = iter_first($class->getAttributes(RoutePrefix::class));
        if ($overrideAttribute !== null) return [ $overrideAttribute->newInstance()->path ];

        // Prefer section concatenation
        $sectionPaths = iter_flatten(static::findRoutePrefixesFromSectionAttributes($class), false);
        if (count($sectionPaths) > 0) return $sectionPaths;

        // Fallback to single prefix
        $routePrefix = static::findRoutePrefixFromAttribute($class);
        if ($routePrefix !== null) return [ $routePrefix->path ];

        return [];
    }


    /**
     * Extract route prefixes from RouteEntrySection class attributes
     * @param ReflectionClass|false $class
     * @return iterable<string>
     */
    private static function findRoutePrefixesFromSectionAttributes(ReflectionClass|false $class) : iterable
    {
        if ($class === false) return;
         yield from static::findRoutePrefixesFromSectionAttributes($class->getParentClass());

         $attribute = iter_first($class->getAttributes(RoutePrefixSection::class));
         if ($attribute !== null) yield $attribute->newInstance()->path;
    }


    /**
     * Extract route prefix from class attributes
     * @param ReflectionClass|false $class
     * @return RoutePrefix|null
     */
    private static function findRoutePrefixFromAttribute(ReflectionClass|false $class) : ?RoutePrefix
    {
        if ($class === false) return null;

        $attribute = iter_first($class->getAttributes(RoutePrefix::class));
        return $attribute?->newInstance() ?? static::findRoutePrefixFromAttribute($class->getParentClass());
    }


    /**
     * Extract route variable default from class attributes
     * @param ReflectionClass|false $class
     * @return iterable<RouteVariableDefault>
     */
    protected static function listRouteVariableDefaultFromAttribute(ReflectionClass|false $class) : iterable
    {
        if ($class === false) return;

        yield from static::listRouteVariableDefaultFromAttribute($class->getParentClass());

        foreach ($class->getAttributes(RouteVariableDefault::class) as $attribute) {
            yield $attribute->newInstance();
        }
    }


    /**
     * Extract route variable set from class attributes
     * @param ReflectionClass|false $class
     * @return iterable<RouteVariableSet>
     */
    protected static function listRouteVariableSetFromAttribute(ReflectionClass|false $class) : iterable
    {
        if ($class === false) return;

        yield from static::listRouteVariableSetFromAttribute($class->getParentClass());

        foreach ($class->getAttributes(RouteVariableSet::class) as $attribute) {
            yield $attribute->newInstance();
        }
    }


    /**
     * Extract route entry from method attributes
     * @param ReflectionMethod $method
     * @return RouteEntry|null
     */
    public static function findRouteEntryFromAttribute(ReflectionMethod $method) : ?RouteEntry
    {
        $attribute = iter_first($method->getAttributes(RouteEntry::class));
        return $attribute?->newInstance() ?? null;
    }


    /**
     * Discover all route variables for given class
     * @return iterable<string, mixed>
     */
    public static function discoverClassRouteVariables(ReflectionClass $class) : iterable
    {
        $variables = [];

        // RouteVariableDefault population
        foreach (static::listRouteVariableDefaultFromAttribute($class) as $routeVariableDefault) {
            $variables[$routeVariableDefault->name] = $routeVariableDefault->value;
        }

        // RouteVariableSet is overriding
        foreach (static::listRouteVariableSetFromAttribute($class) as $routeVariableSet) {
            $variables[$routeVariableSet->name] = $routeVariableSet->value;
        }

        yield from $variables;
    }


    /**
     * Extract route variable from method attributes
     * @param ReflectionMethod $method
     * @return RouteVariable|null
     */
    protected static function findRouteVariableFromAttribute(ReflectionMethod $method) : ?RouteVariable
    {
        $attribute = iter_first($method->getAttributes(RouteVariable::class));
        return $attribute?->newInstance() ?? null;
    }


    /**
     * Extract route if from method attributes
     * @param ReflectionMethod $method
     * @return RouteIf|null
     */
    protected static function findRouteIfFromAttribute(ReflectionMethod $method) : ?RouteIf
    {
        $attribute = iter_first($method->getAttributes(RouteIf::class));
        return $attribute?->newInstance() ?? null;
    }


    /**
     * Extract route use-middleware class names from class or method attributes
     * @param ReflectionClass|ReflectionMethod|false $classOrMethod
     * @return iterable<class-string<RouteMiddleware>>
     */
    protected static function listRouteUseMiddlewareClassNamesFromAttribute(ReflectionClass|ReflectionMethod|false $classOrMethod) : iterable
    {
        if ($classOrMethod === false) return;

        if ($classOrMethod instanceof ReflectionClass) yield from static::listRouteUseMiddlewareClassNamesFromAttribute($classOrMethod->getParentClass());

        foreach ($classOrMethod->getAttributes(RouteUseMiddleware::class) as $attribute) {
            /** @var RouteUseMiddleware $attrInstance */
            $attrInstance = $attribute->newInstance();
            yield $attrInstance->className;
        }
    }


    /**
     * Evaluate the method for result
     * @param ReflectionMethod $method
     * @return mixed
     * @throws UnsupportedException
     * @throws ReflectionException
     */
    private static function evaluateAsVariable(ReflectionMethod $method) : mixed
    {
        if (!$method->isStatic() || !$method->isPublic()) throw new UnsupportedException();

        return $method->invoke(null);
    }


    /**
     * @inheritDoc
     */
    public static function sourceCacheImport(array $data) : static
    {
        $root = RouteNode::sourceCacheImport($data['root']);
        $ret = new static($root);
        $ret->routeVariables = $data['routeVariables'];
        return $ret;
    }
}