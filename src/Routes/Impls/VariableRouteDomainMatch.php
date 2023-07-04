<?php

namespace Magpie\Routes\Impls;

use Magpie\Exceptions\InvalidDataFormatException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May match a routing domain and extract variables
 * @internal
 */
class VariableRouteDomainMatch extends RouteDomainMatch
{
    /**
     * @var array<string> Nodes to be matched against
     */
    protected array $nodes;


    /**
     * Constructor
     * @param iterable<string> $nodes
     * @param string $targetDomainKey
     */
    protected function __construct(iterable $nodes, string $targetDomainKey)
    {
        parent::__construct($targetDomainKey);

        $this->nodes = $nodes;
    }


    /**
     * @inheritDoc
     */
    protected function onMatch(string $hostname, array &$arguments) : bool
    {
        $hostNodes = explode('.', $hostname);

        $totalNodes = count($hostNodes);
        if ($totalNodes <= 0) return false;
        if ($totalNodes !== count($this->nodes)) return false;

        for ($i = 0; $i < $totalNodes; ++$i) {
            if (!static::isMatchNode($hostNodes[$i], $this->nodes[$i], $arguments)) return false;
        }

        return true;
    }


    /**
     * Check for node match
     * @param string $hostNode
     * @param string $node
     * @param array $arguments
     * @return bool
     */
    protected static function isMatchNode(string $hostNode, string $node, array &$arguments) : bool
    {
        // Try to capture as variable
        if (str_starts_with($node, ':')) {
            $varName = substr($node, 1);
            $arguments[$varName] = $hostNode;
            return true;
        }

        // Otherwise, must be matched exactly
        return $hostNode === $node;
    }


    /**
     * Create an instance
     * @param string $spec
     * @param string $targetDomainKey
     * @return static
     * @throws SafetyCommonException
     */
    public static function create(string $spec, string $targetDomainKey) : static
    {
        $specNodes = explode('.', $spec);

        $retNodes = [];
        foreach ($specNodes as $specNode) {
            $startBracePos = strpos($specNode, '{');
            $endBracePos = strpos($specNode, '}');

            if ($startBracePos === false && $endBracePos === false) {
                // Not a variable
                $retNodes[] = $specNode;
            } else {
                // Try to match like a variable
                if ($startBracePos !== 0 || $endBracePos !== (strlen($specNode) - 1)) {
                    throw new InvalidDataFormatException();
                }
                $varName = substr($specNode, 1, -1);
                $retNodes[] = ':' . $varName;
            }
        }

        return new static($retNodes, $targetDomainKey);
    }
}