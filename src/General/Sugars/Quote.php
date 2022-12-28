<?php

namespace Magpie\General\Sugars;

use Magpie\General\Traits\StaticClass;

/**
 * Add quotes to given target
 */
class Quote
{
    use StaticClass;


    /**
     * Add single quote to text: `'example'`
     * @param string $text
     * @return string
     */
    public static function single(string $text) : string
    {
        return '\'' . $text . '\'';
    }


    /**
     * Add double quote to text: `"example"`
     * @param string $text
     * @return string
     */
    public static function double(string $text) : string
    {
        return '"' . $text . '"';
    }


    /**
     * Add round bracket to text: `(example)`
     * @param string $text
     * @return string
     */
    public static function bracket(string $text) : string
    {
        return '(' . $text . ')';
    }


    /**
     * Add square bracket to text: `[example]`
     * @param string $text
     * @return string
     */
    public static function square(string $text) : string
    {
        return '[' . $text . ']';
    }


    /**
     * Add brace (curly-bracket) to text: `{example}`
     * @param string $text
     * @return string
     */
    public static function brace(string $text) : string
    {
        return '{' . $text . '}';
    }


    /**
     * Add angle bracket to text: `<example>`
     * @param string $text
     * @return string
     */
    public static function angle(string $text) : string
    {
        return '<' . $text . '>';
    }


    /**
     * Add back-ticks to text: ```example```
     * @param string $text
     * @return string
     */
    public static function backTick(string $text) : string
    {
        return '`' . $text . '`';
    }
}