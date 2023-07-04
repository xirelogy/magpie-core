<?php

namespace Magpie\Routes\Impls;

use Magpie\General\RegEx;

/**
 * May match a routing domain with regular expression
 * @internal
 */
class RegExRouteDomainMatch extends RouteDomainMatch
{
    /**
     * @var RegEx Expression to be checked against
     */
    protected readonly RegEx $expr;


    /**
     * Constructor
     * @param RegEx $expr
     * @param string $targetDomainKey
     */
    protected function __construct(RegEx $expr, string $targetDomainKey)
    {
        parent::__construct($targetDomainKey);

        $this->expr = $expr;
    }


    /**
     * @inheritDoc
     */
    protected function onMatch(string $hostname, array &$arguments) : bool
    {
        return $this->expr->isMatched($hostname);
    }


    /**
     * Create an instance
     * @param RegEx|string $exprSpec
     * @param string $targetDomainKey
     * @return static
     */
    public static function create(RegEx|string $exprSpec, string $targetDomainKey) : static
    {
        $expr = $exprSpec instanceof RegEx ? $exprSpec : RegEx::fromWildcard($exprSpec);
        return new static($expr, $targetDomainKey);
    }
}