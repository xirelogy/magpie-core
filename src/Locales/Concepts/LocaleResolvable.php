<?php

namespace Magpie\Locales\Concepts;

/**
 * May resolve for additional translation for given locale dynamically
 */
interface LocaleResolvable
{
    /**
     * Resolve the target text for given locale
     * @param string $text The target text to be resolved
     * @param string $locale Locale to be resolved with
     * @return string|null Replacement text if successful, or null if failure
     */
    public function resolveForLocale(string $text, string $locale) : ?string;
}