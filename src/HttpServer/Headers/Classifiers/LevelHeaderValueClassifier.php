<?php

namespace Magpie\HttpServer\Headers\Classifiers;

use Magpie\Codecs\Parsers\FloatParser;
use Magpie\HttpServer\Headers\ColonSeparatedHeaderValue;

/**
 * Classify header values according to 'level'
 */
class LevelHeaderValueClassifier extends HeaderValueClassifier
{
    /**
     * Default level
     */
    protected const DEFAULT_LEVEL = 9999;
    /**
     * Precision for 'level'
     */
    protected const LEVEL_PRECISION = 10000;


    /**
     * @inheritDoc
     */
    protected function getClassifierKey(ColonSeparatedHeaderValue $value) : string|int
    {
        $level = $value->safeOptional('level', FloatParser::create()->withMin(0)) ?? static::DEFAULT_LEVEL;
        return intval(floor($level * static::LEVEL_PRECISION));
    }


    /**
     * @inheritDoc
     */
    protected function sortClassifiers(array &$classes) : void
    {
        ksort($classes);
    }
}