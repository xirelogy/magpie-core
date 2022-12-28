<?php

namespace Magpie\Locales;

use Magpie\Locales\Concepts\LocaleResolvable;
use Magpie\Locales\Concepts\Localizable;
use Magpie\Locales\Traits\CommonNormalizeLocale;

/**
 * Multi locale string
 */
class I18nMultiLocale implements Localizable
{
    use CommonNormalizeLocale;

    /**
     * The key where default string is stored
     */
    protected const DEFAULT_KEY = '';

    /**
     * @var array<string, string> Map of strings in multiple locale
     */
    protected array $texts;
    /**
     * @var LocaleResolvable|null Fallback resolver
     */
    protected ?LocaleResolvable $resolver = null;


    /**
     * Constructor
     * @param string $defaultText
     */
    public function __construct(string $defaultText)
    {
        $this->texts = [
            static::DEFAULT_KEY => $defaultText,
        ];
    }


    /**
     * Add a definition
     * @param string $locale
     * @param string $text
     * @return $this
     */
    public function define(string $locale, string $text) : static
    {
        $locale = static::normalizeLocale($locale);
        $this->texts[$locale] = $text;
        return $this;
    }


    /**
     * Specify the fallback resolver
     * @param LocaleResolvable $resolver
     * @return $this
     */
    public function withResolver(LocaleResolvable $resolver) : static
    {
        $this->resolver = $resolver;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getDefaultTranslation() : string
    {
        return $this->texts[static::DEFAULT_KEY];
    }


    /**
     * @inheritDoc
     */
    public function getTranslation(string $locale) : string
    {
        $locale = static::normalizeLocale($locale);

        foreach (explode(',', $locale) as $thisLocale) {
            if (array_key_exists($thisLocale, $this->texts)) return $this->texts[$thisLocale];

            if ($this->resolver !== null) {
                $target = $this->getDefaultTranslation();
                $resolved = $this->resolver->resolveForLocale($target, $thisLocale);
                if ($resolved !== null) return $resolved;
            }
        }

        return $this->getDefaultTranslation();
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $locale = I18n::getCurrentLocale();
        return $this->getTranslation($locale);
    }
}