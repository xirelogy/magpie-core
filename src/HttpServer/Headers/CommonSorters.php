<?php

namespace Magpie\HttpServer\Headers;

use Magpie\General\Concepts\ArraySortable;
use Magpie\General\Traits\StaticClass;
use Magpie\HttpServer\Headers\Classifiers\DefaultHeaderValueClassifier;
use Magpie\HttpServer\Headers\Classifiers\LevelHeaderValueClassifier;
use Magpie\HttpServer\Headers\Classifiers\MimeHeaderValueClassifier;

/**
 * Sorters for common HTTP CommaSeparatedQualityHeaderValue
 */
class CommonSorters
{
    use StaticClass;


    /**
     * Provide a sorter to sort 'Accept' header
     * @return ArraySortable<ColonSeparatedHeaderValue>
     */
    public static function accept() : ArraySortable
    {
        $classifier = LevelHeaderValueClassifier::create(
            MimeHeaderValueClassifier::create()
        );
        return $classifier->getSorter();
    }


    /**
     * Provide a sorter to sort 'Accept-Encoding' header
     * @return ArraySortable<ColonSeparatedHeaderValue>
     */
    public static function acceptEncoding() : ArraySortable
    {
        $classifier = LevelHeaderValueClassifier::create(
            DefaultHeaderValueClassifier::create()
        );
        return $classifier->getSorter();
    }


    /**
     * Provide a sorter to sort 'Accept-Language' header
     * @return ArraySortable<ColonSeparatedHeaderValue>
     */
    public static function acceptLanguage() : ArraySortable
    {
        $classifier = LevelHeaderValueClassifier::create(
            DefaultHeaderValueClassifier::create()
        );
        return $classifier->getSorter();
    }
}