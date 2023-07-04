<?php

namespace Magpie\Routes\Impls;

use Exception;
use Magpie\General\Sugars\Excepts;

/**
 * May match a routing domain with specific rules
 * @internal
 */
abstract class RouteDomainMatch
{
    /**
     * @var string Target domain key to be used, when current rule matched
     */
    public readonly string $targetDomainKey;


    /**
     * Constructor
     * @param string $targetDomainKey
     */
    protected function __construct(string $targetDomainKey)
    {
        $this->targetDomainKey = $targetDomainKey;
    }


    /**
     * Check if the given hostname matches current rule
     * @param string $hostname Candidate hostname to be matched
     * @param array<string, string> $arguments Any specific domain arguments matched as a result
     * @return bool
     */
    public final function isMatched(string $hostname, array &$arguments) : bool
    {
        // Delegate match check to specific rule
        $tempArguments = [];
        $result = Excepts::noThrow(function () use ($hostname, &$tempArguments) {
            return $this->onMatch($hostname, $tempArguments);
        }, false);

        // And check result
        if (!$result) return false;

        // Only populate the result when matching is successful
        foreach ($tempArguments as $tempKey => $tempValue) {
            $arguments[$tempKey] = $tempValue;
        }

        return true;
    }


    /**
     * Check if the given hostname matches current rule
     * @param string $hostname
     * @param array<string, string> $arguments
     * @return bool
     * @throws Exception
     */
    protected abstract function onMatch(string $hostname, array &$arguments) : bool;
}