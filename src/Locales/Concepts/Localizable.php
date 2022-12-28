<?php

namespace Magpie\Locales\Concepts;

use Stringable;

/**
 * May be translated to corresponding strings according to locale
 */
interface Localizable extends Stringable
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