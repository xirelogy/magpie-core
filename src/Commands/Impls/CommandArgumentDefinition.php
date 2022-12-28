<?php

namespace Magpie\Commands\Impls;

/**
 * Definition of a command argument
 * @internal
 */
class CommandArgumentDefinition
{
    /**
     * @var string Argument name
     */
    public readonly string $name;
    /**
     * @var bool If this argument is mandatory
     */
    public readonly bool $isMandatory;


    /**
     * Constructor
     * @param string $name
     * @param bool $isMandatory
     */
    public function __construct(string $name, bool $isMandatory)
    {
        $this->name = $name;
        $this->isMandatory = $isMandatory;
    }
}