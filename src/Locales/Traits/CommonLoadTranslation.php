<?php

namespace Magpie\Locales\Traits;

use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\System\HardCore\AutoloadReflection;

/**
 * May load translations
 */
trait CommonLoadTranslation
{
    use CommonNormalizeLocale;


    /**
     * Load translations for given class (context) and locale
     * @param string $className
     * @param string $locale
     * @return array<string, string>
     */
    protected static final function loadTranslations(string $className, string $locale) : array
    {
        $locale = static::normalizeLocale($locale);

        $ret = [];

        // Where there isn't a class, translation is not available
        if (trim($className) === '') return $ret;

        foreach (AutoloadReflection::instance()->getClassFilenames($className) as $filename) {
            if (str_ends_with($filename, '.php')) $filename = substr($filename, 0, strlen($filename) - 4);
            $filename .= ".locale.$locale.json";

            if (!LocalRootFileSystem::instance()->isFileExist($filename)) continue;

            Excepts::noThrow(function () use($filename, &$ret) {
                $content = LocalRootFileSystem::instance()->readFile($filename)->getData();

                $decodedContent = SimpleJSON::decode($content, SimpleJSON::OPT_DECODE_AS_ARRAY);
                foreach ($decodedContent as $key => $value) {
                    if (array_key_exists($key, $ret)) continue;
                    if (!is_string($value)) continue;

                    $ret[$key] = $value;
                }
            });
        }

        return $ret;
    }
}