<?php

namespace Magpie\Commands\Attributes;

use Attribute;

/**
 * Declare the signature of a command
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CommandSignature
{
    /**
     * @var string Signature of the command
     */
    public string $signature;


    /**
     * Constructor
     * @param string $signature
     */
    public function __construct(string $signature)
    {
        $this->signature = $signature;
    }
}