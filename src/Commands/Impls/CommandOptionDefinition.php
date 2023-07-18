<?php

namespace Magpie\Commands\Impls;

use Magpie\Locales\Concepts\Localizable;

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
     * @var string|Localizable|null Option description
     */
    public string|Localizable|null $description = null;


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