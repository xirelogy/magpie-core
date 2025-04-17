<?php

namespace Magpie\Locales;

use Magpie\General\Traits\StaticClass;
use Magpie\Locales\Concepts\Localizable;

/**
 * I18n related source cache support
 */
class I18nSourceCache
{
    /**
     * Special type to tag a 'string' text
     */
    protected const TYPE_STRING = '.string';

    use StaticClass;


    /**
     * Export to source cache
     * @param string|Localizable $target
     * @return array
     */
    public static function export(string|Localizable $target) : array
    {
        if ($target instanceof I18nTaggedLocale) {
            $ret = [
                'type' => I18nTaggedLocale::class,
            ];

            $target->_sourceCacheExportTo($ret);
            return $ret;
        }

        // Final choice, flatten into text
        $finalTarget = $target instanceof Localizable ? $target->__toString() : $target;

        // Export as text
        return [
            'type' => static::TYPE_STRING,
            'text' => $finalTarget,
        ];
    }


    /**
     * Import from source cache
     * @param array $data
     * @return string|Localizable
     */
    public static function import(array $data) : string|Localizable
    {
        $targetType = $data['type'];

        return match ($targetType) {
            I18nTaggedLocale::class,
                => I18nTaggedLocale::_sourceCacheImportFrom($data),
            static::TYPE_STRING,
                => $data['text'],
            default,
                => '<unknown>',
        };
    }
}