<?php

namespace Magpie\Locales\Concepts;

use Magpie\Codecs\Concepts\PreferStringable;

/**
 * May be translated to corresponding strings according to locale
 */
interface Localizable extends PreferStringable
{
    /**
     * Default translation
     * @return string
     */
    public function getDefaultTranslation() : string;


    /**
     * Translation for specific locale
     * @param string $locale
     * @return string
     */
    public function getTranslation(string $locale) : string;
}