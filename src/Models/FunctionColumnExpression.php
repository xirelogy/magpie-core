<?php

namespace Magpie\Models;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Quote;
use Magpie\Models\Concepts\QueryArgumentable;
use Magpie\Models\Impls\QueryContext;
use Magpie\Models\Impls\QueryStatement;

/**
 * Column expression which is a function
 */
class FunctionColumnExpression extends ColumnExpression
{
    /**
     * @var string Function name
     */
    public readonly string $name;
    /**
     * @var array Function arguments
     */
    public readonly array $arguments;


    /**
     * Constructor
     * @param string $name
     * @param mixed ...$arguments
     */
    public function __construct(string $name, mixed ...$arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }


    /**
     * @inheritDoc
     */
    protected function onFinalize(QueryContext $context) : QueryStatement
    {
        $ret = new QueryStatement('');

        foreach ($this->arguments as $argument) {
            $argumentFinalized = $this->finalizeArgument($context, $argument);

            if ($ret->isEmpty()) {
                $ret = $argumentFinalized;
            } else {
                $ret->appendJoinIfNotEmpty(', ', $argumentFinalized);
            }
        }

        $ret->sql = $this->name . Quote::bracket($ret->sql);
        return $ret;
    }


    /**
     * Finalize argument
     * @param QueryContext $context
     * @param mixed $argument
     * @return QueryStatement
     * @throws SafetyCommonException
     */
    protected function finalizeArgument(QueryContext $context, mixed $argument) : QueryStatement
    {
        if ($argument instanceof QueryArgumentable) {
            return $argument->_finalize($context);
        }

        return new QueryStatement('?', [$argument]);
    }
}