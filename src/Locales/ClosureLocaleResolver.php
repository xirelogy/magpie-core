<?php

namespace Magpie\Locales;

use Closure;
use Magpie\General\Sugars\Excepts;
use Magpie\Locales\Concepts\LocaleResolvable;

/**
 * Closure implementation for LocaleResolvable
 */
class ClosureLocaleResolver implements LocaleResolvable
{
    /**
     * @var Closure Target function
     */
    protected Closure $fn;


    /**
     * Constructor
     * @param callable(string,string):(string|null) $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function resolveForLocale(string $text, string $locale) : ?string
    {
        return Excepts::noThrow(fn () => ($this->fn)($text, $locale));
    }


    /**
     * Create a new instance
     * @param callable(string,string):(string|null) $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}