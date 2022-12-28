<?php

namespace Magpie\Locales\Traits;

/**
 * May normalize locale
 */
trait CommonNormalizeLocale
{
    /**
     * Normalize the locale string
     * @param string $locale
     * @return string
     */
    protected static final function normalizeLocale(string $locale) : string
    {
        $locale = str_replace('-', '_', strtolower($locale));

        $ret = [];
        foreach (explode(',', $locale) as $subLocale) {
            $ret[] = trim($subLocale);
        }

        return implode(',', $ret);
    }
}
