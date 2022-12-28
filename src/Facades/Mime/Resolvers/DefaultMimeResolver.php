<?php

namespace Magpie\Facades\Mime\Resolvers;

use Magpie\General\Traits\SingletonInstance;

/**
 * Default MIME content types and extensions resolver
 */
class DefaultMimeResolver extends BaseMimeResolver
{
    use SingletonInstance;


    /**
     * Current type class
     */
    public const TYPECLASS = 'default';


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
    protected static function mapMimeTypes() : iterable
    {
        yield 'txt' => 'text/plain';
        yield 'html' => 'text/html';
        yield 'htm' => 'text/html';
        yield 'css' => 'text/css';
        yield 'js' => 'application/javascript';
        yield 'json' => 'application/json';
        yield 'xml' => 'application/xml';

        yield 'jpeg' => 'image/jpeg';
        yield 'jpg' => 'image/jpeg';
        yield 'jpe' => 'image/jpeg';
        yield 'gif' => 'image/gif';
        yield 'bmp' => 'image/bmp';
        yield 'ico' => 'image/x-icon';
        yield 'tiff' => 'image/tiff';
        yield 'tif' => 'image/tiff';
        yield 'svg' => 'image/svg+xml';
        yield 'svgz' => 'image/svg+xml';
    }
}