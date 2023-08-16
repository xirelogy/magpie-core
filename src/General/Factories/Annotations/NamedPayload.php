<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;
use BackedEnum;
use StringBackedEnum;

/**
 * Declare a mappable payload associated to given constant
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class NamedPayload
{
    /**
     * @var BackedEnum|string|int Payload
     */
    public readonly BackedEnum|string|int $payload;
    /**
     * @var string|null Associated tag type for the given payload
     */
    public readonly ?string $tag;


    /**
     * Constructor
     * @param BackedEnum|string|int $payload
     * @param string|null $tag
     */
    public function __construct(BackedEnum|string|int $payload, ?string $tag = null)
    {
        $this->payload = $payload;
        $this->tag = $tag;
    }
}