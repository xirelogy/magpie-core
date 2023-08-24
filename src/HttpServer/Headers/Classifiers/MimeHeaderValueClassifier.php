<?php

namespace Magpie\HttpServer\Headers\Classifiers;

use Magpie\HttpServer\Headers\ColonSeparatedHeaderValue;

/**
 * Classify header values according to MIME type
 */
class MimeHeaderValueClassifier extends HeaderValueClassifier
{
    /**
     * Fully specified
     */
    protected const CLASS_FULL = 2;
    /**
     * Partial wildcard
     */
    protected const CLASS_PARTIAL = 1;
    /**
     * Full wildcard
     */
    protected const CLASS_WILDCARD = 0;
    /**
     * Invalid class
     */
    protected const CLASS_INVALID = -1;


    /**
     * @inheritDoc
     */
    protected function getClassifierKey(ColonSeparatedHeaderValue $value) : string|int
    {
        $mime = $value->safeOptional('');
        if ($mime === null) return static::CLASS_INVALID;

        if ($mime === '*') return static::CLASS_WILDCARD;

        $mimeComponents = explode('/', $mime);
        if (count($mimeComponents) != 2) return static::CLASS_FULL;

        $mime1 = $mimeComponents[0];
        $mime2 = $mimeComponents[1];

        if ($mime1 === '*' && $mime2 === '*') return static::CLASS_WILDCARD;
        if ($mime1 === '*' || $mime2 === '*') return static::CLASS_PARTIAL;

        return static::CLASS_FULL;
    }


    /**
     * @inheritDoc
     */
    protected function sortClassifiers(array &$classes) : void
    {
        krsort($classes);
    }
}