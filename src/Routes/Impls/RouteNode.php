<?php

namespace Magpie\Routes\Impls;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\InvalidStateException;
use Magpie\System\Concepts\SourceCacheTranslatable;

/**
 * Routing node
 * @internal
 */
class RouteNode implements SourceCacheTranslatable
{
    /**
     * @var string Text of the node, with special cases (empty string as landing node, slash string as variable node)
     */
    public readonly string $text;
    /**
     * @var array<string, RouteNode> Sub-nodes
     */
    public array $subNodes = [];
    /**
     * @var array<string, RouteLanding> Method to landing points map
     */
    public array $methods = [];


    /**
     * Constructor
     * @param string $text
     */
    protected function __construct(string $text)
    {
        $this->text = $text;
    }


    /**
     * If this is a landing node
     * @return bool
     */
    public function isLanding() : bool
    {
        return $this->text === '';
    }


    /**
     * Merge in route
     * @param array<string> $pathSections
     * @param array<string, mixed> $reflectedVariables
     * @param array<string> $routeVariableNames
     * @param array<string> $requestMethods
     * @param RouteLanding $landing
     * @return void
     * @throws InvalidDataFormatException
     * @throws InvalidStateException
     */
    public function mergeRoute(array $pathSections, array $reflectedVariables, array $routeVariableNames, array $requestMethods, RouteLanding $landing) : void
    {
        $currentSection = array_shift($pathSections);
        if ($currentSection === null) return;

        $currentSection = static::parseNode($currentSection, $reflectedVariables, $currentVariable);
        if ($currentVariable !== null) $routeVariableNames[] = $currentVariable;

        $node = $this->findSubNode($currentSection);

        if (!$node->isLanding()) {
            $node->mergeRoute($pathSections, $reflectedVariables, $routeVariableNames, $requestMethods, $landing);
        } else {
            $node->addLanding($routeVariableNames, $requestMethods, $landing);
        }
    }


    /**
     * Find or create sub node
     * @param string $name
     * @return RouteNode
     */
    protected function findSubNode(string $name) : RouteNode
    {
        if (!array_key_exists($name, $this->subNodes)) {
            $this->subNodes[$name] = new static($name);
        }

        return $this->subNodes[$name];
    }


    /**
     * Add landing points
     * @param array<string> $routeArgumentNames
     * @param array<string> $requestMethods
     * @param RouteLanding $landing
     * @return void
     * @throws InvalidStateException
     */
    protected function addLanding(array $routeArgumentNames, array $requestMethods, RouteLanding $landing) : void
    {
        if (!$this->isLanding()) throw new InvalidStateException();

        $landing->argumentNames = $routeArgumentNames;

        foreach ($requestMethods as $requestMethod) {
            $this->methods[$requestMethod] = $landing;
        }
    }


    /**
     * Land a route
     * @param array<string> $pathSections
     * @param array<string> $routeArguments
     * @return array<string, RouteLanding>|null
     */
    public function landRoute(array $pathSections, array &$routeArguments) : ?array
    {
        $currentSection = array_shift($pathSections);
        if ($currentSection === null) return null;

        $node = $this->matchSubNode($currentSection, $routeArguments);
        if ($node === null) return null;

        if ($currentSection === '') {
            // May attempt to land
            return $node->isLanding() ? $node->methods : null;
        } else {
            // Otherwise, defer
            return $node->landRoute($pathSections, $routeArguments);
        }
    }


    /**
     * @inheritDoc
     */
    public function sourceCacheExport() : array
    {
        $exportedSubNodes = [];
        foreach ($this->subNodes as $key => $subNode) {
            $exportedSubNodes[$key] = $subNode->sourceCacheExport();
        }

        $exportedMethods = [];
        foreach ($this->methods as $key => $method) {
            $exportedMethods[$key] = $method->sourceCacheExport();
        }

        return [
            'text' => $this->text,
            'subNodes' => $exportedSubNodes,
            'methods' => $exportedMethods,
        ];
    }


    /**
     * Try to match a sub-node for routing
     * @param string $pathSection
     * @param array<string> $routeArguments
     * @return RouteNode|null
     */
    protected function matchSubNode(string $pathSection, array &$routeArguments) : ?RouteNode
    {
        if (array_key_exists($pathSection, $this->subNodes)) {
            return $this->subNodes[$pathSection];
        }

        if (array_key_exists('/', $this->subNodes)) {
            $routeArguments[] = $pathSection;
            return $this->subNodes['/'];
        }

        return null;
    }


        /**
     * Create a reference root node
     * @return static
     */
    public static function createRootNode() : static
    {
        return new static('.');
    }


    /**
     * Parse node string
     * @param string $text
     * @param array<string, mixed> $reflectedVariables
     * @param string|null $argumentName
     * @return string
     * @throws InvalidDataFormatException
     */
    protected static function parseNode(string $text, array $reflectedVariables, ?string &$argumentName) : string
    {
        $argumentName = null;

        $startBracePos = strpos($text, '{');
        $endBracePos = strpos($text, '}');

        if ($startBracePos === false && $endBracePos === false) {
            // Expecting normal text node
            return $text;
        } else if ($startBracePos !== false && $endBracePos !== false) {
            // Expecting variable node
            if ($startBracePos !== 0 || $endBracePos !== (strlen($text) - 1)) {
                throw new InvalidDataFormatException(_l('Variable cannot be mixed with text'));
            }

            $argumentName = substr($text, 1, -1);

            // The '@' prefix can be replaced with reflection variables
            if (str_starts_with($argumentName, '@')) {
                $targetVariableName = substr($argumentName, 1);

                $hasOptionalSuffix = false;
                if (str_ends_with($targetVariableName, '?')) {
                    $hasOptionalSuffix = true;
                    $targetVariableName = substr($targetVariableName, 0, -1);
                }

                if (array_key_exists($targetVariableName, $reflectedVariables)) {
                    $argumentName = $reflectedVariables[$targetVariableName];
                    if ($hasOptionalSuffix) $argumentName .= '?';
                }
            }

            // Support for optional route variable
            if (str_ends_with($argumentName, '?')) {
                $argumentName = substr($argumentName, 0, -1);
                return '/?';
            }

            return '/';
        } else {
            // No matching brace
            throw new InvalidDataFormatException(_l('Brace must be a matching pair'));
        }
    }


    /**
     * @inheritDoc
     */
    public static function sourceCacheImport(array $data) : static
    {
        $text = $data['text'];

        $subNodes = [];
        foreach ($data['subNodes'] as $key => $subNode) {
            $subNodes[$key] = RouteNode::sourceCacheImport($subNode);
        }

        $methods = [];
        foreach ($data['methods'] as $key => $method) {
            $methods[$key] = RouteLanding::sourceCacheImport($method);
        }

        $ret = new static($text);
        $ret->subNodes = $subNodes;
        $ret->methods = $methods;
        return $ret;
    }
}