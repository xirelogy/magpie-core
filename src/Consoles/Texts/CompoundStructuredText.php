<?php

namespace Magpie\Consoles\Texts;

/**
 * Compound structured text
 */
class CompoundStructuredText extends StructuredText
{
    /**
     * @var array<StructuredText> All texts
     */
    public array $texts;


    /**
     * Constructor
     * @param iterable<StructuredText|string> $texts
     */
    public function __construct(iterable $texts)
    {
        $this->texts = static::acceptTexts($texts);
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $ret = '';
        foreach ($this->texts as $text) {
            $ret .= $text->__toString();
        }

        return $ret;
    }


    /**
     * Accept multiple text arguments
     * @param iterable<StructuredText|string> $texts
     * @return array<StructuredText>
     */
    protected static function acceptTexts(iterable $texts) : array
    {
        $ret = [];
        foreach ($texts as $text) {
            if (!$text instanceof StructuredText) $text = new UnitStructuredText($text);
            $ret[] = $text;
        }

        return $ret;
    }
}