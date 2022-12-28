<?php

namespace Magpie\General\Sugars;

use Magpie\General\Traits\StaticClass;

/**
 * String cases related utilities
 */
class StringCase
{
    use StaticClass;

    /**
     * Specific text encoding to be used
     */
    protected const ENCODING = 'UTF-8';


    /**
     * Accept text with deliminator to separate between them into corresponding words.
     * Supported deliminators: space (including newlines and tabs), dash (kebab case), underscore (snake case)
     * @param string $text
     * @return array<string>
     */
    public static function fromDeliminated(string $text) : array
    {
        $text = str_replace(['_', '-', "\r", "\n", "\t"], ' ', $text);
        return static::normalizeWords(explode(' ', $text));
    }


    /**
     * Accept words separated by spaces into corresponding words
     * @param string $text
     * @return array<string>
     */
    public static function fromWords(string $text) : array
    {
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        return static::normalizeWords(explode(' ', $text));
    }


    /**
     * Accept snake case (eg: snake_case) text into corresponding words
     * @param string $text
     * @return array<string>
     */
    public static function fromSnake(string $text) : array
    {
        return static::normalizeWords(explode('_', $text));
    }


    /**
     * Express words in snake case (eg: snake_case)
     * @param iterable<string> $words
     * @return string
     */
    public static function toSnake(iterable $words) : string
    {
        $words = static::normalizeWords($words);
        return implode('_', $words);
    }


    /**
     * Accept kebab case (eg: kebab-case) text into corresponding words
     * @param string $text
     * @return array<string>
     */
    public static function fromKebab(string $text) : array
    {
        return static::normalizeWords(explode('-', $text));
    }


    /**
     * Express words in kebab case (eg: kebab-case)
     * @param iterable<string> $words
     * @return string
     */
    public static function toKebab(iterable $words) : string
    {
        $words = static::normalizeWords($words);
        return implode('-', $words);
    }


    /**
     * Accept camel case (eg: camelCase) text into corresponding words
     * @param string $text
     * @return array<string>
     */
    public static function fromCamel(string $text) : array
    {
        return static::fromCapsSeparating($text);
    }


    /**
     * Express words in camel case (eg: camelCase)
     * @param iterable<string> $words
     * @return string
     */
    public static function toCamel(iterable $words) : string
    {
        $words = static::firstCaps($words, true);
        return implode('', $words);
    }


    /**
     * Accept studly case (eg: StudlyCase) text into corresponding words
     * @param string $text
     * @return array
     */
    public static function fromStudly(string $text) : array
    {
        return static::fromCapsSeparating($text);
    }


    /**
     * Express words in studly case (eg: StudlyCase)
     * @param iterable<string> $words
     * @return string
     */
    public static function toStudly(iterable $words) : string
    {
        $words = static::firstCaps($words, false);
        return implode('', $words);
    }


    /**
     * Accept caps-separating cases text into corresponding words
     * @param string $text
     * @return array<string>
     */
    private static function fromCapsSeparating(string $text) : array
    {
        $ret = [];
        $buffer = '';

        $textLength = mb_strlen($text, static::ENCODING);
        for ($i = 0; $i < $textLength; ++$i) {
            $c = static::substr($text, $i, 1);

            if (static::isUpperCase($c)) {
                if (!is_empty_string($buffer)) {
                    $ret[] = $buffer;
                    $buffer = '';
                }
            }

            $buffer .= $c;
        }

        if (!is_empty_string($buffer)) {
            $ret[] = $buffer;
        }

        return static::normalizeWords($ret);
    }


    /**
     * Check if given character is uppercase
     * @param string $c
     * @return bool
     */
    private static function isUpperCase(string $c) : bool
    {
        return static::strtoupper($c) === $c;
    }


    /**
     * Normalize and convert words with first letter of each word capitalized
     * @param iterable<string> $words
     * @param bool $isCamel When true, make exception to first word not to capitalize
     * @return array<string>
     */
    private static function firstCaps(iterable $words, bool $isCamel) : array
    {
        $isFirst = true;
        if (!$isCamel) $isFirst = false;

        $ret = [];
        foreach ($words as $word) {
            $word = trim(static::strtolower($word));
            if (is_empty_string($word)) continue;

            if (!$isFirst) $word = static::firstCapWord($word);
            $isFirst = false;

            $ret[] = $word;
        }

        return $ret;
    }


    /**
     * Convert first character (letter) of given word into capital letters
     * @param string $word
     * @return string
     */
    private static function firstCapWord(string $word) : string
    {
        return static::strtoupper(static::substr($word, 0, 1)) . static::substr($word, 1);
    }


    /**
     * Normalize the words and provide lowercase outputs
     * @param iterable<string> $words
     * @return array<string>
     */
    private static function normalizeWords(iterable $words) : array
    {
        $ret = [];
        foreach ($words as $word) {
            $word = trim(static::strtolower($word));
            if (is_empty_string($word)) continue;
            $ret[] = $word;
        }

        return $ret;
    }


    /**
     * Multibyte safe substr()
     * @param string $text
     * @param int $start
     * @param int|null $length
     * @return string
     */
    private static function substr(string $text, int $start, ?int $length = null) : string
    {
        return mb_substr($text, $start, $length, static::ENCODING);
    }


    /**
     * Multibyte safe strtoupper()
     * @param string $text
     * @return string
     */
    private static function strtoupper(string $text) : string
    {
        return mb_strtoupper($text, static::ENCODING);
    }


    /**
     * Multibyte safe strtolower()
     * @param string $text
     * @return string
     */
    private static function strtolower(string $text) : string
    {
        return mb_strtolower($text, static::ENCODING);
    }
}