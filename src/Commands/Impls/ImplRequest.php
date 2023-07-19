<?php

namespace Magpie\Commands\Impls;

use Magpie\Commands\Request;
use Magpie\HttpServer\ServerCollection;

/**
 * Implementation of console request
 * @internal
 */
class ImplRequest extends Request
{
    /**
     * Constructor
     * @param string $command
     * @param array<string, string|bool> $options
     * @param array<string, string> $arguments
     * @param ServerCollection $serverVars
     */
    public function __construct(string $command, array $options, array $arguments, ServerCollection $serverVars)
    {
        parent::__construct($command, $options, $arguments, $serverVars);
    }


    /**
     * Arguments as an array (in order)
     * @return array<string>
     */
    public function exportArguments() : array
    {
        return array_values($this->rawArguments);
    }
}