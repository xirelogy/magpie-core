<?php

namespace Magpie\Consoles\Texts;

use Magpie\Consoles\DisplayStyle;

/**
 * Single unit structured text
 */
class UnitStructuredText extends StructuredText
{
    /**
     * @var string Text content
     */
    public string $text;
    /**
     * @var string|null Format tag
     */
    public ?string $format;


    /**
     * Constructor
     * @param string $text
     * @param DisplayStyle|string|null $format
     */
    public function __construct(string $text, DisplayStyle|string|null $format = null)
    {
        $this->text = $text;
        $this->format = static::acceptFormat($format);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->text;
    }


    /**
     * Accept the format argument
     * @param DisplayStyle|string|null $format
     * @return string|null
     */
    protected static function acceptFormat(DisplayStyle|string|null $format) : ?string
    {
        if ($format === null) return null;

        return $format instanceof DisplayStyle ? $format->value : $format;
    }
}