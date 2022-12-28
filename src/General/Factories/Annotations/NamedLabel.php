<?php

namespace Magpie\General\Factories\Annotations;

use Attribute;

/**
 * Declare the translation label for current constant
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class NamedLabel
{
    /**
     * @var string Label text
     */
    public readonly string $label;
    /**
     * @var string|null Specific locale for given label text
     */
    public readonly ?string $locale;


    /**
     * Constructor
     * @param string $label
     * @param string|null $locale
     */
    public function __construct(string $label, ?string $locale = null)
    {
        $this->label = $label;
        $this->locale = $locale !== null ? trim($locale) : null;
    }
}