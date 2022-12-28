<?php

namespace Magpie\Commands\Impls;

use Magpie\Commands\Request;

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
     */
    public function __construct(string $command, array $options, array $arguments)
    {
        parent::__construct($command, $options, $arguments);
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