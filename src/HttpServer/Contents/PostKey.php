<?php

namespace Magpie\HttpServer\Contents;

/**
 * A 'key' in POST body content
 */
class PostKey
{
    /**
     * @var string Key name
     */
    public readonly string $name;
    /**
     * @var array<string> Key indices (if exist), where blank string is for index-less append
     */
    public readonly array $indices;


    /**
     * Constructor
     * @param string $name
     * @param iterable<string> $indices
     */
    protected function __construct(string $name, iterable $indices)
    {
        $this->name = $name;
        $this->indices = iter_flatten($indices, false);
    }


    /**
     * Parse from given string
     * @param string $text
     * @return static|null
     */
    public static function from(string $text) : ?static
    {
        // Try to extract the 'name' part
        $openIndex = strpos($text, '[');
        $closeIndex = strpos($text, ']');
        if ($openIndex !== false && $closeIndex !== false && $openIndex < $closeIndex) {
            $name = substr($text, 0, $openIndex);
            $text = substr($text, $openIndex);
        } else {
            $name = $text;
            $text = '';
        }

        $retIndices = [];
        while (true) {
            $openIndex = strpos($text, '[');
            $closeIndex = strpos($text, ']');
            if ($openIndex === false || $closeIndex === false) break;
            if ($openIndex !== 0) break;
            if ($openIndex >= $closeIndex) break;

            $index = substr($text, $openIndex + 1, $closeIndex - $openIndex - 1);
            $text = substr($text, $closeIndex + 1);

            $retIndices[] = $index;
        }

        return new static(static::safeName($name), $retIndices);
    }


    /**
     * Names are converted into their 'safe' version according to PHP
     * @param string $text
     * @return string
     * @link https://www.php.net/manual/en/language.variables.external.php
     */
    protected static function safeName(string $text) : string
    {
        $text = str_replace(' ', '_', $text);
        $text = str_replace('.', '_', $text);
        $text = str_replace('[', '_', $text);

        return $text;
    }
}