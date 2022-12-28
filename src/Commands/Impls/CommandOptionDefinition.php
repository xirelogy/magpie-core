<?php

namespace Magpie\Commands\Impls;

/**
 * Definition of a command option
 * @internal
 */
class CommandOptionDefinition
{
    /**
     * @var string Option name
     */
    public readonly string $name;
    /**
     * @var bool If this option has corresponding payload
     */
    public readonly bool $hasPayload;


    /**
     * Constructor
     * @param string $name
     * @param bool $hasPayload
     */
    public function __construct(string $name, bool $hasPayload)
    {
        $this->name = $name;
        $this->hasPayload = $hasPayload;
    }
}