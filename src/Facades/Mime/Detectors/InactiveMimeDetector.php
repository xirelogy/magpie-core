<?php

namespace Magpie\Facades\Mime\Detectors;

use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Traits\SingletonInstance;

/**
 * Inactive MIME type detector
 */
class InactiveMimeDetector extends BaseMimeDetector
{
    use SingletonInstance;

    /**
     * Current type class
     */
    public const TYPECLASS = 'inactive';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function detectMimeType(BinaryDataProvidable|string|null $content) : ?string
    {
        // Equivalent to inactive
        return null;
    }
}