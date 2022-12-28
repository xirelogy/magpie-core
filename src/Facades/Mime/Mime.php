<?php

namespace Magpie\Facades\Mime;

use Magpie\Facades\Mime\Detectors\FileInfoMimeDetector;
use Magpie\Facades\Mime\Resolvers\DefaultMimeResolver;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\FilePath;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Kernel\Kernel;

/**
 * MIME facade
 */
class Mime
{
    use StaticClass;


    /**
     * Get extension for MIME type
     * @param string|null $mimeType
     * @return string|null
     */
    public static function getExtension(?string $mimeType) : ?string
    {
        return static::getResolver()->getExtension($mimeType);
    }


    /**
     * Get MIME type for extension
     * @param string|null $extension
     * @return string|null
     */
    public static function getMimeType(?string $extension) : ?string
    {
        return static::getResolver()->getMimeType($extension);
    }


    /**
     * Detect MIME type from content
     * @param BinaryDataProvidable|string|null $content
     * @return string|null
     */
    public static function detectMimeType(BinaryDataProvidable|string|null $content) : ?string
    {
        return static::getDetector()->detectMimeType($content);
    }


    /**
     * Detect extension from content
     * @param BinaryDataProvidable|string|null $content
     * @return string|null
     */
    public static function detectExtension(BinaryDataProvidable|string|null $content) : ?string
    {
        $mimeType = static::detectMimeType($content);
        return static::getExtension($mimeType);
    }


    /**
     * Resolve for MIME type, first from content, then from extension
     * @param string|null $path
     * @param BinaryDataProvidable|string|null $content
     * @return string|null
     */
    public static function resolveMimeType(?string $path, BinaryDataProvidable|string|null $content) : ?string
    {
        if ($content !== null) {
            $mimeType = static::detectMimeType($content);
            if ($mimeType !== null) return $mimeType;
        }

        if ($path !== null) {
            $extension = FilePath::getExtension($path);
            $mimeType = static::getMimeType($extension);
            if ($mimeType !== null) return $mimeType;
        }

        return null;
    }


    /**
     * Get MIME resolver
     * @return MimeResolvable
     */
    protected static function getResolver() : MimeResolvable
    {
        if (Kernel::hasCurrent()) {
            $resolver = Kernel::current()->getProvider(MimeResolvable::class);
            if ($resolver instanceof MimeResolvable) return $resolver;
        }

        return DefaultMimeResolver::instance();
    }


    /**
     * Get MIME detector
     * @return MimeDetectable
     */
    protected static function getDetector() : MimeDetectable
    {
        if (Kernel::hasCurrent()) {
            $detector = Kernel::current()->getProvider(MimeDetectable::class);
            if ($detector instanceof MimeDetectable) return $detector;
        }

        return FileInfoMimeDetector::instance();
    }
}