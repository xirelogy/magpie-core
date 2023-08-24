<?php

namespace Magpie\HttpServer\Headers\Classifiers;

use Magpie\HttpServer\Headers\ColonSeparatedHeaderValue;

/**
 * Default classify header values
 */
class DefaultHeaderValueClassifier extends HeaderValueClassifier
{
    /**
     * @inheritDoc
     */
    protected function getClassifierKey(ColonSeparatedHeaderValue $value) : string|int
    {
        $payload = $value->safeOptional('');
        if ($payload === null) return -1;

        if ($payload === '*') return 0;

        return 1;
    }


    /**
     * @inheritDoc
     */
    protected function sortClassifiers(array &$classes) : void
    {
        krsort($classes);
    }
}