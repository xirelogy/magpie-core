<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;

/**
 * Declare a mappable payload associated to given constant
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class NamedPayload
{
    /**
     * @var string Payload
     */
    public readonly string $payload;
    /**
     * @var string|null Associated tag type for the given payload
     */
    public readonly ?string $tag;


    /**
     * Constructor
     * @param string $payload
     * @param string|null $tag
     */
    public function __construct(string $payload, ?string $tag = null)
    {
        $this->payload = $payload;
        $this->tag = $tag;
    }
}